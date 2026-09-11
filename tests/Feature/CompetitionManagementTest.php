<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\CompetitionTemplate;
use App\Models\CompetitionTemplateFormField;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    public function test_creation_copies_template_cover_to_competition_owned_file(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $templateCover = 'competition-templates/template-cover.jpg';
        Storage::disk('public')->put($templateCover, 'template cover');
        $template->update(['cover_image' => $templateCover]);

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.store'), $this->storePayload($category, $template))
            ->assertRedirect(route('competition-admin.competitions.index'));

        $competition = Competition::where('title', 'New Competition')->sole();
        $this->assertNotSame($templateCover, $competition->cover_image);
        $this->assertStringStartsWith('competitions/', $competition->cover_image);
        Storage::disk('public')->assertExists($templateCover);
        Storage::disk('public')->assertExists($competition->cover_image);
    }

    public function test_missing_template_cover_does_not_create_competition_or_broken_cover(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $template->update(['cover_image' => 'competition-templates/missing-cover.jpg']);

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.store'), $this->storePayload($category, $template))
            ->assertSessionHasErrors('template_id');

        $this->assertDatabaseCount('competitions', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('competitions'));
    }

    public function test_used_template_cannot_be_deleted_and_competition_cover_remains(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $superAdmin = $this->user('super-admin', 'Super Admin');
        $templateCover = 'competition-templates/used-template.jpg';
        $competitionCover = 'competitions/owned-cover.jpg';
        Storage::disk('public')->put($templateCover, 'template cover');
        Storage::disk('public')->put($competitionCover, 'competition cover');
        $template->update(['cover_image' => $templateCover]);
        $competition = $this->competition($admin, $category, $template);
        $competition->update(['cover_image' => $competitionCover]);

        $this->actingAs($superAdmin)
            ->delete(route('superadmin.templates.destroy', $template))
            ->assertRedirect(route('superadmin.templates.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('competition_templates', ['id' => $template->id]);
        $this->assertDatabaseHas('competitions', ['id' => $competition->id]);
        Storage::disk('public')->assertExists($templateCover);
        Storage::disk('public')->assertExists($competitionCover);
    }

    public function test_template_cover_replacement_preserves_legacy_shared_cover(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $superAdmin = $this->user('super-admin', 'Super Admin');
        $sharedCover = 'competition-templates/shared-cover.jpg';
        Storage::disk('public')->put($sharedCover, 'shared cover');
        $template->update(['cover_image' => $sharedCover]);
        $competition = $this->competition($admin, $category, $template);
        $competition->update(['cover_image' => $sharedCover]);

        $this->actingAs($superAdmin)
            ->put(route('superadmin.templates.update', $template), [
                'template_name' => $template->template_name,
                'template_slug' => $template->template_slug,
                'default_description' => $template->default_description,
                'cover_image' => UploadedFile::fake()->image('new-template-cover.jpg'),
                'is_active' => 1,
            ])
            ->assertRedirect(route('superadmin.templates.index'));

        Storage::disk('public')->assertExists($sharedCover);
        $this->assertSame($sharedCover, $competition->fresh()->cover_image);
        Storage::disk('public')->assertExists($template->fresh()->cover_image);
    }

    public function test_competition_without_template_can_be_edited_and_updated(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $competition->update(['template_id' => null, 'status' => 'completed']);

        $this->actingAs($admin)
            ->get(route('competition-admin.competitions.edit', $competition))
            ->assertOk();

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $category, [
                    'title' => 'Recovered Competition',
                    'template_id' => null,
                ])
            )
            ->assertRedirect(route('competition-admin.competitions.show', $competition))
            ->assertSessionHasNoErrors();

        $competition->refresh();
        $this->assertSame('Recovered Competition', $competition->title);
        $this->assertNull($competition->template_id);
        $this->assertSame('completed', $competition->status);
    }

    public function test_replacing_legacy_shared_cover_does_not_delete_template_cover(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $sharedCover = 'competition-templates/shared-cover.jpg';
        Storage::disk('public')->put($sharedCover, 'shared cover');
        $template->update(['cover_image' => $sharedCover]);
        $competition = $this->competition($admin, $category, $template);
        $competition->update(['cover_image' => $sharedCover]);

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $category, [
                    'cover_image' => UploadedFile::fake()->image('replacement.jpg'),
                ])
            )
            ->assertRedirect(route('competition-admin.competitions.show', $competition));

        $competition->refresh();
        $this->assertNotSame($sharedCover, $competition->cover_image);
        $this->assertStringStartsWith('competitions/', $competition->cover_image);
        Storage::disk('public')->assertExists($sharedCover);
        Storage::disk('public')->assertExists($competition->cover_image);
    }

    public function test_competition_update_failure_cleans_new_cover_and_preserves_old_cover(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $oldCover = 'competitions/old-cover.jpg';
        Storage::disk('public')->put($oldCover, 'old cover');
        $competition->update(['cover_image' => $oldCover]);

        DB::statement(
            "CREATE TRIGGER fail_competition_update
            BEFORE UPDATE ON competitions
            BEGIN
                SELECT RAISE(ABORT, 'forced competition update failure');
            END"
        );

        try {
            $response = $this->actingAs($admin)->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $category, [
                    'cover_image' => UploadedFile::fake()->image('new-cover.jpg'),
                ])
            );
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS fail_competition_update');
        }

        $response->assertServerError();
        $this->assertSame($oldCover, $competition->fresh()->cover_image);
        Storage::disk('public')->assertExists($oldCover);
        $this->assertEqualsCanonicalizing(
            [$oldCover],
            Storage::disk('public')->allFiles('competitions')
        );
    }

    public function test_template_update_failure_cleans_new_cover_and_preserves_old_cover(): void
    {
        Storage::fake('public');
        [, , $template] = $this->context('admin');
        $superAdmin = $this->user('super-admin', 'Super Admin');
        $oldCover = 'competition-templates/old-cover.jpg';
        Storage::disk('public')->put($oldCover, 'old cover');
        $template->update(['cover_image' => $oldCover]);

        DB::statement(
            "CREATE TRIGGER fail_template_update
            BEFORE UPDATE ON competition_templates
            BEGIN
                SELECT RAISE(ABORT, 'forced template update failure');
            END"
        );

        try {
            $response = $this->actingAs($superAdmin)->put(
                route('superadmin.templates.update', $template),
                [
                    'template_name' => $template->template_name,
                    'template_slug' => $template->template_slug,
                    'default_description' => $template->default_description,
                    'cover_image' => UploadedFile::fake()->image('new-cover.jpg'),
                    'is_active' => 1,
                ]
            );
        } finally {
            DB::statement('DROP TRIGGER IF EXISTS fail_template_update');
        }

        $response->assertServerError();
        $this->assertSame($oldCover, $template->fresh()->cover_image);
        Storage::disk('public')->assertExists($oldCover);
        $this->assertEqualsCanonicalizing(
            [$oldCover],
            Storage::disk('public')->allFiles('competition-templates')
        );
    }

    public function test_current_inactive_category_remains_available_and_can_be_saved(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $category->update(['is_active' => false]);

        $this->actingAs($admin)
            ->get(route('competition-admin.competitions.edit', $competition))
            ->assertOk()
            ->assertSee($category->category_name);

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $category, ['title' => 'Inactive Category Saved'])
            )
            ->assertRedirect(route('competition-admin.competitions.show', $competition))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'category_id' => $category->id,
            'title' => 'Inactive Category Saved',
        ]);
    }

    public function test_competition_can_change_to_an_active_category(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $activeCategory = CompetitionCategory::create([
            'category_name' => 'New active category',
            'category_slug' => 'new-active-category',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $activeCategory)
            )
            ->assertRedirect(route('competition-admin.competitions.show', $competition))
            ->assertSessionHasNoErrors();

        $this->assertSame($activeCategory->id, $competition->fresh()->category_id);
    }

    public function test_competition_cannot_change_to_another_inactive_category(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $inactiveCategory = CompetitionCategory::create([
            'category_name' => 'Other inactive category',
            'category_slug' => 'other-inactive-category',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $inactiveCategory)
            )
            ->assertSessionHasErrors('category_id');

        $this->assertSame($category->id, $competition->fresh()->category_id);
    }

    public function test_missing_physical_cover_uses_existing_placeholders(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $missingCover = 'competitions/missing-cover.jpg';
        $competition->update(['cover_image' => $missingCover]);
        $missingCoverUrl = Storage::disk('public')->url($missingCover);

        $this->actingAs($admin)
            ->get(route('competition-admin.competitions.index'))
            ->assertOk()
            ->assertDontSee($missingCoverUrl, false);

        $this->actingAs($admin)
            ->get(route('competition-admin.competitions.show', $competition))
            ->assertOk()
            ->assertDontSee($missingCoverUrl, false);

        $this->actingAs($admin)
            ->get(route('competition-admin.competitions.edit', $competition))
            ->assertOk()
            ->assertDontSee($missingCoverUrl, false);

        $this->actingAs($admin)
            ->get(route('competition-admin.judging-rooms.index'))
            ->assertOk()
            ->assertDontSee($missingCoverUrl, false);
    }

    public function test_judge_room_list_displays_template_name(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $judge = $this->user('judge', 'Judge');
        $competition = $this->competition($admin, $category, $template);
        JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $admin->id,
            'status' => 'waiting',
        ]);
        JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $this->actingAs($judge)
            ->get(route('judge.judging-rooms.index'))
            ->assertOk()
            ->assertSee('แบบฟอร์ม: '.$template->template_name);
    }

    public function test_metadata_update_preserves_existing_submission_and_workflow_status(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $competition->update(['status' => 'judging']);
        $submission = Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-METADATA-EDIT',
            'project_title' => 'Existing submission',
            'contact_name' => 'Test Owner',
            'contact_email' => 'owner@example.com',
            'contact_phone' => '0800000000',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(
                route('competition-admin.competitions.update', $competition),
                $this->updatePayload($competition, $category, [
                    'title' => 'Metadata Updated',
                    'status' => 'open',
                ])
            )
            ->assertRedirect(route('competition-admin.competitions.show', $competition));

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'title' => 'Metadata Updated',
            'status' => 'judging',
        ]);
        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'competition_id' => $competition->id,
            'project_title' => 'Existing submission',
        ]);
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

    public function test_competition_admin_can_delete_own_competition_without_dependent_data(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $coverImage = 'competitions/delete-owned-cover.jpg';
        Storage::disk('public')->put($coverImage, 'competition cover');
        $competition->update(['cover_image' => $coverImage]);

        $this->actingAs($admin)
            ->delete(route('competition-admin.competitions.destroy', $competition))
            ->assertRedirect(route('competition-admin.competitions.index'))
            ->assertSessionHas('success', 'ลบการแข่งขันเรียบร้อยแล้ว');

        $this->assertDatabaseMissing('competitions', ['id' => $competition->id]);
        Storage::disk('public')->assertMissing($coverImage);
    }

    public function test_deleting_competition_preserves_legacy_shared_template_cover(): void
    {
        Storage::fake('public');
        [$admin, $category, $template] = $this->context('admin');
        $sharedCover = 'competition-templates/legacy-shared-cover.jpg';
        Storage::disk('public')->put($sharedCover, 'shared cover');
        $template->update(['cover_image' => $sharedCover]);
        $competition = $this->competition($admin, $category, $template);
        $competition->update(['cover_image' => $sharedCover]);

        $this->actingAs($admin)
            ->delete(route('competition-admin.competitions.destroy', $competition))
            ->assertRedirect(route('competition-admin.competitions.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('competitions', ['id' => $competition->id]);
        Storage::disk('public')->assertExists($sharedCover);
    }

    public function test_competition_admin_cannot_delete_another_admins_competition(): void
    {
        [$owner, $category, $template] = $this->context('owner');
        $otherAdmin = $this->user('other-admin');
        $competition = $this->competition($owner, $category, $template);

        $this->actingAs($otherAdmin)
            ->delete(route('competition-admin.competitions.destroy', $competition))
            ->assertForbidden();

        $this->assertDatabaseHas('competitions', ['id' => $competition->id]);
    }

    public function test_competition_with_dependent_data_is_not_deleted_or_returned_as_500(): void
    {
        [$admin, $category, $template] = $this->context('admin');
        $competition = $this->competition($admin, $category, $template);
        $submission = Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-DELETE-BLOCK',
            'project_title' => 'Protected submission',
            'contact_name' => 'Test Owner',
            'contact_email' => 'owner@example.com',
            'contact_phone' => '0800000000',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('competition-admin.competitions.destroy', $competition));

        $response
            ->assertRedirect(route('competition-admin.competitions.index'))
            ->assertSessionHas('error');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseHas('competitions', ['id' => $competition->id]);
        $this->assertDatabaseHas('submissions', ['id' => $submission->id]);
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

    private function user(string $username, string $roleName = 'Competition Admin'): User
    {
        $role = Role::firstOrCreate(
            ['role_name' => $roleName],
            ['display_name' => $roleName]
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
