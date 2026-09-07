<?php

namespace Tests\Feature;

use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class KnowledgeItemCleanupFailureTest extends TestCase
{
    use RefreshDatabase;

    public static function cases(): array
    {
        $cases = [];
        foreach (['competition-admin' => 'Competition Admin', 'superadmin' => 'Super Admin'] as $route => $role) {
            foreach (['false', 'exception'] as $failure) {
                $cases["{$route}-{$failure}"] = [$route, $role, $failure];
            }
        }
        return $cases;
    }

    #[DataProvider('cases')]
    public function test_destroy_keeps_committed_deletion_and_reports_each_failed_file(string $route, string $role, string $failure): void
    {
        [$admin, $category] = $this->owner($role);
        $item = $this->item($admin, $category);
        $this->failingDisk($failure);
        Log::spy();

        $this->actingAs($admin)->delete(route($route.'.km.destroy', $item))->assertRedirect(route($route.'.km.index'));

        $this->assertDatabaseMissing('knowledge_items', ['id' => $item->id]);
        $this->assertLogged($item->cover_image);
        $this->assertLogged($item->attachment_path);
    }

    #[DataProvider('cases')]
    public function test_remove_keeps_committed_record_and_reports_cleanup(string $route, string $role, string $failure): void
    {
        [$admin, $category] = $this->owner($role);
        $item = $this->item($admin, $category);
        $this->failingDisk($failure);
        Log::spy();

        $this->actingAs($admin)->put(route($route.'.km.update', $item), [
            'title' => 'Updated', 'category_id' => $category->id,
            'remove_cover_image' => 1, 'remove_attachment' => 1,
        ])->assertRedirect(route($route.'.km.show', $item));

        $this->assertDatabaseHas('knowledge_items', [
            'id' => $item->id, 'title' => 'Updated',
            'cover_image' => null, 'attachment_path' => null, 'attachment_original_name' => null,
        ]);
        $this->assertLogged($item->cover_image);
        $this->assertLogged($item->attachment_path);
    }

    #[DataProvider('cases')]
    public function test_replacement_keeps_new_attachment_when_old_cleanup_fails(string $route, string $role, string $failure): void
    {
        [$admin, $category] = $this->owner($role);
        $item = $this->item($admin, $category);
        $this->failingDisk($failure);
        Log::spy();

        $this->actingAs($admin)->put(route($route.'.km.update', $item), [
            'title' => 'Updated', 'category_id' => $category->id,
            'attachment' => UploadedFile::fake()->createWithContent('new.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF"),
        ])->assertRedirect(route($route.'.km.show', $item));

        $this->assertSame('knowledge-items/attachments/new.pdf', $item->fresh()->attachment_path);
        $this->assertLogged($item->attachment_path);
    }

    #[DataProvider('cases')]
    public function test_failed_create_preserves_original_exception_and_logs_cleanup_failure(string $route, string $role, string $failure): void
    {
        [$admin, $category] = $this->owner($role);
        $this->failingDisk($failure);
        Log::spy();
        KnowledgeItem::creating(fn () => throw new RuntimeException('original DB failure'));
        $this->withoutExceptionHandling();

        try {
            $this->actingAs($admin)->post(route($route.'.km.store'), [
                'title' => 'New', 'category_id' => $category->id,
                'attachment' => UploadedFile::fake()->createWithContent('new.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF"),
            ]);
            $this->fail('Expected original DB failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('original DB failure', $exception->getMessage());
        } finally {
            KnowledgeItem::flushEventListeners();
        }

        $this->assertDatabaseCount('knowledge_items', 0);
        $this->assertLogged('knowledge-items/attachments/new.pdf');
    }

    private function failingDisk(string $failure): void
    {
        $disk = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('putFileAs')->andReturn('knowledge-items/attachments/new.pdf');
        $delete = $disk->shouldReceive('delete');
        $failure === 'false' ? $delete->andReturn(false) : $delete->andThrow(new RuntimeException('disk unavailable'));
        $factory = Mockery::mock(Factory::class);
        $factory->shouldReceive('disk')->with('local')->andReturn($disk);
        $this->app->instance(Factory::class, $factory);
        $this->app->instance('filesystem', $factory);
        Storage::clearResolvedInstance('filesystem');
    }

    private function assertLogged(string $path): void
    {
        Log::shouldHaveReceived('error')->with('KM file cleanup failed; retry required', Mockery::on(
            fn ($context) => $context['disk'] === 'local' && $context['path'] === $path && $context['exception'] instanceof \Throwable
        ))->once();
    }

    private function owner(string $role): array
    {
        $roleId = DB::table('roles')->insertGetId(['role_name' => $role, 'display_name' => $role]);
        $admin = User::create(['role_id' => $roleId, 'username' => 'admin', 'email' => 'admin@example.com', 'password' => 'password', 'is_active' => true]);
        $category = CompetitionCategory::create(['category_name' => 'KM', 'category_slug' => 'km', 'is_active' => true]);
        return [$admin, $category];
    }

    private function item(User $admin, CompetitionCategory $category): KnowledgeItem
    {
        return KnowledgeItem::create([
            'created_by' => $admin->id, 'category_id' => $category->id,
            'title' => 'Original', 'status' => 'draft',
            'cover_image' => 'knowledge-items/covers/old.png',
            'attachment_path' => 'knowledge-items/attachments/old.pdf',
            'attachment_original_name' => 'old.pdf',
        ]);
    }
}
