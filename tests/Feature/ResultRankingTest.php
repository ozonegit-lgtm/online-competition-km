<?php

namespace Tests\Feature;

use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\KnowledgeItem;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ResultRankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_final_score_uses_submitted_scores_only(): void
    {
        $context = $this->scoringContext([[100, 100]], 1);
        $pendingJudge = $this->user('pending', 'Judge');
        $pending = $this->assignment($context['competition'], $pendingJudge, 'pending');
        Score::create([
            'submission_id' => $context['submission']->id,
            'rubric_id' => $context['rubrics'][0]->id,
            'judge_assignment_id' => $pending->id,
            'score' => 100,
            'submitted_at' => null,
        ]);

        $this->submitScores($context, 0, [70]);

        $this->assertSame('70.00', $context['submission']->fresh()->final_score);
    }

    public function test_inactive_rubric_is_excluded_from_final_score(): void
    {
        $context = $this->scoringContext([[100, 100]], 1);
        $inactive = Rubric::create([
            'competition_id' => $context['competition']->id,
            'criteria_name' => 'Inactive',
            'max_score' => 100,
            'weight' => 100,
            'sort_order' => 2,
            'is_active' => false,
        ]);
        Score::create([
            'submission_id' => $context['submission']->id,
            'rubric_id' => $inactive->id,
            'judge_assignment_id' => $context['assignments'][0]->id,
            'score' => 0,
            'submitted_at' => now(),
        ]);

        $this->submitScores($context, 0, [80]);

        $this->assertSame('80.00', $context['submission']->fresh()->final_score);
    }

    public function test_non_accepted_judge_score_is_excluded_from_final_score(): void
    {
        $context = $this->scoringContext([[100, 100]], 1);
        $declinedJudge = $this->user('declined', 'Judge');
        $declined = $this->assignment($context['competition'], $declinedJudge, 'declined');
        Score::create([
            'submission_id' => $context['submission']->id,
            'rubric_id' => $context['rubrics'][0]->id,
            'judge_assignment_id' => $declined->id,
            'score' => 100,
            'submitted_at' => now(),
        ]);

        $this->submitScores($context, 0, [40]);

        $this->assertSame('40.00', $context['submission']->fresh()->final_score);
    }

    public function test_multi_judge_final_score_uses_average_of_judge_totals(): void
    {
        $context = $this->scoringContext([[100, 100]], 2);

        $this->submitScores($context, 0, [60]);
        $this->assertNull($context['submission']->fresh()->final_score);
        $this->submitScores($context, 1, [100]);

        $this->assertSame('80.00', $context['submission']->fresh()->final_score);
    }

    public function test_multi_rubric_final_score_uses_weighted_scores(): void
    {
        $context = $this->scoringContext([[40, 40], [60, 60]], 1);

        $this->submitScores($context, 0, [20, 30]);

        $this->assertSame('50.00', $context['submission']->fresh()->final_score);
        $this->assertLessThanOrEqual(100, (float) $context['submission']->fresh()->final_score);
    }

    public function test_ranking_is_descending(): void
    {
        $context = $this->rankingContext([70, 90, 80]);

        $ranked = $this->ranked($context);

        $this->assertSame([90.0, 80.0, 70.0], $ranked->pluck('final_score')->map(fn ($v) => (float) $v)->all());
        $this->assertSame([1, 2, 3], $ranked->pluck('rank')->all());
    }

    public function test_tie_ranking_uses_competition_ranks_one_one_three(): void
    {
        $context = $this->rankingContext([90, 90, 80]);

        $ranked = $this->ranked($context);

        $this->assertSame([90.0, 90.0, 80.0], $ranked->pluck('final_score')->map(fn ($v) => (float) $v)->all());
        $this->assertSame([1, 1, 3], $ranked->pluck('rank')->all());
    }

    public function test_all_submissions_tied_at_rank_three_are_included(): void
    {
        $context = $this->rankingContext([100, 90, 80, 80]);

        $ranked = $this->ranked($context);

        $this->assertCount(4, $ranked);
        $this->assertSame([1, 2, 3, 3], $ranked->pluck('rank')->all());
    }

    public function test_disqualified_submission_is_excluded_from_ranking(): void
    {
        $context = $this->rankingContext([90, 80]);
        $disqualified = $this->rankableSubmission($context, 100, 'disqualified');

        $ranked = $this->ranked($context);

        $this->assertSame([90.0, 80.0], $ranked->pluck('final_score')->map(fn ($v) => (float) $v)->all());
        $this->assertFalse($ranked->contains('id', $disqualified->id));
    }

    public function test_incomplete_submission_is_pending_and_not_ranked(): void
    {
        $context = $this->rankingContext([90]);
        $incomplete = $this->submission($context['competition'], 100);

        $response = $this->actingAs($context['owner'])->get(
            route('competition-admin.competitions.results.index', $context['competition'])
        );

        $response->assertOk()
            ->assertViewHas('rankedSubmissions', fn ($items) => ! $items->contains('id', $incomplete->id))
            ->assertViewHas('pendingSubmissions', fn ($items) => $items->contains('id', $incomplete->id))
            ->assertViewHas('isReadyForResults', false);
    }

    public function test_ranking_is_hidden_until_session_is_finished(): void
    {
        $context = $this->rankingContext([90], 'live');

        $this->actingAs($context['owner'])->get(
            route('competition-admin.competitions.results.index', $context['competition'])
        )->assertOk()
            ->assertViewHas('sessionFinished', false)
            ->assertViewHas('rankedSubmissions', fn ($items) => $items->isEmpty());
    }

    public function test_other_admin_cannot_view_results(): void
    {
        $context = $this->rankingContext([90]);
        $otherAdmin = $this->user('other-admin', 'Competition Admin');

        $response = $this->actingAs($otherAdmin)->get(
            route('competition-admin.competitions.results.index', $context['competition'])
        );

        $response->assertForbidden();
        $this->assertNotSame(500, $response->getStatusCode());
    }

    public function test_equal_score_ranking_is_deterministic_by_submission_time(): void
    {
        $context = $this->rankingContext([]);
        $first = $this->rankableSubmission($context, 90, 'submitted', now()->subMinute());
        $second = $this->rankableSubmission($context, 90, 'submitted', now());

        $firstRead = $this->ranked($context);
        $secondRead = $this->ranked($context);

        $this->assertSame([$first->id, $second->id], $firstRead->modelKeys());
        $this->assertSame($firstRead->modelKeys(), $secondRead->modelKeys());
        $this->assertSame([1, 1], $secondRead->pluck('rank')->all());
    }

    public function test_result_read_does_not_mutate_judging_data(): void
    {
        $context = $this->rankingContext([90]);
        $submission = $context['submissions'][0];
        $score = Score::where('submission_id', $submission->id)->sole();
        $before = [
            'submission' => $submission->fresh()->getAttributes(),
            'score' => $score->fresh()->getAttributes(),
            'assignment' => $context['assignment']->fresh()->getAttributes(),
            'session' => $context['session']->fresh()->getAttributes(),
        ];

        $this->actingAs($context['owner'])->get(
            route('competition-admin.competitions.results.index', $context['competition'])
        )->assertOk();

        $this->assertSame($before['submission'], $submission->fresh()->getAttributes());
        $this->assertSame($before['score'], $score->fresh()->getAttributes());
        $this->assertSame($before['assignment'], $context['assignment']->fresh()->getAttributes());
        $this->assertSame($before['session'], $context['session']->fresh()->getAttributes());
    }

    public function test_public_km_detail_shows_top_three_ranks_matching_the_public_index(): void
    {
        $context = $this->publishedRankingContext([70.25, 90.75, 80.50]);
        $this->assertTrue($context['competition']->resultReadiness()['ready']);
        $index = $this->get(route('home'))->assertOk()
            ->viewData('publishedResults')->sole()->submissions->keyBy('id');

        foreach ($context['submissions'] as $submission) {
            $before = $submission->fresh()->getAttributes();
            $rank = $index->get($submission->id)->rank;
            $item = $this->publicKnowledgeItem($submission);

            $this->get(route('knowledge.show', $item))->assertOk()
                ->assertSeeText('อันดับ '.$rank)
                ->assertDontSeeText('อันดับ '.$rank.' ร่วม')
                ->assertViewHas('knowledgeItem', fn ($item) =>
                    $item->submission->rank === $rank
                    && $item->submission->is_shared_rank === false
                );

            $this->assertSame($before, $submission->fresh()->getAttributes());
        }
    }

    public function test_public_km_detail_shows_shared_first_place_with_competition_ranks_one_one_three(): void
    {
        $context = $this->publishedRankingContext([90.75, 90.75, 80.50]);

        foreach ($context['submissions'] as $position => $submission) {
            $rank = [1, 1, 3][$position];
            $shared = $position < 2;
            $item = $this->publicKnowledgeItem($submission);
            $response = $this->get(route('knowledge.show', $item))->assertOk()
                ->assertSeeText('อันดับ '.$rank.($shared ? ' ร่วม' : ''))
                ->assertViewHas('knowledgeItem', fn ($item) =>
                    $item->submission->rank === $rank
                    && $item->submission->is_shared_rank === $shared
                );

            if (! $shared) {
                $response->assertDontSeeText('อันดับ 3 ร่วม');
            }
        }
    }

    public function test_public_km_detail_shows_every_submission_tied_at_third_place(): void
    {
        $context = $this->publishedRankingContext([100, 90, 80, 80]);

        foreach ($context['submissions']->slice(2) as $submission) {
            $item = $this->publicKnowledgeItem($submission);

            $this->get(route('knowledge.show', $item))->assertOk()
                ->assertSeeText('อันดับ 3 ร่วม')
                ->assertViewHas('knowledgeItem', fn ($item) =>
                    $item->submission->rank === 3
                    && $item->submission->is_shared_rank === true
                );
        }
    }

    public function test_public_km_detail_has_no_badge_below_third_place(): void
    {
        $context = $this->publishedRankingContext([100, 90, 80, 70]);

        $this->assertPublicDetailHasNoRank($this->publicKnowledgeItem($context['submissions']->last()));
    }

    public function test_public_km_detail_has_no_badge_when_scores_are_unpublished(): void
    {
        $context = $this->publishedRankingContext([90]);
        $context['competition']->update(['publish_scores' => false]);

        $this->assertPublicDetailHasNoRank($this->publicKnowledgeItem($context['submissions']->first()));
    }

    public function test_public_km_detail_has_no_badge_without_result_announcement(): void
    {
        $context = $this->publishedRankingContext([90]);
        $context['competition']->update(['result_announcement' => null]);

        $this->assertPublicDetailHasNoRank($this->publicKnowledgeItem($context['submissions']->first()));
    }

    public function test_public_km_detail_has_no_badge_until_judging_is_finished(): void
    {
        $context = $this->publishedRankingContext([90], 'live');
        $this->assertFalse($context['competition']->resultReadiness()['ready']);

        $this->assertPublicDetailHasNoRank($this->publicKnowledgeItem($context['submissions']->first()));
    }

    public function test_public_km_detail_has_no_badge_until_all_submissions_have_completed_scores(): void
    {
        $context = $this->publishedRankingContext([90]);
        $this->submission($context['competition'], 100);
        $this->assertFalse($context['competition']->resultReadiness()['ready']);

        $this->assertPublicDetailHasNoRank($this->publicKnowledgeItem($context['submissions']->first()));
    }

    public function test_public_km_detail_excludes_disqualified_submissions_and_keeps_their_detail_hidden(): void
    {
        $context = $this->publishedRankingContext([90]);
        $disqualified = $this->rankableSubmission($context, 100, 'disqualified');
        $item = $this->publicKnowledgeItem($context['submissions']->first());

        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSeeText('อันดับ 1')
            ->assertViewHas('knowledgeItem', fn ($item) => $item->submission->rank === 1);
        $this->get(route('knowledge.show', $this->publicKnowledgeItem($disqualified)))->assertNotFound();
    }

    public function test_public_km_detail_ranks_only_submissions_from_the_same_competition(): void
    {
        $context = $this->publishedRankingContext([70]);
        $this->publishedRankingContext([100, 90, 80, 70]);
        $item = $this->publicKnowledgeItem($context['submissions']->first());

        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSeeText('อันดับ 1')
            ->assertDontSeeText('อันดับ 1 ร่วม')
            ->assertViewHas('knowledgeItem', fn ($item) =>
                $item->submission->rank === 1
                && $item->submission->is_shared_rank === false
            );
    }

    public function test_public_manual_km_detail_has_no_rank_badge(): void
    {
        $this->publishedRankingContext([90]);
        $item = KnowledgeItem::create([
            'title' => 'Manual knowledge item',
            'content' => 'Manual content',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertSeeText('Manual content')
            ->assertDontSeeText('อันดับ')
            ->assertViewHas('knowledgeItem', fn ($item) => $item->submission === null);
    }

    private function publishedRankingContext(array $scores, string $sessionStatus = 'ended'): array
    {
        $context = $this->rankingContext($scores, $sessionStatus);
        $context['competition']->update(['publish_scores' => true, 'result_announcement' => now()]);

        return $context;
    }

    private function publicKnowledgeItem(Submission $submission): KnowledgeItem
    {
        return KnowledgeItem::create([
            'submission_id' => $submission->id,
            'title' => $submission->project_title,
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    private function assertPublicDetailHasNoRank(KnowledgeItem $item): void
    {
        $this->get(route('knowledge.show', $item))->assertOk()
            ->assertDontSeeText('อันดับ')
            ->assertViewHas('knowledgeItem', fn ($item) =>
                ! array_key_exists('rank', $item->submission->getAttributes())
                && ! array_key_exists('is_shared_rank', $item->submission->getAttributes())
            );
    }

    private function submitScores(array $context, int $judgeIndex, array $scores): void
    {
        $payload = [];
        foreach ($context['rubrics'] as $index => $rubric) {
            $payload[$rubric->id] = ['score' => $scores[$index], 'comment' => null];
        }

        if (auth()->id() !== $context['judges'][$judgeIndex]->id) {
            $this->flushSession();
            auth()->forgetGuards();
        }

        $this->actingAs($context['judges'][$judgeIndex])->post(
            route('judge.judging-rooms.scores.submit', $context['session']),
            ['submission_id' => $context['submission']->id, 'scores' => $payload]
        )->assertRedirect()->assertSessionHas('success');
    }

    private function scoringContext(array $rubricDefinitions, int $judgeCount): array
    {
        [$owner, $competition] = $this->baseCompetition();
        $submission = $this->submission($competition, null);
        $rubrics = collect();
        foreach ($rubricDefinitions as $index => [$maxScore, $weight]) {
            $rubrics->push(Rubric::create([
                'competition_id' => $competition->id,
                'criteria_name' => 'Rubric '.($index + 1),
                'max_score' => $maxScore,
                'weight' => $weight,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]));
        }
        $judges = collect();
        $assignments = collect();
        for ($index = 0; $index < $judgeCount; $index++) {
            $judge = $this->user('judge-'.$index, 'Judge');
            $judges->push($judge);
            $assignments->push($this->assignment($competition, $judge, 'accepted'));
        }
        $session = JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $owner->id,
            'current_submission_id' => $submission->id,
            'status' => 'live',
            'started_at' => now(),
        ]);

        return compact('owner', 'competition', 'submission', 'rubrics', 'judges', 'assignments', 'session');
    }

    private function rankingContext(array $scores, string $sessionStatus = 'ended'): array
    {
        [$owner, $competition] = $this->baseCompetition();
        $rubric = Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Overall',
            'max_score' => 100,
            'weight' => 100,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $judge = $this->user('judge', 'Judge');
        $assignment = $this->assignment($competition, $judge, 'accepted');
        $session = JudgingSession::create([
            'competition_id' => $competition->id,
            'controller_user_id' => $owner->id,
            'status' => $sessionStatus,
            'started_at' => now()->subHour(),
            'ended_at' => in_array($sessionStatus, ['ended', 'closed'], true) ? now() : null,
        ]);
        $submissions = collect();
        foreach ($scores as $score) {
            $submissions->push($this->rankableSubmission(compact(
                'competition', 'rubric', 'assignment'
            ), $score));
        }

        return compact('owner', 'competition', 'rubric', 'judge', 'assignment', 'session', 'submissions');
    }

    private function ranked(array $context)
    {
        $response = $this->actingAs($context['owner'])->get(
            route('competition-admin.competitions.results.index', $context['competition'])
        );
        $response->assertOk();

        return $response->viewData('rankedSubmissions');
    }

    private function rankableSubmission(
        array $context,
        float $finalScore,
        string $status = 'submitted',
        $submittedAt = null
    ): Submission {
        $submission = $this->submission(
            $context['competition'],
            $finalScore,
            $status,
            $submittedAt
        );
        Score::create([
            'submission_id' => $submission->id,
            'rubric_id' => $context['rubric']->id,
            'judge_assignment_id' => $context['assignment']->id,
            'score' => $finalScore,
            'submitted_at' => now(),
        ]);

        return $submission;
    }

    private function baseCompetition(): array
    {
        $owner = $this->user('owner', 'Competition Admin');
        $categoryId = DB::table('competition_categories')->insertGetId([
            'category_name' => 'Category '.uniqid(),
            'category_slug' => 'category-'.uniqid(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $competition = Competition::create([
            'category_id' => $categoryId,
            'created_by' => $owner->id,
            'title' => 'Competition '.uniqid(),
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subDay(),
            'registration_end' => now()->addDay(),
            'status' => 'open',
        ]);

        return [$owner, $competition];
    }

    private function user(string $prefix, string $roleName): User
    {
        $roleId = DB::table('roles')->where('role_name', $roleName)->value('id');
        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'role_name' => $roleName,
                'display_name' => $roleName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return User::create([
            'role_id' => $roleId,
            'username' => $prefix.'-'.uniqid(),
            'email' => uniqid().'@example.com',
            'password' => 'password',
            'is_active' => true,
        ]);
    }

    private function assignment(Competition $competition, User $judge, string $status): JudgeAssignment
    {
        return JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $judge->id,
            'assignment_status' => $status,
            'accepted_at' => $status === 'accepted' ? now() : null,
            'declined_at' => $status === 'declined' ? now() : null,
        ]);
    }

    private function submission(
        Competition $competition,
        ?float $finalScore,
        string $status = 'submitted',
        $submittedAt = null
    ): Submission {
        return Submission::create([
            'competition_id' => $competition->id,
            'submission_code' => 'SUB-'.uniqid(),
            'project_title' => 'Project '.uniqid(),
            'contact_name' => 'Submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'final_score' => $finalScore,
            'status' => $status,
            'submitted_at' => $submittedAt ?? now(),
        ]);
    }
}
