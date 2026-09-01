<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuperAdminKnowledgeItemCrudTest extends TestCase
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
            @unlink($path);
        }
        parent::tearDown();
    }

    public function test_only_super_admin_can_open_index_and_create(): void
    {
        $super = $this->user('super', 'Super Admin');
        $admin = $this->user('admin', 'Competition Admin');
        $judge = $this->user('judge', 'Judge');

        $this->actingAs($super)->get(route('superadmin.km.index'))->assertOk();
        $this->actingAs($super)->get(route('superadmin.km.create'))->assertOk();
        $this->actingAs($admin)->get(route('superadmin.km.index'))->assertForbidden();
        $this->actingAs($judge)->get(route('superadmin.km.index'))->assertForbidden();
    }

    public function test_index_shows_all_owners_and_unassigned_and_supports_filters(): void
    {
        $super = $this->user('super', 'Super Admin');
        $owner = $this->user('owner-search', 'Competition Admin');
        $other = $this->user('other', 'Competition Admin');
        $catA = $this->category('Alpha');
        $catB = $this->category('Beta');
        $title = $this->item($owner->id, $catA, ['title' => 'Unique title', 'summary' => 'first']);
        $summary = $this->item($other->id, $catB, ['title' => 'Other', 'summary' => 'Unique summary', 'status' => 'published']);
        $orphan = $this->item(null, $catA, ['title' => 'Orphan']);
        $competition = $this->competition($owner, $catB, 'Unique competition');
        $competitionItem = $this->item($owner->id, $catB, ['submission_id' => $this->submission($competition)->id]);

        $this->assertIndex($super, [], [$title, $summary, $orphan, $competitionItem]);
        $this->assertIndex($super, ['search' => 'Unique title'], [$title], [$summary]);
        $this->assertIndex($super, ['search' => 'Unique summary'], [$summary], [$title]);
        $this->assertIndex($super, ['search' => $owner->username], [$title, $competitionItem], [$summary]);
        $this->assertIndex($super, ['search' => 'Unique competition'], [$competitionItem], [$title]);
        $this->assertIndex($super, ['category_id' => $catA->id], [$title, $orphan], [$summary]);
        $this->assertIndex($super, ['status' => 'published'], [$summary], [$title]);
        $this->assertIndex($super, ['source' => 'manual'], [$title, $orphan], [$competitionItem]);
        $this->assertIndex($super, ['source' => 'competition'], [$competitionItem], [$title]);
        $this->assertIndex($super, ['owner' => $owner->id], [$title, $competitionItem], [$summary, $orphan]);
        $this->assertIndex($super, ['owner' => 'unassigned'], [$orphan], [$title]);
        $this->actingAs($super)->get(route('superadmin.km.index', ['owner' => 'invalid']))->assertOk();
    }

    public function test_index_paginates_fifteen_items(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->category();
        foreach (range(1, 16) as $i) {
            $this->item($super->id, $category, ['title' => "Item {$i}"]);
        }
        $this->actingAs($super)->get(route('superadmin.km.index'))->assertViewHas('knowledgeItems', fn ($items) => $items->count() === 15 && $items->hasPages());
    }

    public function test_store_creates_secure_manual_item_with_random_files(): void
    {
        $super = $this->user('super', 'Super Admin');
        $other = $this->user('other', 'Competition Admin');
        $category = $this->category();
        $response = $this->actingAs($super)->post(route('superadmin.km.store'), [
            'title' => 'Manual', 'category_id' => $category->id,
            'cover_image' => $this->png('original.png'),
            'attachment' => $this->pdf('คู่มือภาษาไทย.pdf'),
        ]);
        $item = KnowledgeItem::sole();
        $response->assertRedirect(route('superadmin.km.show', $item));
        $this->assertSame($super->id, $item->created_by);
        $this->assertNull($item->submission_id);
        $this->assertSame('draft', $item->status);
        $this->assertFalse($item->is_featured);
        $this->assertSame('คู่มือภาษาไทย.pdf', $item->attachment_original_name);
        $this->assertNotSame('original.png', basename($item->cover_image));
        Storage::disk('public')->assertExists($item->cover_image);
        Storage::disk('public')->assertExists($item->attachment_path);

        $this->actingAs($super)->post(route('superadmin.km.store'), [
            'title' => 'Forged', 'category_id' => $category->id,
            'created_by' => $other->id, 'submission_id' => 99,
            'status' => 'published', 'is_featured' => 1,
        ])->assertSessionHasErrors(['created_by', 'submission_id', 'status', 'is_featured']);
    }

    public function test_super_admin_can_show_and_update_other_and_unassigned_items_without_changing_owner(): void
    {
        $super = $this->user('super', 'Super Admin');
        $owner = $this->user('owner', 'Competition Admin');
        $category = $this->category();
        foreach ([$this->item($owner->id, $category), $this->item(null, $category)] as $item) {
            $this->actingAs($super)->get(route('superadmin.km.show', $item))->assertOk();
            $this->actingAs($super)->get(route('superadmin.km.edit', $item))->assertOk();
            $this->actingAs($super)->put(route('superadmin.km.update', $item), [
                'title' => 'Updated '.$item->id, 'category_id' => $category->id,
            ])->assertRedirect(route('superadmin.km.show', $item));
            $this->assertSame($item->created_by, $item->fresh()->created_by);
        }
    }

    public function test_publish_unpublish_feature_and_unfeature_any_item(): void
    {
        $super = $this->user('super', 'Super Admin');
        $item = $this->item(null, $this->category());
        $this->actingAs($super)->post(route('superadmin.km.publish', $item))->assertRedirect();
        $this->assertSame('published', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->published_at);
        $this->actingAs($super)->post(route('superadmin.km.feature', $item))->assertRedirect();
        $this->assertTrue($item->fresh()->is_featured);
        $this->actingAs($super)->delete(route('superadmin.km.unpublish', $item))->assertRedirect();
        $this->assertSame('draft', $item->fresh()->status);
        $this->assertTrue($item->fresh()->is_featured);
        $this->actingAs($super)->delete(route('superadmin.km.unfeature', $item))->assertRedirect();
        $this->assertFalse($item->fresh()->is_featured);
    }

    public function test_replace_and_remove_follow_safe_file_lifecycle(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->category();
        $oldCover = 'knowledge-items/covers/old.png';
        $sourceAttachment = 'submissions/source.pdf';
        Storage::disk('public')->put($oldCover, 'old');
        Storage::disk('public')->put($sourceAttachment, 'source');
        $item = $this->item(null, $category, ['cover_image' => $oldCover, 'attachment_path' => $sourceAttachment, 'attachment_original_name' => 'source.pdf']);

        $this->actingAs($super)->put(route('superadmin.km.update', $item), [
            'title' => 'Replace', 'category_id' => $category->id,
            'cover_image' => $this->png('new.png'), 'attachment' => $this->pdf('ใหม่.pdf'),
            'remove_cover_image' => 1, 'remove_attachment' => 1,
        ])->assertRedirect();
        $item->refresh();
        Storage::disk('public')->assertMissing($oldCover);
        Storage::disk('public')->assertExists($sourceAttachment);
        Storage::disk('public')->assertExists($item->cover_image);
        Storage::disk('public')->assertExists($item->attachment_path);

        $newCover = $item->cover_image;
        $newAttachment = $item->attachment_path;
        $this->actingAs($super)->put(route('superadmin.km.update', $item), [
            'title' => 'Remove', 'category_id' => $category->id,
            'remove_cover_image' => 1, 'remove_attachment' => 1,
        ])->assertRedirect();
        $item->refresh();
        $this->assertNull($item->cover_image);
        $this->assertNull($item->attachment_path);
        $this->assertNull($item->attachment_original_name);
        Storage::disk('public')->assertMissing($newCover);
        Storage::disk('public')->assertMissing($newAttachment);
    }

    public function test_delete_items_of_any_owner_removes_only_managed_files(): void
    {
        $super = $this->user('super', 'Super Admin');
        $owner = $this->user('owner', 'Competition Admin');
        $category = $this->category();
        foreach ([$owner->id, null] as $ownerId) {
            $cover = 'knowledge-items/covers/'.uniqid().'.png';
            Storage::disk('public')->put($cover, 'cover');
            $item = $this->item($ownerId, $category, ['cover_image' => $cover]);
            $this->actingAs($super)->delete(route('superadmin.km.destroy', $item))->assertRedirect(route('superadmin.km.index'));
            $this->assertDatabaseMissing('knowledge_items', ['id' => $item->id]);
            Storage::disk('public')->assertMissing($cover);
        }
    }

    public function test_delete_competition_item_preserves_submission_record_file_and_storage(): void
    {
        $super = $this->user('super', 'Super Admin');
        $owner = $this->user('owner', 'Competition Admin');
        $category = $this->category();
        $submission = $this->submission($this->competition($owner, $category));
        $source = 'submissions/source.pdf';
        Storage::disk('public')->put($source, 'source');
        $file = SubmissionFile::create(['submission_id' => $submission->id, 'original_name' => 'source.pdf', 'stored_name' => 'source.pdf', 'file_path' => $source, 'file_extension' => 'pdf', 'mime_type' => 'application/pdf', 'file_size' => 6, 'is_primary' => true]);
        $item = $this->item($owner->id, $category, ['submission_id' => $submission->id, 'cover_image' => $source]);
        $this->actingAs($super)->delete(route('superadmin.km.destroy', $item))->assertRedirect();
        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
        $this->assertDatabaseHas('submission_files', ['id' => $file->id]);
        Storage::disk('public')->assertExists($source);
    }

    public function test_database_failure_cleans_new_files_and_preserves_old_files(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->category();
        $this->actingAs($super);
        $this->withoutExceptionHandling();
        DB::unprepared("CREATE TRIGGER fail_super_km_insert BEFORE INSERT ON knowledge_items BEGIN SELECT RAISE(ABORT, 'failure'); END");
        try {
            $this->post(route('superadmin.km.store'), ['title' => 'Fail', 'category_id' => $category->id, 'cover_image' => $this->png('new.png')]);
            $this->fail('Expected database exception.');
        } catch (QueryException) {
            $this->assertSame([], Storage::disk('public')->allFiles());
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS fail_super_km_insert');
        }

        $old = 'knowledge-items/covers/old.png';
        Storage::disk('public')->put($old, 'old');
        $item = $this->item(null, $category, ['cover_image' => $old]);
        DB::unprepared("CREATE TRIGGER fail_super_km_update BEFORE UPDATE ON knowledge_items BEGIN SELECT RAISE(ABORT, 'failure'); END");
        try {
            $this->put(route('superadmin.km.update', $item), ['title' => 'Fail', 'category_id' => $category->id, 'cover_image' => $this->png('replacement.png')]);
            $this->fail('Expected database exception.');
        } catch (QueryException) {
            Storage::disk('public')->assertExists($old);
            $this->assertSame([$old], Storage::disk('public')->allFiles());
            $this->assertSame($old, $item->fresh()->cover_image);
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS fail_super_km_update');
        }
    }

    private function assertIndex(User $user, array $query, array $included, array $excluded = []): void
    {
        $this->actingAs($user)->get(route('superadmin.km.index', $query))->assertOk()->assertViewHas('knowledgeItems', function ($items) use ($included, $excluded) {
            foreach ($included as $item) if (! $items->contains('id', $item->id)) return false;
            foreach ($excluded as $item) if ($items->contains('id', $item->id)) return false;
            return true;
        });
    }

    private function user(string $prefix, string $roleName): User
    {
        $roleId = DB::table('roles')->where('role_name', $roleName)->value('id') ?? DB::table('roles')->insertGetId(['role_name' => $roleName, 'display_name' => $roleName, 'created_at' => now(), 'updated_at' => now()]);
        return User::create(['role_id' => $roleId, 'username' => $prefix.'-'.uniqid(), 'email' => uniqid().'@example.com', 'password' => 'password', 'is_active' => true]);
    }

    private function category(string $name = 'Category'): CompetitionCategory
    {
        return CompetitionCategory::create(['category_name' => $name.' '.uniqid(), 'category_slug' => 'category-'.uniqid(), 'is_active' => true]);
    }

    private function item(?int $ownerId, CompetitionCategory $category, array $attributes = []): KnowledgeItem
    {
        return KnowledgeItem::create(array_merge(['submission_id' => null, 'created_by' => $ownerId, 'category_id' => $category->id, 'title' => 'Knowledge '.uniqid(), 'status' => 'draft'], $attributes));
    }

    private function competition(User $owner, CompetitionCategory $category, ?string $title = null): Competition
    {
        return Competition::create(['category_id' => $category->id, 'created_by' => $owner->id, 'title' => $title ?? 'Competition', 'competition_type' => 'individual', 'visibility' => 'public', 'registration_start' => now()->subDays(2), 'registration_end' => now()->subDay(), 'status' => 'closed']);
    }

    private function submission(Competition $competition): Submission
    {
        return Submission::create(['competition_id' => $competition->id, 'submission_code' => 'SUB-'.uniqid(), 'project_title' => 'Project', 'status' => 'submitted', 'submitted_at' => now()]);
    }

    private function png(string $name): UploadedFile
    {
        return $this->upload($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
    }

    private function pdf(string $name): UploadedFile
    {
        return $this->upload($name, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");
    }

    private function upload(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'super-km-');
        $this->assertNotFalse($path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;
        return new UploadedFile($path, $name, null, null, true);
    }
}
