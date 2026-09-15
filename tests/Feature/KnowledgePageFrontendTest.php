<?php

namespace Tests\Feature;

use App\Models\CompetitionCategory;
use App\Models\ContactMessage;
use App\Models\KnowledgeCategory;
use App\Models\KnowledgeItem;
use App\Models\KnowledgePageNavItem;
use App\Models\KnowledgePageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgePageFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_public_knowledge_page_renders_cms_sections_links_and_only_published_ebooks(): void
    {
        KnowledgePageSetting::create([
            'site_name' => 'NRRU Knowledge Library',
            'hero_title' => 'Hero from CMS',
            'about_title' => 'About from CMS',
            'books_title' => 'Books from CMS',
            'contact_title' => 'Contact from CMS',
            'organization_name' => 'NRRU',
            'footer_organization_name' => 'NRRU Footer',
        ]);
        KnowledgePageNavItem::create([
            'placement' => 'navbar',
            'label' => 'External menu',
            'url' => 'https://example.org',
            'target' => '_blank',
            'sort_order' => 1,
            'is_visible' => true,
        ]);
        $category = $this->knowledgeCategory();
        $published = $this->ebook($category, ['title' => 'Published frontend book', 'status' => 'published']);
        $hidden = $this->ebook($category, ['title' => 'Hidden frontend book', 'status' => 'hidden']);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSee('NRRU Knowledge Library')
            ->assertSee('Hero from CMS')
            ->assertSee('About from CMS')
            ->assertSee('Books from CMS')
            ->assertSee('Contact from CMS')
            ->assertSee($published->title)
            ->assertDontSee($hidden->title)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false)
            ->assertSee('name="q"', false)
            ->assertSee('name="category"', false)
            ->assertSee('name="year"', false);
    }

    public function test_disabled_sections_are_not_rendered_and_contact_fields_follow_settings(): void
    {
        KnowledgePageSetting::create([
            'site_name' => 'Conditional Library',
            'hero_enabled' => false,
            'hero_title' => 'Hidden hero section',
            'about_enabled' => false,
            'about_title' => 'Hidden about section',
            'books_enabled' => false,
            'books_title' => 'Hidden books section',
            'contact_enabled' => true,
            'contact_title' => 'Visible contact section',
            'contact_form_enabled' => true,
            'contact_name_enabled' => true,
            'contact_name_required' => true,
            'contact_phone_enabled' => false,
            'contact_email_enabled' => false,
            'contact_message_enabled' => true,
            'contact_message_required' => false,
        ]);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertDontSee('Hidden hero section')
            ->assertDontSee('Hidden about section')
            ->assertDontSee('Hidden books section')
            ->assertSee('Visible contact section')
            ->assertSee('name="name"', false)
            ->assertSee('name="message"', false)
            ->assertSee('name="website"', false)
            ->assertDontSee('name="phone"', false)
            ->assertDontSee('name="email"', false);
    }

    public function test_section_order_settings_use_four_position_selects(): void
    {
        KnowledgePageSetting::create();
        $super = $this->user('section-order-ui', 'Super Admin');

        $response = $this->actingAs($super)
            ->get(route('superadmin.knowledge-page.settings.edit'))
            ->assertOk()
            ->assertSee('ลำดับการแสดง')
            ->assertDontSee('ลำดับ Section')
            ->assertSee('1 จะแสดงก่อน และ 4 จะแสดงท้ายสุด');

        $html = $response->getContent();

        $this->assertSame(4, preg_match_all(
            '/<select name="(?:hero|about|books|contact)_sort_order"/',
            $html
        ));
        $this->assertSame(16, preg_match_all('/<option value="[1-4]"/', $html));
        $this->assertDoesNotMatchRegularExpression(
            '/<input[^>]+name="(?:hero|about|books|contact)_sort_order"/s',
            $html
        );
    }

    public function test_public_sections_follow_the_selected_order(): void
    {
        KnowledgePageSetting::create([
            'hero_title' => 'Section Hero First',
            'hero_sort_order' => 1,
            'about_title' => 'Section About Third',
            'about_sort_order' => 3,
            'books_title' => 'Section Books Second',
            'books_sort_order' => 2,
            'contact_title' => 'Section Contact Fourth',
            'contact_sort_order' => 4,
        ]);

        $this->get(route('knowledge.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'Section Hero First',
                'Section Books Second',
                'Section About Third',
                'Section Contact Fourth',
            ]);
    }

    public function test_ebook_detail_renders_metadata_pdf_online_actions_and_library_back_link(): void
    {
        $category = $this->knowledgeCategory();
        $ebook = $this->ebook($category, [
            'title' => 'Detailed E-Book',
            'summary' => 'Book summary',
            'content' => 'Book content',
            'status' => 'published',
            'publication_year' => 2569,
            'volume' => '2',
            'issue' => '4',
            'attachment_path' => 'knowledge-items/attachments/detail.pdf',
            'attachment_original_name' => 'detail.pdf',
            'external_url' => 'https://example.org/read',
        ]);
        Storage::disk('local')->put($ebook->attachment_path, "%PDF-1.4\n%%EOF");

        $this->get(route('knowledge.show', $ebook))
            ->assertOk()
            ->assertSee('E-Book KM')
            ->assertSee($category->name)
            ->assertSee('2569')
            ->assertSee('Book summary')
            ->assertSee('Book content')
            ->assertSee('อ่าน PDF')
            ->assertSee('ดาวน์โหลด PDF')
            ->assertSee('อ่านออนไลน์')
            ->assertSee(route('knowledge.index'), false)
            ->assertSee(route('knowledge-items.attachment.inline', $ebook), false);
    }

    public function test_super_admin_cms_views_render_real_forms_and_actions(): void
    {
        $super = $this->user('super', 'Super Admin');
        $category = $this->knowledgeCategory();
        $ebook = $this->ebook($category, ['status' => 'hidden']);
        KnowledgePageNavItem::create([
            'placement' => 'navbar',
            'label' => 'About destination',
            'url' => '#about',
            'target' => '_self',
            'is_visible' => true,
        ]);
        KnowledgePageNavItem::create([
            'placement' => 'footer',
            'label' => 'Custom destination',
            'url' => 'https://example.org/custom-page',
            'target' => '_blank',
            'is_visible' => true,
        ]);
        $message = ContactMessage::create([
            'name' => 'Frontend sender',
            'email' => 'sender@example.org',
            'message' => 'Frontend message',
            'status' => 'unread',
        ]);

        $this->actingAs($super)
            ->get(route('superadmin.knowledge-page.settings.edit'))
            ->assertOk()
            ->assertViewIs('superadmin.knowledge-page.edit')
            ->assertSee('name="site_name"', false)
            ->assertSee('name="footer_copyright"', false);
        $this->get(route('superadmin.knowledge-page.nav-items.index'))
            ->assertOk()
            ->assertSee('name="placement"', false)
            ->assertSee('data-selected-destination="#about"', false)
            ->assertSee('data-selected-destination="custom"', false)
            ->assertSee('value="https://example.org/custom-page"', false)
            ->assertSee('value="/knowledge"', false)
            ->assertSee('value="#books"', false)
            ->assertSee('value="#contact"', false)
            ->assertSee('เปิดหน้าเดิม')
            ->assertSee('เปิดแท็บใหม่');
        $this->get(route('superadmin.knowledge-page.categories.index'))
            ->assertOk()->assertSee($category->name);
        $this->get(route('superadmin.knowledge-page.books.index'))
            ->assertOk()->assertSee($ebook->title)->assertSee('confirm_delete', false);
        $this->get(route('superadmin.knowledge-page.books.create'))
            ->assertOk()->assertSee('name="attachment"', false)->assertSee('name="external_url"', false);
        $this->get(route('superadmin.knowledge-page.books.edit', $ebook))
            ->assertOk()->assertSee('name="publication_year"', false);
        $this->get(route('superadmin.contact-messages.index'))
            ->assertOk()->assertSee('Frontend sender');
        $this->get(route('superadmin.contact-messages.show', $message))
            ->assertOk()->assertSee('Frontend message')->assertSee('value="archived"', false);
    }

    public function test_super_admin_can_preview_a_disabled_section_asset_while_public_cannot(): void
    {
        $path = 'knowledge-page/assets/hero/disabled.png';
        Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));
        KnowledgePageSetting::create([
            'site_name' => 'Asset preview',
            'hero_enabled' => false,
            'hero_image_path' => $path,
        ]);

        $assetUrl = route('knowledge.assets.show', 'hero');
        $this->get($assetUrl)->assertNotFound();

        $super = $this->user('super-preview', 'Super Admin');
        $this->actingAs($super)->get($assetUrl)->assertOk();
    }

    public function test_non_super_admin_roles_cannot_render_cms_views(): void
    {
        foreach (['Competition Admin', 'Judge'] as $roleName) {
            $this->flushSession();
            auth()->forgetGuards();
            $user = $this->user(strtolower(str_replace(' ', '-', $roleName)), $roleName);

            $this->actingAs($user)
                ->get(route('superadmin.knowledge-page.settings.edit'))
                ->assertForbidden();
            $this->get(route('superadmin.knowledge-page.books.index'))
                ->assertForbidden();
            $this->get(route('superadmin.contact-messages.index'))
                ->assertForbidden();
        }
    }

    public function test_home_contains_external_ebook_cta_without_changing_legacy_detail_flow(): void
    {
        $legacy = $this->legacyItem([
            'title' => 'Legacy detail remains',
            'summary' => 'Legacy summary',
            'status' => 'published',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('คลัง E-Book KM ↗')
            ->assertSee('href="'.route('knowledge.index').'"', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false);

        $this->get(route('knowledge.show', $legacy))
            ->assertOk()
            ->assertSee('Legacy detail remains')
            ->assertSee('Legacy summary')
            ->assertSee('Manual KM')
            ->assertSee(route('home'), false);
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

    private function knowledgeCategory(): KnowledgeCategory
    {
        return KnowledgeCategory::create([
            'name' => 'Frontend category '.uniqid(),
            'slug' => 'frontend-'.uniqid(),
            'is_active' => true,
        ]);
    }

    private function ebook(KnowledgeCategory $category, array $attributes = []): KnowledgeItem
    {
        return KnowledgeItem::create(array_merge([
            'knowledge_type' => 'ebook',
            'knowledge_category_id' => $category->id,
            'title' => 'Frontend E-Book '.uniqid(),
            'external_url' => 'https://example.org/book',
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }

    private function legacyItem(array $attributes = []): KnowledgeItem
    {
        $category = CompetitionCategory::create([
            'category_name' => 'Legacy category '.uniqid(),
            'category_slug' => 'legacy-'.uniqid(),
            'is_active' => true,
        ]);

        return KnowledgeItem::create(array_merge([
            'category_id' => $category->id,
            'knowledge_type' => 'article',
            'title' => 'Legacy '.uniqid(),
            'status' => 'published',
            'published_at' => now(),
        ], $attributes));
    }
}
