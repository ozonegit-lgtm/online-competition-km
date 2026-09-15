<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeItem;
use App\Models\KnowledgePageNavItem;
use App\Models\KnowledgePageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgePageCmsBackendTest extends TestCase
{
    use RefreshDatabase;

    private array $temporaryFiles = [];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            @unlink($path);
        }

        parent::tearDown();
    }

    public function test_schema_and_model_relationships_support_ebook_cms_metadata(): void
    {
        $this->assertTrue(Schema::hasColumns('knowledge_items', [
            'knowledge_type', 'knowledge_category_id', 'external_url', 'archived_at',
            'sort_order', 'publication_year', 'volume', 'issue',
        ]));
        $this->assertTrue(Schema::hasTable('knowledge_page_settings'));
        $this->assertTrue(Schema::hasTable('knowledge_page_nav_items'));
        $this->assertTrue(Schema::hasTable('contact_messages'));

        $category = KnowledgeCategory::create([
            'name' => 'Schema category',
            'slug' => 'schema-category',
            'is_active' => true,
        ]);
        $ebook = KnowledgeItem::create([
            'knowledge_type' => 'ebook',
            'knowledge_category_id' => $category->id,
            'title' => 'Schema E-Book',
            'external_url' => 'https://example.org/schema-book',
            'status' => 'hidden',
            'sort_order' => 4,
            'publication_year' => 2569,
            'volume' => '2',
            'issue' => '4',
        ]);

        $this->assertTrue($ebook->knowledgeCategory->is($category));
        $this->assertTrue($category->knowledgeItems->contains($ebook));
        $this->assertSame(4, $ebook->sort_order);
        $this->assertSame(2569, $ebook->publication_year);
    }

    public function test_settings_are_fail_safe_without_a_row_and_only_super_admin_can_manage_cms(): void
    {
        $settings = KnowledgePageSetting::current();
        $this->assertFalse($settings->exists);
        $this->assertSame('E-Book KM', $settings->site_name);
        $this->assertTrue($settings->books_enabled);

        $super = $this->user('super', 'Super Admin');
        $admin = $this->user('competition', 'Competition Admin');
        $judge = $this->user('judge', 'Judge');
        $routes = [
            'superadmin.knowledge-page.settings.edit',
            'superadmin.knowledge-page.nav-items.index',
            'superadmin.knowledge-page.categories.index',
            'superadmin.contact-messages.index',
        ];

        foreach ($routes as $routeName) {
            $this->actingAs($super)->get(route($routeName))->assertOk();
            foreach ([$admin, $judge] as $user) {
                $this->flushSession();
                auth()->forgetGuards();
                $this->actingAs($user)->get(route($routeName))->assertForbidden();
            }
            $this->flushSession();
            auth()->forgetGuards();
        }
    }

    public function test_settings_validate_safe_urls_and_replace_private_assets_without_orphans(): void
    {
        $super = $this->user('super', 'Super Admin');

        $this->actingAs($super)->put(route('superadmin.knowledge-page.settings.update'), [
            'site_name' => 'Unsafe',
            'hero_button_url' => 'javascript:alert(1)',
            'map_embed_url' => '<iframe src="https://www.google.com/maps/embed"></iframe>',
        ])->assertSessionHasErrors(['hero_button_url', 'map_embed_url']);
        $this->assertDatabaseCount('knowledge_page_settings', 0);

        $this->put(route('superadmin.knowledge-page.settings.update'), [
            'site_name' => 'KM Library',
            'hero_button_url' => '#books',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=safe',
            'site_logo' => $this->png('first.png'),
        ])->assertRedirect();

        $settings = KnowledgePageSetting::query()->sole();
        $firstPath = $settings->site_logo_path;
        $this->assertStringStartsWith('knowledge-page/assets/logo/', $firstPath);
        Storage::disk('local')->assertExists($firstPath);

        $this->put(route('superadmin.knowledge-page.settings.update'), [
            'site_name' => 'KM Library',
            'site_logo' => $this->png('replacement.png'),
        ])->assertRedirect();

        $settings->refresh();
        Storage::disk('local')->assertMissing($firstPath);
        Storage::disk('local')->assertExists($settings->site_logo_path);

        $assetResponse = $this->get(route('knowledge.assets.show', 'logo'))
            ->assertOk()
            ->assertHeader('x-content-type-options', 'nosniff');
        $this->assertStringContainsString('private', $assetResponse->headers->get('cache-control'));
        $this->assertStringContainsString('no-store', $assetResponse->headers->get('cache-control'));
        $this->get('/knowledge/assets/not-allowed')->assertNotFound();
    }

    public function test_section_sort_orders_accept_unique_positions_and_reject_duplicates_or_legacy_values(): void
    {
        $super = $this->user('section-order', 'Super Admin');
        $route = route('superadmin.knowledge-page.settings.update');

        $this->actingAs($super)->put($route, [
            'hero_sort_order' => 1,
            'about_sort_order' => 2,
            'books_sort_order' => 3,
            'contact_sort_order' => 4,
        ])->assertRedirect();

        $this->assertDatabaseHas('knowledge_page_settings', [
            'hero_sort_order' => 1,
            'about_sort_order' => 2,
            'books_sort_order' => 3,
            'contact_sort_order' => 4,
        ]);

        $this->put($route, [
            'hero_sort_order' => 1,
            'about_sort_order' => 3,
            'books_sort_order' => 2,
            'contact_sort_order' => 4,
        ])->assertRedirect();

        $this->assertDatabaseHas('knowledge_page_settings', [
            'hero_sort_order' => 1,
            'about_sort_order' => 3,
            'books_sort_order' => 2,
            'contact_sort_order' => 4,
        ]);

        $this->put($route, [
            'hero_sort_order' => 1,
            'about_sort_order' => 1,
            'books_sort_order' => 2,
            'contact_sort_order' => 3,
        ])->assertSessionHasErrors(['hero_sort_order', 'about_sort_order']);

        $this->put($route, [
            'hero_sort_order' => 3,
        ])->assertSessionHasErrors([
            'about_sort_order',
            'books_sort_order',
            'contact_sort_order',
        ]);

        $this->assertDatabaseHas('knowledge_page_settings', [
            'hero_sort_order' => 1,
            'about_sort_order' => 3,
            'books_sort_order' => 2,
            'contact_sort_order' => 4,
        ]);

        $this->put($route, [
            'hero_sort_order' => 10,
            'about_sort_order' => 20,
            'books_sort_order' => 30,
            'contact_sort_order' => 40,
        ])->assertSessionHasErrors([
            'hero_sort_order',
            'about_sort_order',
            'books_sort_order',
            'contact_sort_order',
        ]);
    }

    public function test_legacy_section_sort_orders_can_be_normalized_safely(): void
    {
        $settings = KnowledgePageSetting::create([
            'site_name' => 'Legacy section order',
            'hero_sort_order' => 10,
            'about_sort_order' => 20,
            'books_sort_order' => 30,
            'contact_sort_order' => 40,
        ]);
        $super = $this->user('section-normalize', 'Super Admin');

        $this->actingAs($super)->put(route('superadmin.knowledge-page.settings.update'), [
            'hero_sort_order' => 1,
            'about_sort_order' => 2,
            'books_sort_order' => 3,
            'contact_sort_order' => 4,
        ])->assertRedirect();

        $this->assertSame([1, 2, 3, 4], [
            $settings->fresh()->hero_sort_order,
            $settings->fresh()->about_sort_order,
            $settings->fresh()->books_sort_order,
            $settings->fresh()->contact_sort_order,
        ]);
    }

    public function test_hidden_section_asset_and_unmanaged_paths_are_not_public(): void
    {
        $heroPath = 'knowledge-page/assets/hero/hero.png';
        Storage::disk('local')->put($heroPath, $this->pngContents());
        $settings = KnowledgePageSetting::create([
            'site_name' => 'KM',
            'hero_enabled' => false,
            'hero_image_path' => $heroPath,
        ]);

        $this->get(route('knowledge.assets.show', 'hero'))->assertNotFound();

        $settings->update([
            'hero_enabled' => true,
            'hero_image_path' => '../hero.png',
        ]);
        $this->get(route('knowledge.assets.show', 'hero'))->assertNotFound();
    }

    public function test_nav_items_validate_allowlists_and_support_visibility_and_reorder(): void
    {
        $super = $this->user('super', 'Super Admin');
        $route = route('superadmin.knowledge-page.nav-items.store');

        $this->actingAs($super)->post($route, [
            'placement' => 'sidebar',
            'label' => 'Unsafe',
            'url' => 'data:text/html,bad',
            'target' => '_parent',
            'icon_key' => 'raw-html',
        ])->assertSessionHasErrors(['placement', 'url', 'target', 'icon_key']);

        $this->post($route, [
            'placement' => 'navbar',
            'label' => 'Books',
            'url' => '#books',
            'target' => '_self',
            'icon_key' => 'book',
            'sort_order' => 10,
            'is_visible' => true,
        ])->assertRedirect();
        $this->post($route, [
            'placement' => 'footer',
            'label' => 'Contact',
            'url' => '/knowledge#contact',
            'target' => '_blank',
            'sort_order' => 20,
            'is_visible' => true,
        ])->assertRedirect();

        [$first, $second] = KnowledgePageNavItem::query()->orderBy('id')->get();
        $this->patch(route('superadmin.knowledge-page.nav-items.visibility', $first), [
            'is_visible' => false,
        ])->assertRedirect();
        $this->put(route('superadmin.knowledge-page.nav-items.reorder'), [
            'items' => [
                ['id' => $first->id, 'sort_order' => 30],
                ['id' => $second->id, 'sort_order' => 1],
            ],
        ])->assertRedirect();

        $this->assertFalse($first->fresh()->is_visible);
        $this->assertSame(30, $first->fresh()->sort_order);
        $this->assertSame(1, $second->fresh()->sort_order);

        $this->put(route('superadmin.knowledge-page.nav-items.update', $first), [
            'placement' => 'footer',
            'label' => 'Updated Contact',
            'url' => '#contact',
            'target' => '_blank',
            'icon_key' => 'contact',
            'sort_order' => 7,
            'is_visible' => false,
        ])->assertRedirect();
        $this->assertDatabaseHas('knowledge_page_nav_items', [
            'id' => $first->id,
            'label' => 'Updated Contact',
            'url' => '#contact',
            'target' => '_blank',
            'sort_order' => 7,
            'is_visible' => false,
        ]);

        $this->delete(route('superadmin.knowledge-page.nav-items.destroy', $second))
            ->assertRedirect();
        $this->assertDatabaseMissing('knowledge_page_nav_items', ['id' => $second->id]);
    }

    public function test_knowledge_categories_are_independent_and_referenced_category_cannot_be_deleted(): void
    {
        $super = $this->user('super', 'Super Admin');
        $this->actingAs($super)->post(route('superadmin.knowledge-page.categories.store'), [
            'name' => 'Research Articles',
            'is_active' => true,
        ])->assertRedirect();

        $category = KnowledgeCategory::query()->sole();
        $this->assertSame('research-articles', $category->slug);
        $ebook = KnowledgeItem::create([
            'created_by' => $super->id,
            'knowledge_type' => 'ebook',
            'knowledge_category_id' => $category->id,
            'title' => 'Referenced',
            'external_url' => 'https://example.org/book',
            'status' => 'hidden',
        ]);

        $this->delete(route('superadmin.knowledge-page.categories.destroy', $category))
            ->assertSessionHasErrors('category');
        $this->assertDatabaseHas('knowledge_categories', ['id' => $category->id]);

        $ebook->delete();
        $this->delete(route('superadmin.knowledge-page.categories.destroy', $category))
            ->assertRedirect();
        $this->assertDatabaseMissing('knowledge_categories', ['id' => $category->id]);
    }

    public function test_contact_form_rules_follow_enabled_and_required_settings_and_honeypot(): void
    {
        KnowledgePageSetting::create([
            'site_name' => 'KM',
            'contact_enabled' => true,
            'contact_form_enabled' => true,
            'contact_name_enabled' => false,
            'contact_phone_enabled' => true,
            'contact_phone_required' => false,
            'contact_email_enabled' => true,
            'contact_email_required' => true,
            'contact_message_enabled' => true,
            'contact_message_required' => true,
        ]);

        $route = route('knowledge.contact.store');
        $this->postJson($route, [
            'name' => 'Prohibited Name',
            'email' => 'not-an-email',
            'message' => '',
            'website' => 'spam.example',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message', 'website']);

        $this->postJson($route, [
            'phone' => '044-000-000',
            'email' => 'reader@example.org',
            'message' => '<script>alert(1)</script>Hello',
            'website' => '',
        ])->assertCreated();

        $this->assertDatabaseHas('contact_messages', [
            'name' => null,
            'phone' => '044-000-000',
            'email' => 'reader@example.org',
            'message' => '<script>alert(1)</script>Hello',
            'status' => 'unread',
        ]);
    }

    public function test_disabled_contact_form_fails_closed(): void
    {
        KnowledgePageSetting::create([
            'site_name' => 'KM',
            'contact_enabled' => true,
            'contact_form_enabled' => false,
        ]);

        $this->postJson(route('knowledge.contact.store'), [
            'message' => 'Should not be stored',
        ])->assertForbidden();
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_endpoint_is_rate_limited_per_session(): void
    {
        config([
            'knowledge_page.contact_rate_limits.session_per_minute' => 1,
            'knowledge_page.contact_rate_limits.ip_per_hour' => 1,
        ]);
        KnowledgePageSetting::create([
            'site_name' => 'KM',
            'contact_enabled' => true,
            'contact_form_enabled' => true,
            'contact_name_enabled' => false,
            'contact_phone_enabled' => false,
            'contact_email_enabled' => false,
            'contact_message_enabled' => true,
            'contact_message_required' => true,
        ]);

        $this->postJson(route('knowledge.contact.store'), [
            'message' => 'Message 1',
        ])->assertCreated();

        $this->postJson(route('knowledge.contact.store'), [
            'message' => 'Message 2',
        ])->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_super_admin_can_filter_read_and_archive_messages_and_output_is_escaped(): void
    {
        $super = $this->user('super', 'Super Admin');
        $message = ContactMessage::create([
            'name' => '<img src=x onerror=alert(1)>',
            'email' => 'sender@example.org',
            'message' => '<script>alert(1)</script>',
            'status' => 'unread',
        ]);
        ContactMessage::create(['message' => 'Already archived', 'status' => 'archived']);

        $this->actingAs($super)
            ->get(route('superadmin.contact-messages.index', ['status' => 'unread']))
            ->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('Already archived');

        $this->get(route('superadmin.contact-messages.show', $message))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame('read', $message->fresh()->status);
        $this->assertNotNull($message->fresh()->read_at);

        $this->patch(route('superadmin.contact-messages.status', $message), [
            'status' => 'archived',
        ])->assertRedirect();
        $this->assertSame('archived', $message->fresh()->status);
        $this->assertNull($message->fresh()->read_at);
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

    private function png(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'page-cms-');
        $this->assertNotFalse($path);
        file_put_contents($path, $this->pngContents());
        $this->temporaryFiles[] = $path;

        return new UploadedFile($path, $name, null, null, true);
    }

    private function pngContents(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    }
}
