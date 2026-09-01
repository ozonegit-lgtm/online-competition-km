<?php

namespace Tests\Feature;

use App\Models\CompetitionCategory;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeItemFileAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_anonymous_can_open_published_cover_inline_and_attachment_by_original_name(): void
    {
        $item = $this->item(null, 'published');
        Storage::disk('local')->put($item->cover_image, 'cover');
        Storage::disk('local')->put($item->attachment_path, 'attachment');

        $this->get(route('knowledge-items.cover', $item))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=cover.png');
        $attachment = $this->get(route('knowledge-items.attachment', $item));
        $attachment->assertOk();
        $this->assertStringContainsString(
            strtolower("filename*=utf-8''".rawurlencode('คู่มือ.pdf')),
            strtolower($attachment->headers->get('content-disposition'))
        );
    }

    public function test_anonymous_cannot_open_draft_or_hidden_files(): void
    {
        foreach (['draft', 'hidden'] as $status) {
            $item = $this->item(null, $status);
            Storage::disk('local')->put($item->cover_image, 'cover');
            $this->get(route('knowledge-items.cover', $item))->assertNotFound();
        }
    }

    public function test_owner_and_super_admin_can_open_private_status_but_others_cannot(): void
    {
        $owner = $this->user('owner', 'Competition Admin');
        $other = $this->user('other', 'Competition Admin');
        $super = $this->user('super', 'Super Admin');
        $judge = $this->user('judge', 'Judge');

        foreach (['draft', 'hidden'] as $status) {
            $item = $this->item($owner, $status);
            Storage::disk('local')->put($item->cover_image, 'cover');
            $route = route('knowledge-items.cover', $item);
            $this->actingAs($owner)->get($route)->assertOk();
            $this->actingAs($super)->get($route)->assertOk();
            $this->actingAs($other)->get($route)->assertNotFound();
            $this->actingAs($judge)->get($route)->assertNotFound();
        }
    }

    public function test_unpublish_revokes_anonymous_access_immediately(): void
    {
        $owner = $this->user('owner', 'Competition Admin');
        $item = $this->item($owner, 'published');
        Storage::disk('local')->put($item->cover_image, 'cover');
        $route = route('knowledge-items.cover', $item);
        $this->get($route)->assertOk();
        $this->actingAs($owner)->delete(route('competition-admin.km.unpublish', $item))->assertRedirect();
        auth()->logout();
        $this->get($route)->assertNotFound();
    }

    public function test_invalid_outside_traversal_absolute_null_and_missing_paths_return_not_found(): void
    {
        foreach ([
            '../secret.png',
            'submissions/source.png',
            '/knowledge-items/covers/absolute.png',
            'C:\\knowledge-items\\covers\\absolute.png',
            "knowledge-items/covers/bad\0.png",
            'knowledge-items/covers/missing.png',
        ] as $path) {
            $item = $this->item(null, 'published', ['cover_image' => $path]);
            $this->get(route('knowledge-items.cover', $item))->assertNotFound();
        }
    }

    private function item(?User $owner, string $status, array $attributes = []): KnowledgeItem
    {
        $category = CompetitionCategory::create([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
        ]);

        return KnowledgeItem::create(array_merge([
            'created_by' => $owner?->id,
            'category_id' => $category->id,
            'title' => 'Knowledge '.uniqid(),
            'status' => $status,
            'cover_image' => 'knowledge-items/covers/cover.png',
            'attachment_path' => 'knowledge-items/attachments/guide.pdf',
            'attachment_original_name' => 'คู่มือ.pdf',
        ], $attributes));
    }

    private function user(string $prefix, string $roleName): User
    {
        $roleId = DB::table('roles')->where('role_name', $roleName)->value('id')
            ?? DB::table('roles')->insertGetId(['role_name' => $roleName, 'display_name' => $roleName, 'created_at' => now(), 'updated_at' => now()]);
        return User::create(['role_id' => $roleId, 'username' => $prefix.'-'.uniqid(), 'email' => uniqid().'@example.com', 'password' => 'password', 'is_active' => true]);
    }
}
