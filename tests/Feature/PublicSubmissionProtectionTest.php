<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicSubmissionProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'submissions.form_guard.minimum_seconds' => 2,
            'submissions.form_guard.ttl_minutes' => 120,
        ]);
    }

    public function test_normal_user_can_open_form_and_submit_successfully(): void
    {
        [$competition] = $this->createCompetition();

        $this->get(route('competitions.submissions.create', $competition))
            ->assertOk()
            ->assertSee('form_guard_token', false);

        $response = $this->postSubmission($competition);

        $submission = Submission::sole();
        $response->assertRedirect(route('submissions.success', $submission));
        $this->assertNull($submission->final_score);
    }

    public function test_honeypot_is_rejected_without_creating_submission(): void
    {
        [$competition] = $this->createCompetition();

        $this->postSubmission($competition, ['website' => 'https://bot.example'])
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_forged_token_is_rejected(): void
    {
        [$competition] = $this->createCompetition();

        $this->post(
            route('competitions.submissions.store', $competition),
            $this->validPayload() + ['form_guard_token' => 'forged']
        )->assertSessionHasErrors('form');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_token_for_another_competition_is_rejected(): void
    {
        [$competition] = $this->createCompetition();
        [$otherCompetition] = $this->createCompetition();

        $this->postSubmission($competition, [], $this->tokenFor($otherCompetition))
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_submission_faster_than_minimum_time_is_rejected(): void
    {
        [$competition] = $this->createCompetition();

        $this->postSubmission($competition, [], $this->tokenFor($competition, 0))
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_expired_token_is_rejected(): void
    {
        [$competition] = $this->createCompetition();

        $this->postSubmission($competition, [], $this->tokenFor($competition, 121 * 60))
            ->assertSessionHasErrors('form');

        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_successful_token_can_only_be_used_once(): void
    {
        [$competition] = $this->createCompetition();
        $token = $this->tokenFor($competition);

        $this->postSubmission($competition, [], $token)->assertRedirect();
        $this->postSubmission($competition, [], $token)->assertSessionHasErrors('form');

        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_repeated_request_does_not_create_duplicate_submission(): void
    {
        [$competition] = $this->createCompetition();
        $token = $this->tokenFor($competition);
        $payload = $this->validPayload() + ['form_guard_token' => $token];

        $this->post(route('competitions.submissions.store', $competition), $payload);
        $this->post(route('competitions.submissions.store', $competition), $payload);

        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_rate_limit_returns_429(): void
    {
        config([
            'submissions.rate_limits.session_per_minute' => 1,
            'submissions.rate_limits.session_per_hour' => 1,
            'submissions.rate_limits.ip_per_minute' => 1,
            'submissions.rate_limits.ip_per_hour' => 1,
        ]);
        [$competition] = $this->createCompetition();

        $this->postSubmission($competition)->assertRedirect();
        $this->postSubmission($competition)->assertStatus(429)
            ->assertHeader('Retry-After');
    }

    public function test_more_than_five_files_are_rejected(): void
    {
        Storage::fake('public');
        [$competition] = $this->createCompetition(6);
        $files = [];

        foreach ($competition->formFields as $field) {
            $files['fields'][$field->id] = UploadedFile::fake()->create(
                "file-{$field->id}.pdf",
                1,
                'application/pdf'
            );
        }

        $this->postSubmission($competition, $files)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('submissions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_total_file_size_over_twenty_mb_is_rejected(): void
    {
        Storage::fake('public');
        [$competition] = $this->createCompetition(3);
        $files = [];

        foreach ($competition->formFields as $field) {
            $files['fields'][$field->id] = UploadedFile::fake()->create(
                "file-{$field->id}.pdf",
                7168,
                'application/pdf'
            );
        }

        $this->postSubmission($competition, $files)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('submissions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_file_in_unauthorized_field_is_rejected(): void
    {
        Storage::fake('public');
        [$competition] = $this->createCompetition();

        $this->postSubmission($competition, [
            'fields' => [999999 => UploadedFile::fake()->create('attack.pdf', 1)],
        ])->assertSessionHasErrors('files');

        $this->assertDatabaseCount('submissions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_request_leaves_no_data_or_files_and_token_can_retry(): void
    {
        Storage::fake('public');
        [$competition] = $this->createCompetition(1);
        $token = $this->tokenFor($competition);
        $field = $competition->formFields->first();

        $this->postSubmission($competition, [
            'project_title' => '',
            'fields' => [$field->id => $this->realPdf('valid.pdf')],
        ], $token)->assertSessionHasErrors('project_title');

        $this->assertDatabaseCount('submissions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->postSubmission($competition, [
            'fields' => [$field->id => $this->realPdf('valid.pdf')],
        ], $token)->assertRedirect();
        $this->assertDatabaseCount('submissions', 1);
    }

    private function postSubmission(
        Competition $competition,
        array $overrides = [],
        ?string $token = null
    ) {
        return $this->post(
            route('competitions.submissions.store', $competition),
            array_replace_recursive(
                $this->validPayload(),
                $overrides,
                ['form_guard_token' => $token ?? $this->tokenFor($competition)]
            )
        );
    }

    private function realPdf(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pdf-');
        file_put_contents($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");

        return new UploadedFile($path, $name, null, null, true);
    }

    private function validPayload(): array
    {
        return [
            'project_title' => 'Protected project',
            'contact_name' => 'Test Person',
            'contact_email' => 'person@example.com',
            'contact_phone' => '0800000000',
            'terms' => '1',
            'website' => '',
        ];
    }

    private function tokenFor(Competition $competition, int $ageSeconds = 2): string
    {
        return Crypt::encryptString(json_encode([
            'competition_id' => $competition->id,
            'issued_at' => Carbon::now()->subSeconds($ageSeconds)->timestamp,
            'nonce' => bin2hex(random_bytes(20)),
        ], JSON_THROW_ON_ERROR));
    }

    private function createCompetition(int $fileFieldCount = 0): array
    {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => 'Competition Admin '.uniqid(),
            'display_name' => 'Competition Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::create([
            'role_id' => $roleId,
            'username' => 'admin-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId,
            'created_by' => $admin->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subHour(),
            'registration_end' => now()->addHour(),
            'status' => 'open',
        ]);

        $count = max(1, $fileFieldCount);
        for ($index = 1; $index <= $count; $index++) {
            CompetitionFormField::create([
                'competition_id' => $competition->id,
                'label' => $fileFieldCount > 0 ? "File {$index}" : 'Details',
                'field_name' => "field_{$index}",
                'field_type' => $fileFieldCount > 0 ? 'file' : 'text',
                'max_file_size' => 10,
                'is_required' => false,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }

        return [$competition->load('formFields'), $admin];
    }
}
