<?php

namespace Tests\Feature;

use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class KnowledgeItemStorageWriteFailureTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_competition_admin_create_rejects_false_storage_path(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $this->mockDisk([false]);

        $response = $this->actingAs($admin)->post(
            route('competition-admin.km.store'),
            $this->data($category, ['cover_image' => $this->png('cover.png')])
        );

        $response->assertSessionHasErrors('cover_image');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('knowledge_items', 0);
    }

    public function test_super_admin_create_rejects_empty_storage_path(): void
    {
        $admin = $this->user('super', 'Super Admin');
        $category = $this->category();
        $this->mockDisk(['']);

        $response = $this->actingAs($admin)->post(
            route('superadmin.km.store'),
            $this->data($category, ['attachment' => $this->pdf('guide.pdf')])
        );

        $response->assertSessionHasErrors('attachment');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('knowledge_items', 0);
    }

    public function test_storage_exception_becomes_field_validation_error(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $this->mockDisk([new RuntimeException('sensitive storage failure')]);

        $response = $this->actingAs($admin)->post(
            route('competition-admin.km.store'),
            $this->data($category, ['attachment' => $this->pdf('guide.pdf')])
        );

        $response->assertSessionHasErrors([
            'attachment' => 'ไม่สามารถบันทึกไฟล์แนบได้ กรุณาลองใหม่อีกครั้ง',
        ]);
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('knowledge_items', 0);
    }

    public function test_competition_admin_update_failure_preserves_record_and_old_file(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $item = $this->item($admin, $category);
        $disk = $this->mockDisk([false]);
        $disk->shouldNotReceive('delete');

        $response = $this->actingAs($admin)->put(
            route('competition-admin.km.update', $item),
            $this->data($category, ['title' => 'Changed', 'attachment' => $this->pdf('new.pdf')])
        );

        $response->assertSessionHasErrors('attachment');
        $response->assertSessionMissing('success');
        $item->refresh();
        $this->assertSame('Original', $item->title);
        $this->assertSame('knowledge-items/attachments/old.pdf', $item->attachment_path);
        $this->assertSame('old.pdf', $item->attachment_original_name);
    }

    public function test_super_admin_update_failure_preserves_record_and_old_file(): void
    {
        $super = $this->user('super', 'Super Admin');
        $owner = $this->user('owner', 'Competition Admin');
        $category = $this->category();
        $item = $this->item($owner, $category);
        $disk = $this->mockDisk([false]);
        $disk->shouldNotReceive('delete');

        $response = $this->actingAs($super)->put(
            route('superadmin.km.update', $item),
            $this->data($category, ['title' => 'Changed', 'cover_image' => $this->png('new.png')])
        );

        $response->assertSessionHasErrors('cover_image');
        $response->assertSessionMissing('success');
        $item->refresh();
        $this->assertSame('Original', $item->title);
        $this->assertSame('knowledge-items/covers/old.png', $item->cover_image);
        $this->assertSame($owner->id, $item->created_by);
        $this->assertSame('draft', $item->status);
        $this->assertFalse($item->is_featured);
    }

    public function test_partial_upload_failure_cleans_new_cover_and_creates_no_record(): void
    {
        $admin = $this->user('admin', 'Competition Admin');
        $category = $this->category();
        $newCover = 'knowledge-items/covers/new-cover.png';
        $disk = $this->mockDisk([$newCover, false]);
        $disk->shouldReceive('delete')->once()->with($newCover)->andReturn(true);

        $response = $this->actingAs($admin)->post(
            route('competition-admin.km.store'),
            $this->data($category, [
                'cover_image' => $this->png('cover.png'),
                'attachment' => $this->pdf('guide.pdf'),
            ])
        );

        $response->assertSessionHasErrors('attachment');
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('knowledge_items', 0);
    }

    public function test_partial_update_failure_cleans_new_cover_and_preserves_old_values(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->category();
        $item = $this->item($super, $category);
        $newCover = 'knowledge-items/covers/new-cover.png';
        $disk = $this->mockDisk([$newCover, new RuntimeException('failure')]);
        $disk->shouldReceive('delete')->once()->with($newCover)->andReturn(true);

        $response = $this->actingAs($super)->put(
            route('superadmin.km.update', $item),
            $this->data($category, [
                'title' => 'Changed',
                'cover_image' => $this->png('cover.png'),
                'attachment' => $this->pdf('guide.pdf'),
            ])
        );

        $response->assertSessionHasErrors('attachment');
        $response->assertSessionMissing('success');
        $item->refresh();
        $this->assertSame('Original', $item->title);
        $this->assertSame('knowledge-items/covers/old.png', $item->cover_image);
        $this->assertSame('knowledge-items/attachments/old.pdf', $item->attachment_path);
    }

    private function mockDisk(array $results): Filesystem
    {
        $disk = Mockery::mock(Filesystem::class);

        foreach ($results as $result) {
            $expectation = $disk->shouldReceive('putFileAs')
                ->once()
                ->ordered();

            if ($result instanceof \Throwable) {
                $expectation->andThrow($result);
            } else {
                $expectation->andReturn($result);
            }
        }

        $factory = Mockery::mock(FilesystemFactory::class);
        $factory->shouldReceive('disk')->with('public')->andReturn($disk);
        $this->app->instance(FilesystemFactory::class, $factory);
        $this->app->instance('filesystem', $factory);
        Storage::clearResolvedInstance('filesystem');

        return $disk;
    }

    private function data(CompetitionCategory $category, array $overrides): array
    {
        return array_merge([
            'title' => 'Knowledge',
            'category_id' => $category->id,
        ], $overrides);
    }

    private function item(User $owner, CompetitionCategory $category): KnowledgeItem
    {
        return KnowledgeItem::create([
            'created_by' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Original',
            'status' => 'draft',
            'is_featured' => false,
            'cover_image' => 'knowledge-items/covers/old.png',
            'attachment_path' => 'knowledge-items/attachments/old.pdf',
            'attachment_original_name' => 'old.pdf',
        ]);
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

    private function category(): CompetitionCategory
    {
        return CompetitionCategory::create([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
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
        $path = tempnam(sys_get_temp_dir(), 'km-storage-failure-');
        $this->assertNotFalse($path);
        file_put_contents($path, $contents);
        $this->temporaryFiles[] = $path;

        return new UploadedFile($path, $name, null, null, true);
    }
}
