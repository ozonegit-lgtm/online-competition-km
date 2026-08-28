<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\CompetitionTemplate;
use App\Models\CompetitionTemplateFormField;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_admin_can_create_competition_with_valid_data(): void
    {
        [$admin, $category, $template] = $this->context('admin');

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.store'), $this->storePayload($category, $template))
            ->assertRedirect(route('competition-admin.competitions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('competitions', [
            'title' => 'New Competition',
            'category_id' => $category->id,
            'template_id' => $template->id,
            'created_by' => $admin->id,
            'status' => 'open',
        ]);
    }

    public function test_category_must_exist_when_creating_competition(): void
    {
        [$admin, $category, $template] = $this->context('admin');

        $response = $this->actingAs($admin)->post(
            route('competition-admin.competitions.store'),
            $this->storePayload($category, $template, ['category_id' => 999999])
        );

        $response->assertSessionHasErrors('category_id');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('competitions', 0);
    }

    public function test_template_must_exist_when_creating_competition(): void
    {
        [$admin, $category, $template] = $this->context('admin');

        $response = $this->actingAs($admin)->post(
            route('competition-admin.competitions.store'),
            $this->storePayload($category, $template, ['template_id' => 999999])
        );

        $response->assertSessionHasErrors('template_id');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('competitions', 0);
    }

    public function test_registration_end_before_registration_start_is_rejected(): void
    {
        [$admin, $category, $template] = $this->context('admin');

        $response = $this->actingAs($admin)->post(
            route('competition-admin.competitions.store'),
            $this->storePayload($category, $template, [
                'registration_start' => '2026-09-02 09:00:00',
                'registration_end' => '2026-09-01 09:00:00',
            ])
        );

        $response->assertSessionHasErrors('registration_end');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('competitions', 0);
    }

    public function test_competition_admin_can_update_own_competition(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $category, ['title' => 'Updated Competition'])
            )
            ->assertRedirect(route('competition-admin.competitions.show', $competition))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'title' => 'Updated Competition',
        ]);
    }

    public function test_competition_admin_cannot_update_another_admins_competition(): void
    {
        [$owner, $category, $template] = $this->context('owner');
        $otherAdmin = $this->user('other-admin');
        $competition = $this->competition($owner, $category, $template);

        $response = $this->actingAs($otherAdmin)->put(
            route('competition-admin.competitions.update', $competition),
            $this->updatePayload($competition, $category, ['title' => 'Unauthorized Change'])
        );

        $response->assertForbidden();
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'title' => 'Existing Competition',
            'created_by' => $owner->id,
        ]);
    }

    public function test_competition_admin_cannot_edit_another_admins_competition(): void
    {
        [$owner, $category, $template] = $this->context('owner');
        $otherAdmin = $this->user('other-admin');
        $competition = $this->competition($owner, $category, $template);

        $response = $this->actingAs($otherAdmin)
            ->get(route('competition-admin.competitions.edit', $competition));

        $response->assertForbidden();
        $this->assertNotSame(500, $response->getStatusCode());
    }

    public function test_creation_copies_active_template_field_metadata(): void
    {
        [$admin, $category, $template] = $this->context('admin', false);
        CompetitionTemplateFormField::create([
            'template_id' => $template->id,
            'label' => 'Project Category',
            'field_name' => 'project_category',
            'field_type' => 'select',
            'placeholder' => 'Select category',
            'help_text' => 'Choose one option',
            'options' => ['Research', 'Innovation'],
            'is_required' => true,
            'sort_order' => 7,
            'is_active' => true,
        ]);
        CompetitionTemplateFormField::create([
            'template_id' => $template->id,
            'label' => 'Inactive Field',
            'field_name' => 'inactive_field',
            'field_type' => 'text',
            'is_required' => false,
            'sort_order' => 8,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.store'), $this->storePayload($category, $template))
            ->assertRedirect(route('competition-admin.competitions.index'));

        $competition = Competition::where('title', 'New Competition')->sole();
        $field = $competition->formFields()->sole();
        $this->assertSame('Project Category', $field->label);
        $this->assertSame('project_category', $field->field_name);
        $this->assertSame('select', $field->field_type);
        $this->assertSame('Select category', $field->placeholder);
        $this->assertSame('Choose one option', $field->help_text);
        $this->assertSame(['Research', 'Innovation'], $field->options);
        $this->assertTrue($field->is_required);
        $this->assertSame(7, $field->sort_order);
        $this->assertTrue($field->is_active);
    }

    public function test_updating_competition_cannot_change_created_by(): void
    {
        [$owner, $category, $template] = $this->context('owner');
        $otherAdmin = $this->user('other-admin');
        $competition = $this->competition($owner, $category, $template);

        $this->actingAs($owner)->put(
            route('competition-admin.competitions.update', $competition),
            $this->updatePayload($competition, $category, [
                'title' => 'Owner Updated Competition',
                'created_by' => $otherAdmin->id,
            ])
        )->assertRedirect(route('competition-admin.competitions.show', $competition));

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'title' => 'Owner Updated Competition',
            'created_by' => $owner->id,
        ]);
    }

    private function context(string $username, bool $withField = true): array
    {
        $admin = $this->user($username);
        $category = CompetitionCategory::create([
            'category_name' => 'Category '.$username,
            'category_slug' => 'category-'.$username,
            'is_active' => true,
        ]);
        $template = CompetitionTemplate::create([
            'template_name' => 'Template '.$username,
            'template_slug' => 'template-'.$username,
            'is_active' => true,
        ]);

        if ($withField) {
            CompetitionTemplateFormField::create([
                'template_id' => $template->id,
                'label' => 'Project Title',
                'field_name' => 'project_title',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 1,
                'is_active' => true,
            ]);
        }

        return [$admin, $category, $template];
    }

    private function user(string $username): User
    {
        $role = Role::firstOrCreate(
            ['role_name' => 'Competition Admin'],
            ['display_name' => 'Competition Admin']
        );

        return User::create([
            'role_id' => $role->id,
            'username' => $username,
            'email' => $username.'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function storePayload(
        CompetitionCategory $category,
        CompetitionTemplate $template,
        array $overrides = []
    ): array {
        return array_replace([
            'title' => 'New Competition',
            'category_id' => $category->id,
            'template_id' => $template->id,
            'description' => 'Competition description',
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => '2026-09-01 09:00:00',
            'registration_end' => '2026-09-02 09:00:00',
            'judging_start' => '2026-09-02 09:00:00',
            'judging_end' => '2026-09-03 09:00:00',
            'result_announcement' => '2026-09-03 09:00:00',
        ], $overrides);
    }

    private function competition(
        User $owner,
        CompetitionCategory $category,
        CompetitionTemplate $template
    ): Competition {
        return Competition::create([
            'category_id' => $category->id,
            'template_id' => $template->id,
            'created_by' => $owner->id,
            'title' => 'Existing Competition',
            'description' => 'Original description',
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => '2026-09-01 09:00:00',
            'registration_end' => '2026-09-02 09:00:00',
            'judging_start' => '2026-09-02 09:00:00',
            'judging_end' => '2026-09-03 09:00:00',
            'result_announcement' => '2026-09-03 09:00:00',
            'status' => 'open',
        ]);
    }

    private function updatePayload(
        Competition $competition,
        CompetitionCategory $category,
        array $overrides = []
    ): array {
        return array_replace([
            'category_id' => $category->id,
            'template_id' => $competition->template_id,
            'title' => $competition->title,
            'description' => $competition->description,
            'competition_type' => $competition->competition_type,
            'visibility' => $competition->visibility,
            'registration_start' => '2026-09-01 09:00:00',
            'registration_end' => '2026-09-02 09:00:00',
            'judging_start' => '2026-09-02 09:00:00',
            'judging_end' => '2026-09-03 09:00:00',
            'result_announcement' => '2026-09-03 09:00:00',
            'status' => 'open',
        ], $overrides);
    }
}
