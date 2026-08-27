<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResultPublicationSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_competition_does_not_publish_results_automatically(): void
    {
        [$competition] = $this->createCompetition();

        $this->assertFalse($competition->fresh()->publish_scores);
    }

    public function test_new_submission_has_a_null_final_score(): void
    {
        [$competition] = $this->createCompetition();
        $submission = $this->createSubmission($competition);

        $this->assertNull($submission->fresh()->final_score);
    }

    public function test_incomplete_published_competition_is_hidden_from_public_page(): void
    {
        [$competition] = $this->createReadyStructure(true, false);

        $this->get('/')
            ->assertOk()
            ->assertDontSee($competition->title);
    }

    public function test_publish_endpoint_rejects_incomplete_scores(): void
    {
        [$competition, $admin] = $this->createReadyStructure(false, false);

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.results.publish', $competition))
            ->assertSessionHasErrors('results');

        $this->assertFalse($competition->fresh()->publish_scores);
    }

    public function test_forged_competition_update_cannot_change_publication_flags(): void
    {
        [$competition, $admin, $category, $template] = $this->createCompetition();
        $competition->update(['publish_scores' => false, 'publish_km' => true]);

        $this->actingAs($admin)->put(
            route('competition-admin.competitions.update', $competition),
            $this->validCompetitionUpdate($competition, $category->id, $template->id) + [
                'publish_scores' => true,
                'publish_km' => false,
            ]
        )->assertRedirect(route('competition-admin.competitions.show', $competition));

        $competition->refresh();
        $this->assertFalse($competition->publish_scores);
        $this->assertTrue($competition->publish_km);
    }

    public function test_complete_real_zero_score_can_be_published(): void
    {
        [$competition, $admin] = $this->createReadyStructure(false, true, 0);

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.results.publish', $competition))
            ->assertRedirect();

        $this->assertTrue($competition->fresh()->publish_scores);
    }

    public function test_valid_published_result_is_visible_on_public_page(): void
    {
        [$competition] = $this->createReadyStructure(true, true, 0);

        $this->get('/')
            ->assertOk()
            ->assertSee($competition->title);
    }

    public function test_unpublished_result_disappears_from_public_page(): void
    {
        [$competition, $admin] = $this->createReadyStructure(true, true, 0);

        $this->actingAs($admin)
            ->delete(route('competition-admin.competitions.results.unpublish', $competition))
            ->assertRedirect();

        $this->get('/')
            ->assertOk()
            ->assertDontSee($competition->title);
    }

    public function test_result_publication_does_not_change_km_publication_flag(): void
    {
        [$competition, $admin] = $this->createReadyStructure(false, true, 0);
        $competition->update(['publish_km' => true]);

        $this->actingAs($admin)
            ->post(route('competition-admin.competitions.results.publish', $competition));
        $this->assertTrue($competition->fresh()->publish_km);

        $this->actingAs($admin)
            ->delete(route('competition-admin.competitions.results.unpublish', $competition));
        $this->assertTrue($competition->fresh()->publish_km);
    }

    private function createReadyStructure(
        bool $published,
        bool $withScore,
        float $score = 10
    ): array {
        [$competition, $admin] = $this->createCompetition();
        $competition->update(['publish_scores' => $published]);
        $judge = $this->createUser('Judge');
        $submission = $this->createSubmission($competition);

        JudgingSession::create([
            'competition_id' => $competition->id,
            'status' => 'ended',
        ]);
        $rubric = Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Quality',
            'max_score' => 10,
            'weight' => 100,
            'is_active' => true,
        ]);
        $assignment = JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
        ]);

        if ($withScore) {
            Score::create([
                'submission_id' => $submission->id,
                'rubric_id' => $rubric->id,
                'judge_assignment_id' => $assignment->id,
                'score' => $score,
                'submitted_at' => now(),
            ]);
            $submission->update(['final_score' => $score]);
        }

        return [$competition->fresh(), $admin];
    }

    private function createCompetition(): array
    {
        $admin = $this->createUser('Competition Admin');
        $category = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category ' . uniqid(),
            'category_slug' => 'category-' . uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $template = DB::table('competition_templates')->insertGetId([
            'template_name' => 'Template ' . uniqid(),
            'template_slug' => 'template-' . uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $competition = Competition::create([
            'category_id' => $category,
            'template_id' => $template,
            'created_by' => $admin->id,
            'title' => 'Competition ' . uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDays(5),
            'registration_end' => now()->subDays(4),
            'judging_start' => now()->subDays(3),
            'judging_end' => now()->subDays(2),
            'result_announcement' => now()->subDay(),
            'status' => 'closed',
        ]);

        return [
            $competition,
            $admin,
            (object) ['id' => $category],
            (object) ['id' => $template],
        ];
    }

    private function createSubmission(Competition $competition): Submission
    {
        return Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-' . uniqid(),
            'project_title' => 'Project ' . uniqid(),
            'contact_name' => 'Test Person',
            'contact_email' => uniqid() . '@example.com',
            'contact_phone' => '0800000000',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    private function createUser(string $roleName): User
    {
        $roleId = DB::table('roles')->insertGetId([
            'role_name' => $roleName,
            'display_name' => $roleName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::create([
            'role_id' => $roleId,
            'username' => strtolower(str_replace(' ', '-', $roleName)) . '-' . uniqid(),
            'email' => uniqid() . '@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function validCompetitionUpdate(
        Competition $competition,
        int $categoryId,
        int $templateId
    ): array {
        return [
            'category_id' => $categoryId,
            'template_id' => $templateId,
            'title' => $competition->title,
            'description' => null,
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDays(5)->format('Y-m-d H:i:s'),
            'registration_end' => now()->subDays(4)->format('Y-m-d H:i:s'),
            'judging_start' => now()->subDays(3)->format('Y-m-d H:i:s'),
            'judging_end' => now()->subDays(2)->format('Y-m-d H:i:s'),
            'result_announcement' => now()->subDay()->format('Y-m-d H:i:s'),
            'status' => 'closed',
        ];
    }
}
