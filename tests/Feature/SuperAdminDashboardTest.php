<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Competition;
use App\Models\CompetitionCategory;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\KnowledgeItem;
use App\Models\Role;
use App\Models\Rubric;
use App\Models\Score;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Carbon::setTestNow(Carbon::parse('2026-09-08 12:00:00', 'Asia/Bangkok'));

        // Real role IDs need not be 1, 2 and 3, and labels may be localized.
        DB::table('roles')->insert([
            'id' => 40,
            'role_name' => 'Unused fixture role',
            'display_name' => 'Unused role',
        ]);
        $this->superAdmin = $this->createUser('Super Admin');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_dashboard_has_real_zero_counts_and_empty_collections(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewIs('superadmin.dashboard')
            ->assertViewHas('stats', [
                'users' => 1,
                'competitions' => 0,
                'submissions' => 0,
                'published_km' => 0,
                'open_competitions' => 0,
                'judging_competitions' => 0,
                'published_results' => 0,
                'draft_km' => 0,
            ])
            ->assertViewHas('roleStats', [
                'super_admin' => 1,
                'competition_admin' => 0,
                'judge' => 0,
            ])
            ->assertViewHas('recentCompetitions', fn ($items) => $items->isEmpty())
            ->assertViewHas('recentActivities', fn ($items) => $items->isEmpty())
            ->assertSee('ยังไม่มีข้อมูลการแข่งขัน')
            ->assertSee('ยังไม่มีกิจกรรมล่าสุด');
    }

    public function test_counts_use_all_database_records_and_role_names_including_inactive_users(): void
    {
        $this->createUser('Super Admin', false);
        $this->createUser('Judge');
        $this->createUser('Judge');
        $this->createUser('Judge', false);
        $this->createUser('Competition Admin');
        $this->createUser('Competition Admin', false);

        $first = $this->createCompetition();
        $second = $this->createCompetition(['status' => 'archived']);
        $this->createSubmission($first);
        $this->createSubmission($first, ['status' => 'disqualified']);
        $this->createSubmission($second, ['status' => 'draft']);

        $this->assertGreaterThan(3, $this->superAdmin->role_id);
        $this->assertNotSame('Super Admin', $this->superAdmin->role->display_name);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['users'] === 7
                && $stats['competitions'] === 2
                && $stats['submissions'] === 3)
            ->assertViewHas('roleStats', [
                'super_admin' => 2,
                'competition_admin' => 2,
                'judge' => 3,
            ]);
    }

    public function test_km_counts_follow_public_eligibility_and_existing_draft_status(): void
    {
        $competition = $this->createCompetition([
            'visibility' => 'private',
            'publish_km' => false,
        ]);

        $this->createKnowledgeItem(['status' => 'published', 'published_at' => null]);
        $this->createKnowledgeItem(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->createKnowledgeItem([
            'submission_id' => $this->createSubmission($competition)->id,
            'status' => 'published',
            'published_at' => null,
        ]);
        $this->createKnowledgeItem([
            'submission_id' => $this->createSubmission($competition, ['status' => 'draft'])->id,
            'status' => 'published',
        ]);
        $this->createKnowledgeItem([
            'submission_id' => $this->createSubmission($competition, ['status' => 'disqualified'])->id,
            'status' => 'published',
        ]);
        $this->createKnowledgeItem(['status' => 'draft']);
        $this->createKnowledgeItem([
            'submission_id' => $this->createSubmission($competition, ['status' => 'disqualified'])->id,
            'status' => 'draft',
        ]);
        $this->createKnowledgeItem(['status' => 'archived']);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['published_km'] === 4
                && $stats['draft_km'] === 2);
    }

    public function test_open_and_judging_counts_follow_time_boundaries_and_session_status(): void
    {
        foreach ([
            [now(), now()->addHour()],
            [now()->subHour(), now()->addHour()],
            [now()->addSecond(), now()->addHour()],
            [now()->subHour(), now()],
            [now()->subHour(), now()->subSecond()],
        ] as [$start, $end]) {
            $this->createCompetition([
                'status' => 'open',
                'registration_start' => $start,
                'registration_end' => $end,
            ]);
        }

        foreach (['draft', 'closed', 'judging', 'completed', 'archived'] as $status) {
            $this->createCompetition(['status' => $status]);
        }

        foreach (['live', 'paused', 'waiting', 'ended', 'closed'] as $status) {
            JudgingSession::create([
                'competition_id' => $this->createCompetition(['status' => 'closed'])->id,
                'status' => $status,
            ]);
        }

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['open_competitions'] === 2
                && $stats['judging_competitions'] === 2);
    }

    public function test_recent_competitions_are_limited_and_ordered_by_creation_then_id_with_submission_counts(): void
    {
        $competitions = [];
        foreach ([10, 5, 4, 3, 2, 1, 1] as $daysAgo) {
            $competition = $this->createCompetition();
            $competition->forceFill([
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now()->subDays($daysAgo),
            ])->save();
            $competitions[] = $competition;
        }
        $competitions[0]->forceFill(['updated_at' => now()->addDay()])->save();
        $this->createSubmission($competitions[6]);
        $this->createSubmission($competitions[6], ['status' => 'disqualified']);
        $competitions[6]->update([
            'status' => 'open',
            'registration_start' => now()->addHour(),
            'registration_end' => now()->addDay(),
        ]);

        $expectedIds = array_map(fn ($index) => $competitions[$index]->id, [6, 5, 4, 3, 2]);

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('recentCompetitions', function ($items) use ($expectedIds) {
                return $items->pluck('id')->all() === $expectedIds
                    && (int) $items[0]->submissions_count === 2
                    && (int) $items[1]->submissions_count === 0
                    && $items[0]->display_status === 'upcoming';
            })
            ->assertSee($competitions[6]->title)
            ->assertSee('upcoming')
            ->assertDontSee($competitions[0]->title);
    }

    public function test_recent_activities_use_stored_descriptions_and_creation_order_with_a_limit_of_three(): void
    {
        $logs = [];
        foreach ([5, 2, 1, 1, 10] as $daysAgo) {
            $log = ActivityLog::create([
                'module' => 'auth',
                'action' => 'success',
                'description' => 'Stored dashboard activity #'.++$this->sequence,
            ]);
            $log->forceFill([
                'created_at' => now()->subDays($daysAgo),
                'updated_at' => now(),
            ])->save();
            $logs[] = $log;
        }
        $expectedIds = [$logs[3]->id, $logs[2]->id, $logs[1]->id];

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('recentActivities', fn ($items) => $items->pluck('id')->all() === $expectedIds)
            ->assertSeeInOrder([$logs[3]->description, $logs[2]->description, $logs[1]->description])
            ->assertDontSee($logs[0]->description)
            ->assertDontSee($logs[4]->description);
    }

    public function test_published_results_include_ready_ended_and_closed_sessions_and_ignore_ineligible_extras(): void
    {
        $ended = $this->createReadyResult();
        $this->createSubmission($ended['competition'], ['status' => 'disqualified']);
        $this->createRubric($ended['competition'], false);
        $this->createAssignment($ended['competition'], 'pending');

        $closed = $this->createReadyResult();
        $closed['session']->update(['status' => 'closed']);
        $closed['competition']->update([
            'status' => 'draft',
            'visibility' => 'private',
            'result_announcement' => now()->addDay(),
        ]);
        $secondSubmission = $this->createSubmission($closed['competition']);
        $secondRubric = $this->createRubric($closed['competition']);
        $secondAssignment = $this->createAssignment($closed['competition']);
        foreach ([$closed['submission'], $secondSubmission] as $submission) {
            foreach ([$closed['rubric'], $secondRubric] as $rubric) {
                foreach ([$closed['assignment'], $secondAssignment] as $assignment) {
                    if ($submission->is($closed['submission'])
                        && $rubric->is($closed['rubric'])
                        && $assignment->is($closed['assignment'])) {
                        continue;
                    }

                    $this->createScore($submission, $rubric, $assignment);
                }
            }
        }

        $this->actingAs($this->superAdmin)
            ->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn ($stats) => $stats['published_results'] === 2);
    }

    public function test_published_results_exclude_missing_publication_requirements_and_incomplete_readiness(): void
    {
        $mutations = [
            'not published' => fn ($context) => $context['competition']->update(['publish_scores' => false]),
            'no announcement' => fn ($context) => $context['competition']->update(['result_announcement' => null]),
            'no session' => fn ($context) => $context['session']->delete(),
            'live session' => fn ($context) => $context['session']->update(['status' => 'live']),
            'paused session' => fn ($context) => $context['session']->update(['status' => 'paused']),
            'no active rubric' => fn ($context) => $context['rubric']->update(['is_active' => false]),
            'no accepted judge' => fn ($context) => $context['assignment']->update(['assignment_status' => 'declined']),
            'no submissions' => fn ($context) => $context['submission']->delete(),
            'only disqualified submissions' => fn ($context) => $context['submission']->update(['status' => 'disqualified']),
            'score not finalized' => fn ($context) => $context['score']->update(['submitted_at' => null]),
            'missing score' => fn ($context) => $context['score']->delete(),
            'extra unscored submission' => fn ($context) => $this->createSubmission($context['competition']),
            'inactive rubric scores cannot replace missing active scores' => function ($context) {
                $this->createRubric($context['competition']);
                $inactive = $this->createRubric($context['competition'], false);
                $this->createScore($context['submission'], $inactive, $context['assignment']);
            },
            'pending judge scores cannot replace missing accepted scores' => function ($context) {
                $this->createAssignment($context['competition']);
                $pending = $this->createAssignment($context['competition'], 'pending');
                $this->createScore($context['submission'], $context['rubric'], $pending);
            },
        ];

        foreach ($mutations as $reason => $mutate) {
            $context = $this->createReadyResult();
            $mutate($context);

            $this->actingAs($this->superAdmin)
                ->get(route('superadmin.dashboard'))
                ->assertOk()
                ->assertViewHas('stats', function ($stats) use ($reason) {
                    $this->assertSame(0, $stats['published_results'], $reason);

                    return true;
                });
        }
    }

    public function test_dashboard_query_count_does_not_grow_with_result_candidates(): void
    {
        $this->createReadyResult();
        $this->superAdmin->load(['role', 'adminProfile']);
        $this->actingAs($this->superAdmin)->get(route('superadmin.dashboard'))->assertOk();
        $initialQueryCount = $this->dashboardQueryCount(1);

        for ($index = 0; $index < 4; $index++) {
            $this->createReadyResult();
        }

        $expandedQueryCount = $this->dashboardQueryCount(5);

        $this->assertLessThanOrEqual(
            $initialQueryCount,
            $expandedQueryCount,
            'Adding result candidates must not add per-competition queries.'
        );
    }

    private function createUser(string $roleName, bool $active = true): User
    {
        $role = Role::firstOrCreate(
            ['role_name' => $roleName],
            ['display_name' => 'Localized '.$roleName]
        );
        $number = ++$this->sequence;

        return User::create([
            'role_id' => $role->id,
            'username' => 'dashboard-user-'.$number,
            'email' => 'dashboard-user-'.$number.'@example.com',
            'password' => 'password',
            'is_active' => $active,
        ]);
    }

    private function createCompetition(array $overrides = []): Competition
    {
        $category = CompetitionCategory::firstOrCreate(
            ['category_slug' => 'dashboard-category'],
            ['category_name' => 'Dashboard category', 'is_active' => true]
        );

        return Competition::create(array_replace([
            'category_id' => $category->id,
            'created_by' => $this->superAdmin->id,
            'title' => 'Dashboard competition #'.++$this->sequence,
            'competition_type' => 'individual',
            'visibility' => 'public',
            'registration_start' => now()->subHour(),
            'registration_end' => now()->addHour(),
            'publish_scores' => false,
            'publish_km' => false,
            'status' => 'draft',
        ], $overrides));
    }

    private function createSubmission(Competition $competition, array $overrides = []): Submission
    {
        $number = ++$this->sequence;

        return Submission::create(array_replace([
            'competition_id' => $competition->id,
            'submission_code' => 'DASH-'.$number,
            'project_title' => 'Dashboard project #'.$number,
            'contact_name' => 'Dashboard submitter',
            'contact_email' => 'submitter@example.com',
            'contact_phone' => '0800000000',
            'status' => 'submitted',
            'submitted_at' => now(),
        ], $overrides));
    }

    private function createKnowledgeItem(array $overrides): KnowledgeItem
    {
        return KnowledgeItem::create(array_replace([
            'created_by' => $this->superAdmin->id,
            'title' => 'Dashboard knowledge #'.++$this->sequence,
            'status' => 'draft',
        ], $overrides));
    }

    private function createRubric(Competition $competition, bool $active = true): Rubric
    {
        return Rubric::create([
            'competition_id' => $competition->id,
            'criteria_name' => 'Dashboard criterion #'.++$this->sequence,
            'max_score' => 10,
            'weight' => 100,
            'is_active' => $active,
        ]);
    }

    private function createAssignment(Competition $competition, string $status = 'accepted'): JudgeAssignment
    {
        return JudgeAssignment::create([
            'competition_id' => $competition->id,
            'judge_id' => $this->createUser('Judge')->id,
            'assignment_status' => $status,
            'accepted_at' => $status === 'accepted' ? now() : null,
        ]);
    }

    private function createScore(Submission $submission, Rubric $rubric, JudgeAssignment $assignment): Score
    {
        return Score::create([
            'submission_id' => $submission->id,
            'rubric_id' => $rubric->id,
            'judge_assignment_id' => $assignment->id,
            'score' => 0,
            'submitted_at' => now(),
        ]);
    }

    private function createReadyResult(): array
    {
        $competition = $this->createCompetition([
            'publish_scores' => true,
            'result_announcement' => now()->subDay(),
            'status' => 'closed',
        ]);
        $session = JudgingSession::create([
            'competition_id' => $competition->id,
            'status' => 'ended',
        ]);
        $submission = $this->createSubmission($competition, ['final_score' => null]);
        $rubric = $this->createRubric($competition);
        $assignment = $this->createAssignment($competition);
        $score = $this->createScore($submission, $rubric, $assignment);

        return compact('competition', 'session', 'submission', 'rubric', 'assignment', 'score');
    }

    private function dashboardQueryCount(int $expectedPublishedResults): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $this->get(route('superadmin.dashboard'))
                ->assertOk()
                ->assertViewHas('stats', fn ($stats) => $stats['published_results'] === $expectedPublishedResults);

            return count(array_filter(
                DB::getQueryLog(),
                fn ($query) => preg_match('/^\s*select\b/i', $query['query']) === 1
            ));
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }
}
