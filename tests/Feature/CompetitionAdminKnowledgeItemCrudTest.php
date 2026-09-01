<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\KnowledgeItem;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompetitionAdminKnowledgeItemCrudTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_admin_can_open_index_create_and_legacy_submission_page(): void
    {
        $admin = $this->user('admin', 'Competition Admin');

        $this->actingAs($admin)
            ->get(route('competition-admin.km.index'))
            ->assertOk()
            ->assertViewIs('competition-admin.km.index');
        $this->actingAs($admin)
            ->get(route('competition-admin.km.create'))
            ->assertOk()
            ->assertViewIs('competition-admin.km.create');
        $this->actingAs($admin)
            ->get('/competition-admin/km/submissions')
            ->assertOk()
            ->assertViewIs('competition-admin.km.submissions');
    }

    public function test_judge_cannot_open_competition_admin_km(): void
    {
        $judge = $this->user('judge', 'Judge');

        $this->actingAs($judge)
            ->get(route('competition-admin.km.index'))
            ->assertForbidden();
    }

    public function test_index_only_lists_owned_items_and_supports_filters(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $other = $this->user('other', 'Competition Admin');
        $category = $this->category('Primary');
        $otherCategory = $this->category('Other');
        $competition = $this->competition($admin, $category, 'Search Competition');
        $submission = $this->submission($competition);

        $manual = $this->item($admin, $category, [
            'title' => 'Search Manual',
            'status' => 'draft',
        ]);
        $competitionItem = $this->item($admin, $category, [
            'submission_id' => $submission->id,
            'title' => 'Competition Item',
            'status' => 'published',
            'published_at' => now(),
        ]);
        $otherCategoryItem = $this->item($admin, $otherCategory);
        $foreign = $this->item($other, $category, ['title' => 'Foreign Item']);

        $this->assertIndexContains($admin, [], [$manual, $competitionItem, $otherCategoryItem], [$foreign]);
        $this->assertIndexContains($admin, ['search' => 'Search Competition'], [$competitionItem], [$manual]);
        $this->assertIndexContains($admin, ['category_id' => $otherCategory->id], [$otherCategoryItem], [$manual]);
        $this->assertIndexContains($admin, ['status' => 'published'], [$competitionItem], [$manual]);
        $this->assertIndexContains($admin, ['source' => 'manual'], [$manual, $otherCategoryItem], [$competitionItem]);
        $this->assertIndexContains($admin, ['source' => 'competition'], [$competitionItem], [$manual]);
    }

    public function test_index_paginates_fifteen_items(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();

        foreach (range(1, 16) as $index) {
            $this->item($admin, $category, ['title' => "Item {$index}"]);
        }

        $this->actingAs($admin)
            ->get(route('competition-admin.km.index'))
            ->assertViewHas('knowledgeItems', fn ($items) =>
                $items->count() === 15 && $items->total() === 16
            );
    }

    public function test_admin_can_create_manual_item_with_secure_defaults_and_files(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();

        $response = $this->actingAs($admin)->post(
            route('competition-admin.km.store'),
            [
                'title' => 'Manual KM',
                'category_id' => $category->id,
                'summary' => 'Summary',
                'content' => 'Content',
                'cover_image' => $this->png('original-cover.png'),
                'attachment' => $this->pdf('คู่มือความรู้.pdf'),
            ]
        );

        $response->assertRedirect();
        $item = KnowledgeItem::sole();
        $this->assertNull($item->submission_id);
        $this->assertSame($admin->id, $item->created_by);
        $this->assertSame('draft', $item->status);
        $this->assertNull($item->published_at);
        $this->assertFalse($item->is_featured);
        $this->assertSame('คู่มือความรู้.pdf', $item->attachment_original_name);
        $this->assertStringStartsWith('knowledge-items/covers/', $item->cover_image);
        $this->assertStringStartsWith('knowledge-items/attachments/', $item->attachment_path);
        $this->assertStringNotContainsString('original-cover', $item->cover_image);
        Storage::disk('public')->assertExists($item->cover_image);
        Storage::disk('public')->assertExists($item->attachment_path);
    }

    public function test_forged_server_fields_are_rejected_without_storing_files(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $other = $this->user('other', 'Competition Admin');
        $category = $this->category();
        $submission = $this->submission($this->competition($other, $category));

        $this->actingAs($admin)->post(route('competition-admin.km.store'), [
            'title' => 'Forged KM',
            'category_id' => $category->id,
            'cover_image' => $this->png('cover.png'),
            'created_by' => $other->id,
            'submission_id' => $submission->id,
            'status' => 'published',
            'is_featured' => true,
        ])->assertSessionHasErrors([
            'created_by',
            'submission_id',
            'status',
            'is_featured',
        ]);

        $this->assertDatabaseCount('knowledge_items', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_database_failures_cleanup_new_files_and_preserve_old_files(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $this->actingAs($admin);
        $this->withoutExceptionHandling();

        DB::unprepared(
            "CREATE TRIGGER fail_km_insert BEFORE INSERT ON knowledge_items "
            ."BEGIN SELECT RAISE(ABORT, 'forced insert failure'); END"
        );

        try {
            $this->post(route('competition-admin.km.store'), [
                'title' => 'Will fail',
                'category_id' => $category->id,
                'cover_image' => $this->png('new-cover.png'),
                'attachment' => $this->pdf('new.pdf'),
            ]);
            $this->fail('Store should have thrown a database exception.');
        } catch (QueryException) {
            $this->assertSame([], Storage::disk('public')->allFiles());
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS fail_km_insert');
        }

        $oldCover = 'knowledge-items/covers/original.png';
        $oldAttachment = 'knowledge-items/attachments/original.pdf';
        Storage::disk('public')->put($oldCover, 'original cover');
        Storage::disk('public')->put($oldAttachment, 'original attachment');
        $item = $this->item($admin, $category, [
            'cover_image' => $oldCover,
            'attachment_path' => $oldAttachment,
            'attachment_original_name' => 'original.pdf',
        ]);

        DB::unprepared(
            "CREATE TRIGGER fail_km_update BEFORE UPDATE ON knowledge_items "
            ."BEGIN SELECT RAISE(ABORT, 'forced update failure'); END"
        );

        try {
            $this->put(route('competition-admin.km.update', $item), [
                'title' => 'Will also fail',
                'category_id' => $category->id,
                'cover_image' => $this->png('replacement.png'),
                'attachment' => $this->pdf('replacement.pdf'),
            ]);
            $this->fail('Update should have thrown a database exception.');
        } catch (QueryException) {
            Storage::disk('public')->assertExists($oldCover);
            Storage::disk('public')->assertExists($oldAttachment);
            $this->assertEqualsCanonicalizing(
                [$oldCover, $oldAttachment],
                Storage::disk('public')->allFiles()
            );
            $this->assertSame($oldCover, $item->fresh()->cover_image);
            $this->assertSame(
                $oldAttachment,
                $item->fresh()->attachment_path
            );
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS fail_km_update');
        }
    }

    public function test_owner_can_show_edit_update_publish_and_unpublish(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $item = $this->item($admin, $category);

        $this->actingAs($admin)
            ->get(route('competition-admin.km.show', $item))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('competition-admin.km.edit', $item))
            ->assertOk();
        $this->actingAs($admin)->put(
            route('competition-admin.km.update', $item),
            [
                'title' => 'Updated title',
                'category_id' => $category->id,
                'summary' => 'Updated summary',
                'content' => 'Updated content',
            ]
        )->assertRedirect(route('competition-admin.km.show', $item));

        $item->refresh();
        $this->assertSame('Updated title', $item->title);
        $this->assertSame($admin->id, $item->created_by);
        $this->assertNull($item->submission_id);

        $this->actingAs($admin)
            ->post(route('competition-admin.km.publish', $item))
            ->assertRedirect();
        $this->assertSame('published', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->published_at);

        $this->actingAs($admin)
            ->delete(route('competition-admin.km.unpublish', $item))
            ->assertRedirect();
        $this->assertSame('draft', $item->fresh()->status);
        $this->assertNull($item->fresh()->published_at);
    }

    public function test_replacing_files_deletes_old_managed_files_after_update(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $oldCover = 'knowledge-items/covers/old.png';
        $oldAttachment = 'knowledge-items/attachments/old.pdf';
        Storage::disk('public')->put($oldCover, 'old cover');
        Storage::disk('public')->put($oldAttachment, 'old attachment');
        $item = $this->item($admin, $category, [
            'cover_image' => $oldCover,
            'attachment_path' => $oldAttachment,
            'attachment_original_name' => 'old.pdf',
        ]);

        $this->actingAs($admin)->put(
            route('competition-admin.km.update', $item),
            [
                'title' => $item->title,
                'category_id' => $category->id,
                'cover_image' => $this->png('new.png'),
                'attachment' => $this->pdf('ใหม่.pdf'),
                'remove_cover_image' => 1,
                'remove_attachment' => 1,
            ]
        )->assertRedirect();

        $item->refresh();
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertMissing($oldAttachment);
        Storage::disk('public')->assertExists($item->cover_image);
        Storage::disk('public')->assertExists($item->attachment_path);
        $this->assertSame('ใหม่.pdf', $item->attachment_original_name);
    }

    public function test_remove_flags_only_delete_managed_files(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $submissionCover = 'submissions/source.png';
        $managedAttachment = 'knowledge-items/attachments/manual.pdf';
        Storage::disk('public')->put($submissionCover, 'source');
        Storage::disk('public')->put($managedAttachment, 'manual');
        $item = $this->item($admin, $category, [
            'cover_image' => $submissionCover,
            'attachment_path' => $managedAttachment,
            'attachment_original_name' => 'manual.pdf',
        ]);

        $this->actingAs($admin)->put(
            route('competition-admin.km.update', $item),
            [
                'title' => $item->title,
                'category_id' => $category->id,
                'remove_cover_image' => 1,
                'remove_attachment' => 1,
            ]
        )->assertRedirect();

        $item->refresh();
        $this->assertNull($item->cover_image);
        $this->assertNull($item->attachment_path);
        $this->assertNull($item->attachment_original_name);
        Storage::disk('public')->assertExists($submissionCover);
        Storage::disk('public')->assertMissing($managedAttachment);
    }

    public function test_destroy_manual_item_deletes_managed_files(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $cover = 'knowledge-items/covers/cover.png';
        $attachment = 'knowledge-items/attachments/file.pdf';
        Storage::disk('public')->put($cover, 'cover');
        Storage::disk('public')->put($attachment, 'file');
        $item = $this->item($admin, $category, [
            'cover_image' => $cover,
            'attachment_path' => $attachment,
        ]);

        $this->actingAs($admin)
            ->delete(route('competition-admin.km.destroy', $item))
            ->assertRedirect(route('competition-admin.km.index'));

        $this->assertDatabaseMissing('knowledge_items', ['id' => $item->id]);
        Storage::disk('public')->assertMissing($cover);
        Storage::disk('public')->assertMissing($attachment);
    }

    public function test_other_admin_cannot_access_any_item_action(): void
    {
        $owner = $this->user('owner', 'Competition Admin');
        $other = $this->user('other', 'Competition Admin');
        $category = $this->category();
        $item = $this->item($owner, $category);
        $data = ['title' => 'Attack', 'category_id' => $category->id];

        $this->actingAs($other)->get(route('competition-admin.km.show', $item))->assertForbidden();
        $this->actingAs($other)->get(route('competition-admin.km.edit', $item))->assertForbidden();
        $this->actingAs($other)->put(route('competition-admin.km.update', $item), $data)->assertForbidden();
        $this->actingAs($other)->delete(route('competition-admin.km.destroy', $item))->assertForbidden();
        $this->actingAs($other)->post(route('competition-admin.km.publish', $item))->assertForbidden();
        $this->actingAs($other)->delete(route('competition-admin.km.unpublish', $item))->assertForbidden();
        $this->assertDatabaseHas('knowledge_items', ['id' => $item->id]);
    }

    public function test_destroy_competition_item_preserves_submission_file_and_source_file(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $submission = $this->submission($this->competition($admin, $category));
        $sourcePath = 'submissions/source.pdf';
        Storage::disk('public')->put($sourcePath, 'source');
        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'original_name' => 'source.pdf',
            'stored_name' => 'source.pdf',
            'file_path' => $sourcePath,
            'file_extension' => 'pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 6,
            'is_primary' => true,
        ]);
        $item = $this->item($admin, $category, [
            'submission_id' => $submission->id,
            'cover_image' => $sourcePath,
        ]);

        $this->actingAs($admin)
            ->delete(route('competition-admin.km.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
        $this->assertDatabaseHas('submission_files', ['id' => $submissionFile->id]);
        Storage::disk('public')->assertExists($sourcePath);
    }

    public function test_submission_publication_sets_missing_owner_and_category(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $judge = $this->user('judge', 'Judge');
        $category = $this->category();
        $competition = $this->competition($admin, $category);
        $submission = $this->submission($competition);
        JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $admin->id,
            'status' => 'ended',
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);
        $rubric = Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Quality',
            'max_score' => 100,
            'weight' => 100,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $assignment = JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
        ]);
        Score::create([
            'submission_id' => $submission->id,
            'rubric_id' => $rubric->id,
            'judge_assignment_id' => $assignment->id,
            'score' => 90,
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->post(
            route('competition-admin.submissions.km.publish', $submission)
        )->assertRedirect();

        $item = KnowledgeItem::sole();
        $this->assertSame($admin->id, $item->created_by);
        $this->assertSame($category->id, $item->category_id);
    }

    private function assertIndexContains(
        User $admin,
        array $query,
        array $included,
        array $excluded
    ): void {
        $this->actingAs($admin)
            ->get(route('competition-admin.km.index', $query))
            ->assertOk()
            ->assertViewHas('knowledgeItems', function ($items) use ($included, $excluded) {
                foreach ($included as $item) {
                    if (! $items->contains('id', $item->id)) {
                        return false;
                    }
                }
                foreach ($excluded as $item) {
                    if ($items->contains('id', $item->id)) {
                        return false;
                    }
                }
                return true;
            });
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

    private function category(string $name = 'Category'): CompetitionCategory
    {
        return CompetitionCategory::create([
            'category_name' => $name.' '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function item(
        User $owner,
        CompetitionCategory $category,
        array $attributes = []
    ): KnowledgeItem {
        return KnowledgeItem::create(array_merge([
            'submission_id' => null,
            'created_by' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Knowledge '.uniqid(),
            'status' => 'draft',
        ], $attributes));
    }

    private function competition(
        User $owner,
        CompetitionCategory $category,
        ?string $title = null
    ): Competition {
        return Competition::create([
            'category_id' => $category->id,
            'created_by' => $owner->id,
            'title' => $title ?? 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDays(2),
            'registration_end' => now()->subDay(),
            'status' => 'closed',
        ]);
    }

    private function submission(Competition $competition): Submission
    {
        return Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Submission '.uniqid(),
            'project_description' => 'Description',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    private function png(string $name): UploadedFile
    {
        return $this->upload($name, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
    }

    private function pdf(string $name): UploadedFile
    {
        return $this->upload(
            $name,
            "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF"
        );
    }

    private function upload(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'km-crud-');
        $this->assertNotFalse($path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return new UploadedFile($path, $name, null, null, true);
    }
}
