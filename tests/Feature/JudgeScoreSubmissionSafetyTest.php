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

class JudgeScoreSubmissionSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_accepted_judge_can_submit_all_rubrics_in_live_room(): void
    {
        $context = $this->createScoringContext(2);

        $this->submitScores($context, [7, 8])->assertRedirect();

        $scores = Score::query()->orderBy('rubric_id')->get();
        $this->assertCount(2, $scores);
        $this->assertSame(['7.00', '8.00'], $scores->pluck('score')->all());
        $this->assertTrue($scores->every(fn ($score) => $score->submitted_at !== null));
        $this->assertNotNull($context['submission']->fresh()->final_score);
    }

    public function test_second_submit_cannot_change_submitted_score_comment_timestamp_or_final_score(): void
    {
        $context = $this->createScoringContext();
        $this->submitScores($context, [6], ['original'])->assertRedirect();

        $originalScore = Score::sole();
        $originalFinalScore = $context['submission']->fresh()->final_score;

        $this->submitScores($context, [9], ['changed'])->assertStatus(422);

        $score = $originalScore->fresh();
        $this->assertSame('6.00', $score->score);
        $this->assertSame('original', $score->comment);
        $this->assertTrue($score->submitted_at->equalTo($originalScore->submitted_at));
        $this->assertSame(
            $originalFinalScore,
            $context['submission']->fresh()->final_score
        );
    }

    public function test_non_live_room_states_cannot_submit_scores(): void
    {
        foreach (['waiting', 'paused', 'ended', 'closed'] as $status) {
            $context = $this->createScoringContext(1, $status);

            $this->submitScores($context, [5])->assertStatus(422);
            $this->assertDatabaseMissing('scores', [
                'submission_id' => $context['submission']->id,
            ]);
        }
    }

    public function test_judge_cannot_submit_using_another_judges_assignment(): void
    {
        $context = $this->createScoringContext();
        $otherJudge = $this->createUser($context['judge_role_id'], 'other-judge');

        $this->actingAs($otherJudge)
            ->post(
                route('judge.judging-rooms.scores.submit', $context['session']),
                $this->scorePayload($context, [5])
            )
            ->assertNotFound();

        $this->assertDatabaseCount('scores', 0);
    }

    public function test_pending_and_declined_assignments_cannot_submit(): void
    {
        foreach (['pending', 'declined'] as $status) {
            $context = $this->createScoringContext(1, 'live', $status);

            $this->submitScores($context, [5])->assertStatus(403);
            $this->assertDatabaseMissing('scores', [
                'submission_id' => $context['submission']->id,
            ]);
        }
    }

    public function test_zero_score_can_be_submitted(): void
    {
        $context = $this->createScoringContext();

        $this->submitScores($context, [0])->assertRedirect();

        $score = Score::sole();
        $this->assertSame('0.00', $score->score);
        $this->assertNotNull($score->submitted_at);
        $this->assertSame('0.00', $context['submission']->fresh()->final_score);
    }

    private function submitScores(
        array $context,
        array $scores,
        array $comments = []
    ) {
        return $this->actingAs($context['judge'])->post(
            route('judge.judging-rooms.scores.submit', $context['session']),
            $this->scorePayload($context, $scores, $comments)
        );
    }

    private function scorePayload(
        array $context,
        array $scores,
        array $comments = []
    ): array {
        $rubricScores = [];

        foreach ($context['rubrics'] as $index => $rubric) {
            $rubricScores[$rubric->id] = [
                'score' => $scores[$index],
                'comment' => $comments[$index] ?? null,
            ];
        }

        return [
            'submission_id' => $context['submission']->id,
            'scores' => $rubricScores,
        ];
    }

    private function createScoringContext(
        int $rubricCount = 1,
        string $sessionStatus = 'live',
        string $assignmentStatus = 'accepted'
    ): array {
        $adminRoleId = $this->createRole('Competition Admin '.uniqid());
        $judgeRoleId = DB::table('roles')
            ->where('role_name', 'Judge')
            ->value('id') ?? $this->createRole('Judge');
        $admin = $this->createUser($adminRoleId, 'admin');
        $judge = $this->createUser($judgeRoleId, 'judge');
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId,
            'created_by' => $admin->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDays(3),
            'registration_end' => now()->subDays(2),
            'judging_start' => now()->subDay(),
            'judging_end' => now()->addDay(),
            'status' => 'judging',
        ]);
        $submission = Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Project',
            'contact_name' => 'Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        $session = JudgingSession::create([
            'competition_id' => $competition->id,
            'current_submission_id' => $submission->id,
            'status' => $sessionStatus,
        ]);
        $assignment = JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => $assignmentStatus,
            'accepted_at' => $assignmentStatus === 'accepted' ? now() : null,
            'declined_at' => $assignmentStatus === 'declined' ? now() : null,
        ]);
        $rubrics = collect();

        for ($index = 1; $index <= $rubricCount; $index++) {
            $rubrics->push(Rubric::create([
                'competition_id' => $competition->id,
                'criteria_name' => "Rubric {$index}",
                'max_score' => 10,
                'weight' => 100 / $rubricCount,
                'sort_order' => $index,
                'is_active' => true,
            ]));
        }

        return compact(
            'competition',
            'submission',
            'session',
            'assignment',
            'rubrics',
            'judge'
        ) + ['judge_role_id' => $judgeRoleId];
    }

    private function createRole(string $name): int
    {
        return DB::table('roles')->insertGetId([
            'role_name' => $name,
            'display_name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(int $roleId, string $prefix): User
    {
        return User::create([
            'role_id' => $roleId,
            'username' => $prefix.'-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }
}
