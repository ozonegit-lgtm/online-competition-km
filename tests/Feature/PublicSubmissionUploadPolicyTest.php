<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class PublicSubmissionUploadPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Storage::fake('public');
    }

    public function test_real_pdf_allowed_by_field_is_accepted(): void
    {
        [$competition, $field] = $this->competition('pdf', 8);
        $this->submit($competition, $field, $this->pdf('รายงาน.PDF'))->assertRedirect();
        $this->assertDatabaseCount('submissions', 1);
        $this->assertDatabaseCount('submission_files', 1);
    }

    public function test_field_allowlist_is_enforced_and_invalid_policy_fails_closed(): void
    {
        [$competition, $field] = $this->competition('docx', 8);
        $this->submit($competition, $field, $this->pdf())->assertSessionHasErrors("fields.{$field->id}");
        $field->update(['accepted_file_types' => 'php,exe']);
        $this->submit($competition, $field->fresh(), $this->pdf())->assertSessionHasErrors("fields.{$field->id}");
        $this->assertNothingPersisted();
    }

    public function test_dangerous_double_and_spoofed_extensions_are_rejected(): void
    {
        [$competition, $field] = $this->competition(null, null);
        foreach (['attack.php', 'report.pdf.php'] as $name) {
            $this->submit($competition, $field, $this->upload($name, '<?php echo 1;'))
                ->assertSessionHasErrors("fields.{$field->id}");
        }
        $this->submit($competition, $field, $this->upload('report.pdf', '<html>fake</html>'))
            ->assertSessionHasErrors("fields.{$field->id}");
        $this->assertNothingPersisted();
    }

    public function test_field_size_and_global_size_cap_are_enforced(): void
    {
        [$competition, $field] = $this->competition('pdf', 1);
        $this->submit($competition, $field, $this->pdf('large.pdf', 1024 * 1024 + 1))
            ->assertSessionHasErrors("fields.{$field->id}");
        $field->update(['max_file_size' => 99]);
        $this->submit($competition, $field->fresh(), $this->pdf('global.pdf', 10 * 1024 * 1024 + 1))
            ->assertSessionHasErrors("fields.{$field->id}");
        $this->assertNothingPersisted();
    }

    public function test_valid_docx_and_pptx_containers_are_accepted(): void
    {
        foreach ([
            ['docx', 'word/document.xml'],
            ['pptx', 'ppt/presentation.xml'],
        ] as [$extension, $requiredEntry]) {
            [$competition, $field] = $this->competition($extension, 8);
            $this->submit($competition, $field, $this->zip("work.{$extension}", [
                '[Content_Types].xml' => '<Types/>',
                $requiredEntry => '<document/>',
            ]))->assertRedirect();
        }
        $this->assertDatabaseCount('submissions', 2);
    }

    public function test_plain_zip_renamed_docx_and_corrupt_zip_are_rejected(): void
    {
        [$competition, $field] = $this->competition('docx,zip', 8);
        $this->submit($competition, $field, $this->zip('fake.docx', ['notes.txt' => 'no']))
            ->assertSessionHasErrors("fields.{$field->id}");
        $this->submit($competition, $field, $this->upload('broken.zip', "PK\x03\x04broken"))
            ->assertSessionHasErrors("fields.{$field->id}");
        $this->assertNothingPersisted();
    }

    public function test_validation_failure_leaves_no_records_or_files_and_token_can_retry(): void
    {
        [$competition, $field] = $this->competition('pdf', 8);
        $token = $this->token($competition);
        $this->submit($competition, $field, $this->upload('fake.pdf', '<?php'), $token)
            ->assertSessionHasErrors("fields.{$field->id}");
        $this->assertNothingPersisted();
        $this->submit($competition, $field, $this->pdf(), $token)->assertRedirect();
        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_public_form_displays_resolved_policy(): void
    {
        [$competition] = $this->competition('.DOCX, pdf,docx', 8);
        $this->get(route('competitions.submissions.create', $competition))
            ->assertOk()->assertSee('accept=".pdf,.docx"', false)
            ->assertSee('รองรับ: PDF, DOCX • สูงสุด 8 MB');
    }

    private function submit(Competition $competition, CompetitionFormField $field, UploadedFile $file, ?string $token = null)
    {
        return $this->post(route('competitions.submissions.store', $competition), [
            'project_title' => 'Project', 'contact_name' => 'Person',
            'contact_email' => 'person@example.com', 'contact_phone' => '0800000000',
            'terms' => '1', 'website' => '', 'form_guard_token' => $token ?? $this->token($competition),
            'fields' => [$field->id => $file],
        ]);
    }

    private function token(Competition $competition): string
    {
        return Crypt::encryptString(json_encode([
            'competition_id' => $competition->id, 'issued_at' => now()->subSeconds(2)->timestamp,
            'nonce' => bin2hex(random_bytes(20)),
        ], JSON_THROW_ON_ERROR));
    }

    private function competition(?string $types, ?int $size): array
    {
        $roleId = DB::table('roles')->insertGetId(['role_name' => 'Admin '.uniqid(), 'display_name' => 'Admin', 'created_at' => now(), 'updated_at' => now()]);
        $admin = User::create(['role_id' => $roleId, 'username' => 'admin-'.uniqid(), 'email' => uniqid().'@example.com', 'password' => 'password', 'is_active' => true]);
        $categoryId = DB::table('competition_categories')->insertGetId(['category_name' => 'Category '.uniqid(), 'category_slug' => 'category-'.uniqid(), 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $competition = Competition::create(['category_id' => $categoryId, 'created_by' => $admin->id, 'title' => 'Competition '.uniqid(), 'competition_type' => 'individual', 'visibility' => 'public', 'registration_start' => now()->subHour(), 'registration_end' => now()->addHour(), 'status' => 'open']);
        $field = CompetitionFormField::create(['competition_id' => $competition->id, 'label' => 'เอกสารผลงาน', 'field_name' => 'document', 'field_type' => 'file', 'accepted_file_types' => $types, 'max_file_size' => $size, 'is_required' => true, 'sort_order' => 1, 'is_active' => true]);
        return [$competition, $field];
    }

    private function pdf(string $name = 'report.pdf', int $minimumSize = 0): UploadedFile
    {
        return $this->upload($name, str_pad("%PDF-1.4\n%%EOF", $minimumSize, ' '));
    }

    private function zip(string $name, array $entries): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'zip-');
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entries as $entry => $contents) $zip->addFromString($entry, $contents);
        $zip->close();
        return new UploadedFile($path, $name, null, null, true);
    }

    private function upload(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upload-');
        file_put_contents($path, $contents);
        return new UploadedFile($path, $name, null, null, true);
    }

    private function assertNothingPersisted(): void
    {
        $this->assertDatabaseCount('submissions', 0);
        $this->assertDatabaseCount('submission_files', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
