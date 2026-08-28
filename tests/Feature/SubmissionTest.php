<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\CompetitionFormField;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config([
            'submissions.form_guard.minimum_seconds' => 2,
            'submissions.form_guard.ttl_minutes' => 120,
        ]);
    }

    public function test_valid_public_submission_persists_submission_and_contact_information(): void
    {
        $competition = $this->competition();
        $this->field($competition, ['is_required' => false]);

        $response = $this->submit($competition, [
            'project_title' => 'Community Innovation',
            'contact_name' => 'Test Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0812345678',
        ]);

        $submission = Submission::sole();
        $response->assertRedirect(route('submissions.success', $submission));
        $this->assertSame($competition->id, $submission->competition_id);
        $this->assertMatchesRegularExpression('/^SUB-\d{8}-[A-Z0-9]{6}$/', $submission->submission_code);
        $this->assertSame('submitted', $submission->status);
        $this->assertNotNull($submission->submitted_at);
        $this->assertSame('Community Innovation', $submission->project_title);
        $this->assertSame('Test Submitter', $submission->contact_name);
        $this->assertSame('submitter@example.com', $submission->contact_email);
        $this->assertSame('0812345678', $submission->contact_phone);
    }

    public function test_required_dynamic_field_is_rejected_when_missing(): void
    {
        $competition = $this->competition();
        $field = $this->field($competition, ['is_required' => true]);

        $response = $this->submit($competition);

        $response->assertSessionHasErrors("fields.{$field->id}");
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_optional_dynamic_field_can_be_omitted(): void
    {
        $competition = $this->competition();
        $this->field($competition, ['is_required' => false]);

        $this->submit($competition)->assertRedirect();

        $this->assertDatabaseCount('submissions', 1);
        $this->assertDatabaseCount('submission_field_values', 0);
    }

    public function test_multiple_dynamic_field_values_are_persisted_for_submission(): void
    {
        $competition = $this->competition();
        $text = $this->field($competition, [
            'label' => 'Summary',
            'field_name' => 'summary',
            'field_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);
        $textarea = $this->field($competition, [
            'label' => 'Details',
            'field_name' => 'details',
            'field_type' => 'textarea',
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $this->submit($competition, [
            'fields' => [
                $text->id => 'Short summary',
                $textarea->id => 'Long-form project details',
            ],
        ])->assertRedirect();

        $submission = Submission::sole();
        $this->assertDatabaseHas('submission_field_values', [
            'submission_id' => $submission->id,
            'field_id' => $text->id,
            'field_value' => 'Short summary',
        ]);
        $this->assertDatabaseHas('submission_field_values', [
            'submission_id' => $submission->id,
            'field_id' => $textarea->id,
            'field_value' => 'Long-form project details',
        ]);
    }

    public function test_private_competition_accepts_correct_access_code(): void
    {
        $competition = $this->competition('private', 'SECRET-123');
        $this->field($competition, ['is_required' => false]);

        $this->submit($competition, ['access_code' => 'SECRET-123'])
            ->assertRedirect();

        $this->assertDatabaseCount('submissions', 1);
    }

    public function test_private_competition_rejects_wrong_access_code(): void
    {
        $competition = $this->competition('private', 'SECRET-123');
        $this->field($competition, ['is_required' => false]);

        $response = $this->submit($competition, ['access_code' => 'WRONG']);

        $response->assertSessionHasErrors('access_code');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_private_competition_rejects_missing_access_code(): void
    {
        $competition = $this->competition('private', 'SECRET-123');
        $this->field($competition, ['is_required' => false]);

        $response = $this->submit($competition);

        $response->assertSessionHasErrors('access_code');
        $this->assertNotSame(500, $response->getStatusCode());
        $this->assertDatabaseCount('submissions', 0);
    }

    public function test_dynamic_field_from_another_competition_cannot_be_injected(): void
    {
        $competitionA = $this->competition();
        $this->field($competitionA, ['is_required' => false]);
        $competitionB = $this->competition();
        $foreignField = $this->field($competitionB, ['is_required' => false]);

        $this->submit($competitionA, [
            'fields' => [$foreignField->id => 'Injected value'],
        ])->assertRedirect();

        $submission = Submission::where('competition_id', $competitionA->id)->sole();
        $this->assertDatabaseMissing('submission_field_values', [
            'submission_id' => $submission->id,
            'field_id' => $foreignField->id,
        ]);
    }

    public function test_inactive_dynamic_field_is_not_required_or_persisted(): void
    {
        $competition = $this->competition();
        $this->field($competition, ['is_required' => false, 'is_active' => true]);
        $inactiveField = $this->field($competition, [
            'label' => 'Inactive Required Field',
            'field_name' => 'inactive_required',
            'is_required' => true,
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $this->submit($competition, [
            'fields' => [$inactiveField->id => 'Must not persist'],
        ])->assertRedirect();

        $submission = Submission::sole();
        $this->assertDatabaseMissing('submission_field_values', [
            'submission_id' => $submission->id,
            'field_id' => $inactiveField->id,
        ]);
    }

    private function submit(Competition $competition, array $overrides = [])
    {
        return $this->post(
            route('competitions.submissions.store', $competition),
            array_replace_recursive($this->payload($competition), $overrides)
        );
    }

    private function payload(Competition $competition): array
    {
        return [
            'project_title' => 'Test Project',
            'contact_name' => 'Test Person',
            'contact_email' => 'person@example.com',
            'contact_phone' => '0800000000',
            'terms' => '1',
            'website' => '',
            'form_guard_token' => Crypt::encryptString(json_encode([
                'competition_id' => $competition->id,
                'issued_at' => now()->subSeconds(2)->timestamp,
                'nonce' => bin2hex(random_bytes(20)),
            ], JSON_THROW_ON_ERROR)),
        ];
    }

    private function competition(
        string $visibility = 'public',
        ?string $accessCode = null
    ): Competition {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => 'Competition Admin '.uniqid(),
            'display_name' => 'Competition Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $admin = User::create([
            'role_id' => $roleId,
            'username' => 'admin-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Competition::create([
            'category_id' => $categoryId,
            'created_by' => $admin->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => $visibility,
            'access_code' => $accessCode,
            'registration_start' => now()->subHour(),
            'registration_end' => now()->addHour(),
            'status' => 'open',
        ]);
    }

    private function field(
        Competition $competition,
        array $overrides = []
    ): CompetitionFormField {
        return CompetitionFormField::create(array_replace([
            'competition_id' => $competition->id,
            'label' => 'Details',
            'field_name' => 'details',
            'field_type' => 'text',
            'is_required' => false,
            'sort_order' => 1,
            'is_active' => true,
        ], $overrides));
    }
}
