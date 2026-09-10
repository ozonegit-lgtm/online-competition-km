<?php

namespace Tests\Feature;

use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class LegacyKnowledgeAttachmentMigrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Log::spy();
    }

    public function test_dry_run_preserves_all_data_and_files(): void
    {
        [$item, $path] = $this->legacy();
        $this->artisan('knowledge-items:import-legacy-attachments')->assertSuccessful();
        $this->assertNull($item->fresh()->attachment_path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
        Storage::disk('public')->assertExists($path);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_migration_preserves_bytes_and_name_uses_private_route_and_cleans_on_delete(): void
    {
        [$item, $path, $owner] = $this->legacy();
        $before = $item->getAttributes();
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertSuccessful();
        $item->refresh();
        $this->assertStringStartsWith('knowledge-items/attachments/', $item->attachment_path);
        $this->assertSame('legacy.doc', $item->attachment_original_name);
        $this->assertSame('legacy document bytes', Storage::disk('local')->get($item->attachment_path));
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseCount('knowledge_item_files', 0);
        foreach (array_diff_key($before, array_flip(['attachment_path', 'attachment_original_name'])) as $key => $value) {
            $this->assertEquals($value, $item->getAttributes()[$key], $key);
        }
        $this->get(route('knowledge-items.attachment', $item))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->actingAs($owner)->get(route('competition-admin.km.show', $item))
            ->assertOk()->assertSee(route('knowledge-items.attachment', $item))->assertSee('legacy.doc');
        $this->actingAs($owner)->delete(route('competition-admin.km.unpublish', $item))->assertRedirect();
        $this->app['auth']->forgetGuards();
        $this->get(route('knowledge-items.attachment', $item))->assertNotFound();
        $this->actingAs($owner)->get(route('knowledge-items.attachment', $item))->assertOk();
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertSuccessful();
        $this->actingAs($owner)->delete(route('competition-admin.km.destroy', $item))->assertRedirect();
        Storage::disk('local')->assertMissing($item->attachment_path);
    }

    public function test_multiple_attachments_abort_without_overwriting_or_discarding_any_file(): void
    {
        [$item, $path] = $this->legacy();
        $this->addLegacy($item, 'second.doc');
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        $this->assertNull($item->fresh()->attachment_path);
        $this->assertDatabaseCount('knowledge_item_files', 2);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists("knowledge-items/{$item->id}/files/second.doc");
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_existing_canonical_attachment_conflict_preserves_both_references(): void
    {
        [$item, $path] = $this->legacy();
        $item->update(['attachment_path' => 'knowledge-items/attachments/current.doc']);
        Storage::disk('local')->put($item->attachment_path, 'current');
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        $this->assertSame('knowledge-items/attachments/current.doc', $item->fresh()->attachment_path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
    }

    public function test_missing_or_corrupt_source_is_not_retired(): void
    {
        [$item, $path] = $this->legacy();
        Storage::disk('public')->put($path, 'wrong size');
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        Storage::disk('public')->delete($path);
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        $this->assertNull($item->fresh()->attachment_path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
    }

    public function test_copy_failure_preserves_source_and_reference(): void
    {
        [$item, $path] = $this->legacy();
        $disk = Mockery::mock(Storage::disk('local'));
        $disk->shouldReceive('put')->once()->andReturn(false);
        Storage::set('local', $disk);
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        $this->assertNull($item->fresh()->attachment_path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
    }

    public function test_source_cleanup_failure_keeps_retry_record_and_rerun_finishes(): void
    {
        [$item, $path, $owner] = $this->legacy();
        $realDisk = Storage::disk('public');
        $disk = Mockery::mock($realDisk);
        $disk->shouldReceive('delete')->once()->with($path)->andReturn(false);
        Storage::set('public', $disk);
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        $this->assertNotNull($item->fresh()->attachment_path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
        $this->actingAs($owner)->delete(route('competition-admin.km.destroy', $item))->assertSessionHasErrors('attachment');
        $this->assertDatabaseHas('knowledge_items', ['id' => $item->id]);
        Storage::set('public', $realDisk);
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertSuccessful();
        $this->assertDatabaseCount('knowledge_item_files', 0);
        $realDisk->assertMissing($path);
    }

    public function test_unmigrated_delete_is_blocked_for_both_admin_roles(): void
    {
        [$item, $path, $owner] = $this->legacy();
        $this->actingAs($owner)->delete(route('competition-admin.km.destroy', $item))->assertSessionHasErrors('attachment');
        $role = DB::table('roles')->insertGetId(['role_name' => 'Super Admin', 'display_name' => 'Super Admin']);
        $super = User::create(['role_id' => $role, 'username' => 'super', 'email' => 'super@example.com', 'password' => 'password', 'is_active' => true]);
        $this->flushSession();
        auth()->forgetGuards();
        $this->actingAs($super)->delete(route('superadmin.km.destroy', $item))->assertSessionHasErrors('attachment');
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
    }

    public function test_database_failure_keeps_source_and_removes_unreferenced_private_copy(): void
    {
        [$item, $path] = $this->legacy();
        DB::listen(function ($query): void {
            if (str_starts_with(strtolower($query->sql), 'update "knowledge_items"')) {
                throw new \RuntimeException('Simulated database update failure');
            }
        });
        $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
        $this->assertNull($item->fresh()->attachment_path);
        Storage::disk('public')->assertExists($path);
        $this->assertDatabaseCount('knowledge_item_files', 1);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_destination_symlink_is_rejected_before_any_write(): void
    {
        [$item, $path] = $this->legacy();
        Storage::disk('local')->makeDirectory('knowledge-items');
        $outside = Storage::disk('public')->path('');
        $link = Storage::disk('local')->path('knowledge-items/attachments');
        $this->assertTrue(symlink($outside, $link));
        try {
            $this->artisan('knowledge-items:import-legacy-attachments', ['--execute' => true])->assertFailed();
            $this->assertNull($item->fresh()->attachment_path);
            $this->assertSame([$path], Storage::disk('public')->allFiles());
        } finally {
            unlink($link);
        }
    }

    private function legacy(): array
    {
        $role = DB::table('roles')->insertGetId(['role_name' => 'Competition Admin', 'display_name' => 'Competition Admin']);
        $owner = User::create(['role_id' => $role, 'username' => 'owner', 'email' => 'owner@example.com', 'password' => 'password', 'is_active' => true]);
        $item = KnowledgeItem::create(['created_by' => $owner->id, 'title' => 'Legacy', 'status' => 'published', 'published_at' => now()]);
        return [$item, $this->addLegacy($item, 'legacy.doc'), $owner];
    }

    private function addLegacy(KnowledgeItem $item, string $name): string
    {
        $path = "knowledge-items/{$item->id}/files/{$name}";
        Storage::disk('public')->put($path, 'legacy document bytes');
        DB::table('knowledge_item_files')->insert([
            'knowledge_item_id' => $item->id, 'original_name' => $name, 'stored_name' => $name,
            'file_path' => $path, 'file_extension' => 'doc', 'mime_type' => 'application/msword',
            'file_size' => strlen('legacy document bytes'),
        ]);
        return $path;
    }
}
