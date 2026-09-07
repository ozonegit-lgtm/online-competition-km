<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionFileAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_anonymous_cannot_open_unpublished_submission_file(): void
    {
        $context = $this->context();

        $this->get(route('submission-files.show', $context['file']))->assertNotFound();
    }

    public function test_owner_super_admin_and_accepted_judge_can_open_private_file(): void
    {
        $context = $this->context();
        $super = $this->user('super', 'Super Admin');
        $judge = $this->user('judge', 'Judge');
        JudgeAssignment::create([
            'competition_id' => $context['competition']->id,
            'judge_id' => $judge->id,
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
        ]);

        foreach ([$context['owner'], $super, $judge] as $user) {
            $this->actingAs($user)
                ->get(route('submission-files.show', $context['file']))
                ->assertOk();
        }
    }

    public function test_other_admin_unaccepted_judge_and_unrelated_judge_cannot_open_file(): void
    {
        $context = $this->context();
        $otherAdmin = $this->user('other-admin', 'Competition Admin');
        $pendingJudge = $this->user('pending', 'Judge');
        $otherJudge = $this->user('other-judge', 'Judge');
        JudgeAssignment::create([
            'competition_id' => $context['competition']->id,
            'judge_id' => $pendingJudge->id,
            'assignment_status' => 'pending',
        ]);

        foreach ([$otherAdmin, $pendingJudge, $otherJudge] as $user) {
            $this->actingAs($user)
                ->get(route('submission-files.show', $context['file']))
                ->assertNotFound();
        }
    }

    public function test_published_km_allows_anonymous_access_but_unpublish_revokes_it(): void
    {
        $context = $this->context();
        $item = KnowledgeItem::create([
            'submission_id' => $context['submission']->id,
            'created_by' => $context['owner']->id,
            'category_id' => $context['competition']->category_id,
            'title' => 'Published project',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $url = route('submission-files.show', $context['file']);

        $this->get($url)->assertOk();
        $item->update(['status' => 'draft', 'published_at' => null]);
        $this->get($url)->assertNotFound();
    }

    public function test_disqualified_submission_is_not_public_even_when_km_is_published(): void
    {
        $context = $this->context(['submission_status' => 'disqualified']);
        KnowledgeItem::create([
            'submission_id' => $context['submission']->id,
            'title' => 'Disqualified project',
            'status' => 'published',
        ]);

        $this->get(route('submission-files.show', $context['file']))->assertNotFound();
    }

    public function test_invalid_paths_and_missing_file_return_not_found(): void
    {
        foreach ([
            '../secret.pdf',
            'knowledge-items/attachments/secret.pdf',
            '/submissions/absolute.pdf',
            'C:\\submissions\\absolute.pdf',
            "submissions/bad\0.pdf",
            'submissions/missing.pdf',
        ] as $path) {
            $context = $this->context(['path' => $path, 'store' => false]);
            $this->actingAs($context['owner'])
                ->get(route('submission-files.show', $context['file']))
                ->assertNotFound();
        }
    }

    public function test_inline_and_download_responses_use_safe_original_filename(): void
    {
        $context = $this->context(['original_name' => "folder/..\\รายงาน\0.pdf"]);

        $inline = $this->actingAs($context['owner'])
            ->get(route('submission-files.show', $context['file']))
            ->assertOk();
        $this->assertStringStartsWith(
            'inline;',
            strtolower($inline->headers->get('content-disposition'))
        );
        $this->assertStringContainsString(
            strtolower("filename*=utf-8''".rawurlencode('รายงาน.pdf')),
            strtolower($inline->headers->get('content-disposition'))
        );

        $response = $this->actingAs($context['owner'])
            ->get(route('submission-files.download', $context['file']))
            ->assertOk();
        $this->assertStringContainsString(
            strtolower("filename*=utf-8''".rawurlencode('รายงาน.pdf')),
            strtolower($response->headers->get('content-disposition'))
        );
    }

    public function test_record_cannot_reference_another_submissions_file(): void
    {
        $one = $this->context();
        $two = $this->context();
        $one['file']->update(['file_path' => $two['file']->file_path]);
        $this->actingAs($one['owner'])->get($one['file']->file_url)->assertNotFound();
        $this->get($one['file']->download_url)->assertNotFound();
    }

    public function test_legacy_km_cover_requires_matching_submission_image_and_publication(): void
    {
        $one = $this->context();
        $two = $this->context();
        $one['file']->update(['mime_type' => 'image/png']);
        $two['file']->update(['mime_type' => 'image/png']);
        $item = KnowledgeItem::create([
            'submission_id' => $one['submission']->id,
            'created_by' => $one['owner']->id,
            'title' => 'Legacy cover',
            'status' => 'published',
            'cover_image' => $one['file']->file_path,
        ]);
        $url = $item->cover_image_url;
        $this->get($url)->assertOk();
        $item->update(['cover_image' => $two['file']->file_path]);
        $this->get($url)->assertNotFound();
        $item->update(['cover_image' => $one['file']->file_path, 'status' => 'draft']);
        $this->get($url)->assertNotFound();
        $this->actingAs($one['owner'])->get($url)->assertOk();
        auth()->logout();
        $item->update(['status' => 'published']);
        $one['submission']->update(['status' => 'disqualified']);
        $this->get($url)->assertNotFound();
    }

    public function test_home_and_admin_submission_html_use_application_routes(): void
    {
        $context = $this->context();
        $context['file']->update(['mime_type' => 'image/png']);
        $item = KnowledgeItem::create([
            'submission_id' => $context['submission']->id,
            'created_by' => $context['owner']->id,
            'title' => 'Legacy HTML',
            'status' => 'published',
            'cover_image' => $context['file']->file_path,
        ]);
        $this->get(route('home'))->assertOk()->assertSee('src="'.$item->cover_image_url.'"', false)
            ->assertDontSee('/storage/submissions/', false);
        $this->actingAs($context['owner'])->get(route('competition-admin.submissions.index'))
            ->assertOk()->assertSee('src="'.$context['file']->file_url.'"', false)
            ->assertDontSee('/storage/submissions/', false);
        foreach (['competition-admin.km.show', 'competition-admin.km.edit'] as $route) {
            $this->get(route($route, $item))->assertOk()->assertSee($item->cover_image_url, false);
        }
        $super = $this->user('super', 'Super Admin');
        foreach (['superadmin.km.show', 'superadmin.km.edit'] as $route) {
            $this->actingAs($super)->get(route($route, $item))->assertOk()->assertSee($item->cover_image_url, false);
        }
    }

    public function test_submission_list_does_not_render_documents_as_images(): void
    {
        $context = $this->context();
        $this->actingAs($context['owner'])->get(route('competition-admin.submissions.index'))
            ->assertOk()->assertDontSee('src="'.$context['file']->file_url.'"', false);
    }

    private function context(array $options = []): array
    {
        $owner = $this->user('owner', 'Competition Admin');
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId,
            'created_by' => $owner->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'status' => 'open',
        ]);
        $submission = Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Project',
            'contact_name' => 'Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'status' => $options['submission_status'] ?? 'submitted',
            'submitted_at' => now(),
        ]);
        $path = $options['path'] ?? "submissions/{$competition->id}/{$submission->submission_code}/report.pdf";
        $file = SubmissionFile::create([
            'submission_id' => $submission->id,
            'original_name' => $options['original_name'] ?? 'report.pdf',
            'stored_name' => 'random.pdf',
            'file_path' => $path,
            'file_extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 15,
            'is_primary' => true,
        ]);
        if ($options['store'] ?? true) {
            Storage::disk('local')->put($path, "%PDF-1.4\n%%EOF");
        }

        return compact('owner', 'competition', 'submission', 'file');
    }

    private function user(string $prefix, string $roleName): User
    {
        $roleId = DB::table('roles')->where('role_name', $roleName)->value('id')
            ?? DB::table('roles')->insertGetId([
                'role_name' => $roleName,
                'display_name' => $roleName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        return User::create([
            'role_id' => $roleId,
            'username' => $prefix.'-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }
}
