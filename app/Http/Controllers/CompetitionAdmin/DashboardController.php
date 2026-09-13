<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\KnowledgeItem;
use App\Models\Submission;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Base Queries
        |--------------------------------------------------------------------------
        | ทุกข้อมูลต้องผ่านการแข่งขันที่ Competition Admin คนนี้เป็นเจ้าของ
        */

        $competitionBase = Competition::query()
            ->where('created_by', $userId);

        $submissionBase = Submission::query()
            ->whereHas('competition', function ($query) use ($userId) {
                $query->where('created_by', $userId);
            });

        $assignmentBase = JudgeAssignment::query()
            ->whereHas('competition', function ($query) use ($userId) {
                $query->where('created_by', $userId);
            });

        $sessionBase = JudgingSession::query()
            ->whereHas('competition', function ($query) use ($userId) {
                $query->where('created_by', $userId);
            });

        /*
        |--------------------------------------------------------------------------
        | Competition Stats
        |--------------------------------------------------------------------------
        */

        $totalCompetitions = (clone $competitionBase)->count();

        $openCompetitions = (clone $competitionBase)
            ->where('status', 'open')
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('registration_start')
                    ->orWhere('registration_start', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('registration_end')
                    ->orWhere('registration_end', '>', $now);
            })
            ->count();

        $totalSubmissions = (clone $submissionBase)->count();

        $disqualifiedSubmissions = (clone $submissionBase)
            ->where('status', 'disqualified')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Judge Stats
        |--------------------------------------------------------------------------
        */

        $acceptedJudges = (clone $assignmentBase)
            ->where('assignment_status', 'accepted')
            ->count();

        $pendingJudges = (clone $assignmentBase)
            ->where('assignment_status', 'pending')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Judging Session Stats
        |--------------------------------------------------------------------------
        */

        $activeJudgingSessions = (clone $sessionBase)
            ->whereIn('status', [
                JudgingSession::STATUS_LIVE,
                JudgingSession::STATUS_PAUSED,
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Result Stats
        |--------------------------------------------------------------------------
        */

        $publishedResults = (clone $competitionBase)
            ->where('publish_scores', true)
            ->count();

        $waitingResults = (clone $competitionBase)
            ->where('publish_scores', false)
            ->whereHas('judgingSession', function ($query) {
                $query->whereIn('status', [
                    JudgingSession::STATUS_ENDED,
                    JudgingSession::STATUS_CLOSED,
                ]);
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Knowledge Management Stats
        |--------------------------------------------------------------------------
        |
        | 1. KM ที่ Admin เพิ่มเอง
        | 2. KM ที่มาจาก Submission ของการแข่งขันที่ Admin เป็นเจ้าของ
        */

        $knowledgeBase = KnowledgeItem::query()
            ->where(function ($query) use ($userId) {
                $query
                    ->where(function ($manual) use ($userId) {
                        $manual
                            ->where('created_by', $userId)
                            ->whereNull('submission_id');
                    })
                    ->orWhereHas(
                        'submission.competition',
                        function ($competition) use ($userId) {
                            $competition->where('created_by', $userId);
                        }
                    );
            });

        $totalKnowledgeItems = (clone $knowledgeBase)->count();

        $publishedKnowledgeItems = (clone $knowledgeBase)
            ->where('status', 'published')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Competitions
        |--------------------------------------------------------------------------
        */

        $recentCompetitions = Competition::query()
            ->where('created_by', $userId)
            ->with([
                'category:id,category_name',
                'judgingSession',
            ])
            ->withCount([
                'submissions',
                'judgeAssignments',
                'judgeAssignments as accepted_judges_count' => function ($query) {
                    $query->where('assignment_status', 'accepted');
                },
            ])
            ->latest()
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Submissions
        |--------------------------------------------------------------------------
        */

        $recentSubmissions = Submission::query()
            ->whereHas('competition', function ($query) use ($userId) {
                $query->where('created_by', $userId);
            })
            ->with([
                'competition:id,title,created_by',
            ])
            ->latest('submitted_at')
            ->limit(6)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Stats
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_competitions' => $totalCompetitions,
            'open_competitions' => $openCompetitions,
            'total_submissions' => $totalSubmissions,
            'disqualified_submissions' => $disqualifiedSubmissions,

            'accepted_judges' => $acceptedJudges,
            'pending_judges' => $pendingJudges,

            'active_judging_sessions' => $activeJudgingSessions,

            'published_results' => $publishedResults,
            'waiting_results' => $waitingResults,

            'total_knowledge_items' => $totalKnowledgeItems,
            'published_knowledge_items' => $publishedKnowledgeItems,
        ];

        return view(
            'competition-admin.dashboard',
            compact(
                'stats',
                'recentCompetitions',
                'recentSubmissions'
            )
        );
    }

    public function submissions(): View
    {
        $submissions = Submission::query()
            ->with([
                'competition.category',
                'files',
            ])
            ->whereHas('competition', function ($query) {
                $query->where('created_by', auth()->id());
            })
            ->latest('submitted_at')
            ->paginate(12);

        return view(
            'competition-admin.submissions.index',
            compact('submissions')
        );
    }
}