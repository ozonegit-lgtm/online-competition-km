<?php

namespace Tests\Feature;

use App\Models\CompetitionCategory;
use App\Models\CompetitionTemplate;
use App\Models\CompetitionTemplateFormField;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateFilePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_field_uses_policy_defaults_and_non_file_field_stores_no_policy(): void
    {
        [$admin, $template] = $this->superAdminContext();

        $this->actingAs($admin)->post(
            route('superadmin.templates.form-fields.store', $template),
            ['fields' => json_encode([
                $this->field(['label' => 'Attachment', 'type' => 'file']),
                $this->field([
                    'label' => 'Title',
                    'type' => 'text',
                    'accepted_file_types' => 'php',
                    'max_file_size' => 99,
                ]),
            ])]
        )->assertRedirect(route('superadmin.templates.index'));

        $this->assertDatabaseHas('competition_template_form_fields', [
            'template_id' => $template->id,
            'label' => 'Attachment',
            'accepted_file_types' => 'jpg,jpeg,png,webp,pdf,doc,docx,ppt,pptx,zip',
            'max_file_size' => 10,
        ]);
        $this->assertDatabaseHas('competition_template_form_fields', [
            'template_id' => $template->id,
            'label' => 'Title',
            'accepted_file_types' => null,
            'max_file_size' => null,
        ]);
    }

    public function test_extensions_are_normalized_and_duplicates_are_removed(): void
    {
        [$admin, $template] = $this->superAdminContext();

        $this->actingAs($admin)->post(
            route('superadmin.templates.form-fields.store', $template),
            ['fields' => json_encode([
                $this->field([
                    'type' => 'file',
                    'accepted_file_types' => '.PDF, jpg,PDF,.JPG',
                    'max_file_size' => 7,
                ]),
            ])]
        )->assertSessionHasNoErrors();

        $this->assertDatabaseHas('competition_template_form_fields', [
            'template_id' => $template->id,
            'accepted_file_types' => 'pdf,jpg',
            'max_file_size' => 7,
        ]);
    }

    public function test_unsupported_extension_is_rejected(): void
    {
        [$admin, $template] = $this->superAdminContext();

        $this->actingAs($admin)->from(route('superadmin.templates.form-fields.create', $template))
            ->post(route('superadmin.templates.form-fields.store', $template), [
                'fields' => json_encode([
                    $this->field(['type' => 'file', 'accepted_file_types' => 'pdf,php']),
                ]),
            ])
            ->assertSessionHasErrors('fields.0.accepted_file_types');

        $this->assertDatabaseCount('competition_template_form_fields', 0);
    }

    public function test_file_size_outside_one_to_ten_megabytes_is_rejected(): void
    {
        [$admin, $template] = $this->superAdminContext();

        foreach ([0, 11] as $size) {
            $this->actingAs($admin)->post(
                route('superadmin.templates.form-fields.store', $template),
                ['fields' => json_encode([
                    $this->field(['type' => 'file', 'max_file_size' => $size]),
                ])]
            )->assertSessionHasErrors('fields.0.max_file_size');
        }

        $this->assertDatabaseCount('competition_template_form_fields', 0);
    }

    public function test_updating_file_field_persists_normalized_policy(): void
    {
        [$admin, $template] = $this->superAdminContext();
        CompetitionTemplateFormField::create([
            'template_id' => $template->id,
            'label' => 'Legacy upload',
            'field_name' => 'legacy_upload',
            'field_type' => 'file',
            'accepted_file_types' => null,
            'max_file_size' => null,
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->get(route('superadmin.templates.form-fields.edit', $template))
            ->assertOk();

        $this->actingAs($admin)->put(
            route('superadmin.templates.form-fields.update', $template),
            ['fields' => json_encode([
                $this->field([
                    'label' => 'Legacy upload',
                    'type' => 'file',
                    'accepted_file_types' => '.DOCX,.PNG',
                    'max_file_size' => 9,
                ]),
            ])]
        )->assertSessionHasNoErrors();

        $this->assertDatabaseHas('competition_template_form_fields', [
            'template_id' => $template->id,
            'accepted_file_types' => 'docx,png',
            'max_file_size' => 9,
        ]);
    }

    public function test_competition_creation_copies_template_file_policy(): void
    {
        [, $template] = $this->superAdminContext();
        CompetitionTemplateFormField::create([
            'template_id' => $template->id,
            'label' => 'Portfolio',
            'field_name' => 'portfolio',
            'field_type' => 'file',
            'accepted_file_types' => 'pdf,zip',
            'max_file_size' => 8,
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $role = Role::create(['role_name' => 'Competition Admin', 'display_name' => 'Competition Admin']);
        $admin = $this->user($role, 'competition-admin');
        $category = CompetitionCategory::create([
            'category_name' => 'General',
            'category_slug' => 'general',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('competition-admin.competitions.store'), [
            'title' => 'Policy copy test',
            'category_id' => $category->id,
            'template_id' => $template->id,
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->addDay()->format('Y-m-d H:i:s'),
            'registration_end' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'judging_start' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'judging_end' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'result_announcement' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ])->assertRedirect(route('competition-admin.competitions.index'));

        $this->assertDatabaseHas('competition_form_fields', [
            'label' => 'Portfolio',
            'accepted_file_types' => 'pdf,zip',
            'max_file_size' => 8,
        ]);
    }

    private function superAdminContext(): array
    {
        $role = Role::firstOrCreate(
            ['role_name' => 'Super Admin'],
            ['display_name' => 'Super Admin']
        );

        $template = CompetitionTemplate::create([
            'template_name' => 'Template ' . uniqid(),
            'template_slug' => 'template-' . uniqid(),
            'is_active' => true,
        ]);

        return [$this->user($role, 'super-admin-' . uniqid()), $template];
    }

    private function user(Role $role, string $username): User
    {
        return User::create([
            'role_id' => $role->id,
            'username' => $username,
            'email' => $username . '@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function field(array $overrides = []): array
    {
        return array_merge([
            'label' => 'Upload',
            'type' => 'file',
            'placeholder' => null,
            'help' => null,
            'options' => [],
            'accepted_file_types' => null,
            'max_file_size' => null,
            'required' => false,
            'active' => true,
        ], $overrides);
    }
}
