<?php

namespace Tests\Feature;

use App\Models\CompetitionCategory;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EbookBackendTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_only_active_super_admin_can_access_ebook_backend(): void
    {
        $super = $this->user('super', 'Super Admin');
        $competitionAdmin = $this->user('competition', 'Competition Admin');
        $judge = $this->user('judge', 'Judge');

        $this->actingAs($super)
            ->get(route('superadmin.knowledge-page.books.index'))
            ->assertOk();

        foreach ([$competitionAdmin, $judge] as $user) {
            $this->flushSession();
            auth()->forgetGuards();
            $this->actingAs($user)
                ->get(route('superadmin.knowledge-page.books.index'))
                ->assertForbidden();
        }

        $this->flushSession();
        auth()->forgetGuards();
        $this->get(route('superadmin.knowledge-page.books.index'))
            ->assertRedirect(route('login'));
    }

    public function test_store_enforces_ebook_invariants_and_requires_pdf_or_http_url(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->knowledgeCategory('Policies');
        $base = [
            'title' => 'Safe E-Book',
            'knowledge_category_id' => $category->id,
            'publication_year' => 2569,
            'volume' => '3',
            'issue' => '1',
            'sort_order' => 7,
        ];

        $this->actingAs($super)
            ->from(route('superadmin.knowledge-page.books.create'))
            ->post(route('superadmin.knowledge-page.books.store'), $base)
            ->assertRedirect(route('superadmin.knowledge-page.books.create'))
            ->assertSessionHasErrors('attachment');

        $this->post(route('superadmin.knowledge-page.books.store'), $base + [
            'external_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('external_url');

        $this->post(route('superadmin.knowledge-page.books.store'), $base + [
            'external_url' => 'https://example.org/books/safe',
            'knowledge_type' => 'article',
            'submission_id' => 123,
            'created_by' => 123,
            'status' => 'published',
        ])->assertSessionHasErrors(['knowledge_type', 'submission_id', 'created_by', 'status']);

        $this->post(route('superadmin.knowledge-page.books.store'), $base + [
            'external_url' => 'https://example.org/books/safe',
        ])->assertRedirect();

        $ebook = KnowledgeItem::query()->sole();
        $this->assertSame('ebook', $ebook->knowledge_type);
        $this->assertNull($ebook->submission_id);
        $this->assertNull($ebook->category_id);
        $this->assertSame($super->id, $ebook->created_by);
        $this->assertSame('hidden', $ebook->status);
        $this->assertNull($ebook->published_at);
        $this->assertSame(2569, $ebook->publication_year);
        $this->assertSame(7, $ebook->sort_order);
        $this->assertTrue($ebook->knowledgeCategory->is($category));
    }

    public function test_pdf_upload_publish_hide_and_confirmed_delete_follow_private_file_lifecycle(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->knowledgeCategory('Guides');

        $this->actingAs($super)->post(route('superadmin.knowledge-page.books.store'), [
            'title' => 'PDF Book',
            'knowledge_category_id' => $category->id,
            'attachment' => $this->pdf('guide.pdf'),
        ])->assertRedirect();

        $ebook = KnowledgeItem::query()->ebooks()->sole();
        $this->assertNotNull($ebook->attachment_path);
        Storage::disk('local')->assertExists($ebook->attachment_path);
        $this->assertStringStartsWith('knowledge-items/attachments/', $ebook->attachment_path);

        $this->post(route('superadmin.knowledge-page.books.publish', $ebook))
            ->assertRedirect();
        $this->assertSame('published', $ebook->fresh()->status);
        $this->assertNotNull($ebook->fresh()->published_at);

        $this->delete(route('superadmin.knowledge-page.books.hide', $ebook))
            ->assertRedirect();
        $this->assertSame('hidden', $ebook->fresh()->status);
        $this->assertNull($ebook->fresh()->published_at);

        $this->delete(route('superadmin.knowledge-page.books.destroy', $ebook))
            ->assertSessionHasErrors('confirm_delete');
        $this->assertDatabaseHas('knowledge_items', ['id' => $ebook->id]);

        $path = $ebook->attachment_path;
        $this->delete(route('superadmin.knowledge-page.books.destroy', $ebook), [
            'confirm_delete' => 'yes',
        ])->assertRedirect(route('superadmin.knowledge-page.books.index'));
        $this->assertDatabaseMissing('knowledge_items', ['id' => $ebook->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_update_keeps_existing_pdf_and_replacement_removes_old_file(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->knowledgeCategory('Manuals');
        $ebook = $this->ebook($super, $category, [
            'attachment_path' => 'knowledge-items/attachments/old.pdf',
            'attachment_original_name' => 'old.pdf',
        ]);
        Storage::disk('local')->put($ebook->attachment_path, "%PDF-1.4\nold\n%%EOF");

        $this->actingAs($super)->put(route('superadmin.knowledge-page.books.update', $ebook), [
            'title' => 'Updated without replacement',
            'knowledge_category_id' => $category->id,
        ])->assertRedirect();
        Storage::disk('local')->assertExists('knowledge-items/attachments/old.pdf');

        $this->put(route('superadmin.knowledge-page.books.update', $ebook), [
            'title' => 'Updated with replacement',
            'knowledge_category_id' => $category->id,
            'attachment' => $this->pdf('new.pdf'),
        ])->assertRedirect();

        $ebook->refresh();
        Storage::disk('local')->assertMissing('knowledge-items/attachments/old.pdf');
        Storage::disk('local')->assertExists($ebook->attachment_path);
        $this->assertSame('new.pdf', $ebook->attachment_original_name);
    }

    public function test_database_failure_rolls_back_ebook_and_cleans_new_upload(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->knowledgeCategory('Rollback');
        $this->actingAs($super);
        $this->withoutExceptionHandling();
        DB::unprepared("CREATE TRIGGER fail_ebook_insert BEFORE INSERT ON knowledge_items BEGIN SELECT RAISE(ABORT, 'failure'); END");

        try {
            $this->post(route('superadmin.knowledge-page.books.store'), [
                'title' => 'Must roll back',
                'knowledge_category_id' => $category->id,
                'attachment' => $this->pdf('rollback.pdf'),
            ]);
            $this->fail('Expected database exception.');
        } catch (QueryException) {
            $this->assertDatabaseCount('knowledge_items', 0);
            $this->assertSame([], Storage::disk('local')->allFiles());
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS fail_ebook_insert');
        }
    }

    public function test_ebook_controller_scoping_cannot_mutate_legacy_knowledge_items(): void
    {
        $super = $this->user('super', 'Super Admin');
        $legacy = $this->legacyItem($super, ['title' => 'Legacy Article']);
        $category = $this->knowledgeCategory('Books');

        $this->actingAs($super)
            ->get(route('superadmin.knowledge-page.books.edit', $legacy))
            ->assertNotFound();
        $this->post(route('superadmin.knowledge-page.books.publish', $legacy))
            ->assertNotFound();
        $this->put(route('superadmin.knowledge-page.books.update', $legacy), [
            'title' => 'Must not change',
            'knowledge_category_id' => $category->id,
            'external_url' => 'https://example.org/book',
        ])->assertNotFound();
        $this->delete(route('superadmin.knowledge-page.books.destroy', $legacy), [
            'confirm_delete' => 'yes',
        ])->assertNotFound();

        $this->assertSame('Legacy Article', $legacy->fresh()->title);
    }

    public function test_legacy_home_and_admin_lists_exclude_ebooks_without_losing_null_legacy_types(): void
    {
        $super = $this->user('super', 'Super Admin');
        $competitionAdmin = $this->user('competition', 'Competition Admin');
        $category = $this->knowledgeCategory('Books');
        $legacy = $this->legacyItem($competitionAdmin, [
            'title' => 'Visible Legacy',
            'status' => 'published',
            'knowledge_type' => 'article',
            'published_at' => now(),
        ]);
        $ebook = $this->ebook($super, $category, [
            'title' => 'Separated Ebook',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($legacy->title)
            ->assertDontSee($ebook->title);

        $this->actingAs($super)
            ->get(route('superadmin.km.index'))
            ->assertOk()
            ->assertSee($legacy->title)
            ->assertDontSee($ebook->title);

        $this->flushSession();
        auth()->forgetGuards();
        $this->actingAs($competitionAdmin)
            ->get(route('competition-admin.km.index'))
            ->assertOk()
            ->assertSee($legacy->title)
            ->assertDontSee($ebook->title);
    }

    public function test_competition_admin_cannot_manage_or_privately_read_hidden_ebook_by_direct_url(): void
    {
        $super = $this->user('super', 'Super Admin');
        $competitionAdmin = $this->user('competition', 'Competition Admin');
        $category = $this->knowledgeCategory('Protected');
        $ebook = $this->ebook($super, $category, [
            'status' => 'hidden',
            'attachment_path' => 'knowledge-items/attachments/private.pdf',
            'attachment_original_name' => 'private.pdf',
        ]);
        Storage::disk('local')->put($ebook->attachment_path, "%PDF-1.4\nprivate\n%%EOF");

        $this->actingAs($competitionAdmin)
            ->get(route('competition-admin.km.edit', $ebook))
            ->assertForbidden();
        $this->post(route('competition-admin.km.publish', $ebook))
            ->assertForbidden();
        $this->delete(route('competition-admin.km.destroy', $ebook))
            ->assertForbidden();
        $this->get(route('knowledge-items.attachment', $ebook))
            ->assertNotFound();
        $this->get(route('knowledge-items.attachment.inline', $ebook))
            ->assertNotFound();

        $this->flushSession();
        auth()->forgetGuards();
        $this->actingAs($super)
            ->get(route('knowledge-items.attachment.inline', $ebook))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=private.pdf');
    }

    public function test_public_only_sees_published_ebooks_with_filters_and_sort_order(): void
    {
        $super = $this->user('super', 'Super Admin');
        $categoryA = $this->knowledgeCategory('Category A');
        $categoryB = $this->knowledgeCategory('Category B');
        $first = $this->ebook($super, $categoryA, [
            'title' => 'Alpha Searchable', 'status' => 'published',
            'publication_year' => 2569, 'sort_order' => 1, 'published_at' => now()->subDay(),
        ]);
        $second = $this->ebook($super, $categoryB, [
            'title' => 'Beta Searchable', 'status' => 'published',
            'publication_year' => 2568, 'sort_order' => 2, 'published_at' => now(),
        ]);
        $hidden = $this->ebook($super, $categoryA, [
            'title' => 'Hidden Searchable', 'status' => 'hidden', 'sort_order' => 0,
        ]);
        $legacy = $this->legacyItem($super, ['title' => 'Legacy Searchable', 'status' => 'published']);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSeeInOrder([$first->title, $second->title])
            ->assertDontSee($hidden->title)
            ->assertDontSee($legacy->title);

        $this->get(route('knowledge.index', [
            'q' => 'Searchable', 'category' => $categoryA->id, 'year' => 2569,
        ]))->assertOk()
            ->assertSee($first->title)
            ->assertDontSee($second->title)
            ->assertDontSee($hidden->title);
    }

    public function test_published_pdf_supports_download_and_inline_but_inline_rejects_non_pdf(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->knowledgeCategory('Files');
        $ebook = $this->ebook($super, $category, [
            'status' => 'published',
            'attachment_path' => 'knowledge-items/attachments/public.pdf',
            'attachment_original_name' => 'public.pdf',
        ]);
        Storage::disk('local')->put($ebook->attachment_path, "%PDF-1.4\npublic\n%%EOF");

        $this->get(route('knowledge-items.attachment', $ebook))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=public.pdf');
        $this->get(route('knowledge-items.attachment.inline', $ebook))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=public.pdf')
            ->assertHeader('x-content-type-options', 'nosniff');

        $ebook->update([
            'attachment_path' => 'knowledge-items/attachments/not-pdf.pdf',
        ]);
        Storage::disk('local')->put($ebook->attachment_path, 'plain text');
        $this->get(route('knowledge-items.attachment.inline', $ebook))->assertNotFound();
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

    private function knowledgeCategory(string $name): KnowledgeCategory
    {
        return KnowledgeCategory::create([
            'name' => $name.' '.uniqid(),
            'slug' => 'knowledge-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function ebook(User $owner, KnowledgeCategory $category, array $attributes = []): KnowledgeItem
    {
        return KnowledgeItem::create(array_merge([
            'submission_id' => null,
            'created_by' => $owner->id,
            'category_id' => null,
            'knowledge_category_id' => $category->id,
            'knowledge_type' => 'ebook',
            'title' => 'Ebook '.uniqid(),
            'external_url' => 'https://example.org/book',
            'status' => 'hidden',
            'sort_order' => 0,
        ], $attributes));
    }

    private function legacyItem(User $owner, array $attributes = []): KnowledgeItem
    {
        $category = CompetitionCategory::create([
            'category_name' => 'Competition '.uniqid(),
            'category_slug' => 'competition-'.uniqid(),
            'is_active' => true,
        ]);

        return KnowledgeItem::create(array_merge([
            'submission_id' => null,
            'created_by' => $owner->id,
            'category_id' => $category->id,
            'knowledge_type' => 'article',
            'title' => 'Legacy '.uniqid(),
            'status' => 'draft',
        ], $attributes));
    }

    private function pdf(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'ebook-');
        $this->assertNotFalse($path);
        file_put_contents($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF");
        $this->temporaryFiles[] = $path;

        return new UploadedFile($path, $name, null, null, true);
    }
}
