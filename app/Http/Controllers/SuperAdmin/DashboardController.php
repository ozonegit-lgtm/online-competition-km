<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Competition;
use App\Models\JudgingSession;
use App\Models\KnowledgeItem;
use App\Models\Role;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = now();

        $stats = [
            'users' => User::query()->count(),
            'competitions' => Competition::query()->count(),
            'submissions' => Submission::query()->count(),
            // Keep the same eligibility as the public KM listing.
            'published_km' => KnowledgeItem::query()
                ->legacy()
                ->where('status', 'published')
                ->where(function (Builder $query) {
                    $query->whereNull('submission_id')
                        ->orWhereHas('submission', fn (Builder $query) => $query
                            ->where('status', '!=', 'disqualified'));
                })
                ->count(),
            // SQL equivalent of Competition::isRegistrationOpen().
            'open_competitions' => Competition::query()
                ->where('status', 'open')
                ->where(fn (Builder $query) => $query
                    ->whereNull('registration_start')
                    ->orWhere('registration_start', '<=', $now))
                ->where(fn (Builder $query) => $query
                    ->whereNull('registration_end')
                    ->orWhere('registration_end', '>', $now))
                ->count(),
            'judging_competitions' => Competition::query()
                ->whereHas('judgingSession', fn (Builder $query) => $query
                    ->whereIn('status', [JudgingSession::STATUS_LIVE, JudgingSession::STATUS_PAUSED]))
                ->count(),
            'published_results' => $this->publishedResultsCount(),
            'draft_km' => KnowledgeItem::query()->legacy()->where('status', 'draft')->count(),
        ];

        $roleCounts = Role::query()
            ->whereIn('role_name', ['Super Admin', 'Competition Admin', 'Judge'])
            ->withCount('users')
            ->get(['id', 'role_name'])
            ->pluck('users_count', 'role_name');

        $roleStats = [
            'super_admin' => (int) $roleCounts->get('Super Admin', 0),
            'competition_admin' => (int) $roleCounts->get('Competition Admin', 0),
            'judge' => (int) $roleCounts->get('Judge', 0),
        ];

        $recentCompetitions = Competition::query()
            ->withCount('submissions')
            ->latest('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentActivities = ActivityLog::query()
            ->latest('created_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get(['id', 'description', 'created_at']);

        return view('superadmin.dashboard', compact(
            'stats',
            'roleStats',
            'recentCompetitions',
            'recentActivities',
        ));
    }

    private function publishedResultsCount(): int
    {
        // Match public result eligibility and Competition::resultReadiness()
        // in one count query, without loading every competition's scores.
        return Competition::query()
            ->where('publish_scores', true)
            ->whereNotNull('result_announcement')
            ->whereHas('judgingSession', fn (Builder $query) => $query
                ->whereIn('status', [JudgingSession::STATUS_ENDED, JudgingSession::STATUS_CLOSED]))
            ->whereHas('rubrics', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('judgeAssignments', fn (Builder $query) => $query
                ->where('assignment_status', 'accepted'))
            ->whereHas('submissions', fn (Builder $query) => $query
                ->where('status', '!=', 'disqualified'))
            ->whereDoesntHave('submissions', function (Builder $query) {
                $query->where('status', '!=', 'disqualified')
                    ->whereRaw(<<<'SQL'
                        (
                            SELECT COUNT(*)
                            FROM scores
                            INNER JOIN rubrics ON rubrics.id = scores.rubric_id
                            INNER JOIN judge_assignments
                                ON judge_assignments.id = scores.judge_assignment_id
                            WHERE scores.submission_id = submissions.id
                                AND scores.submitted_at IS NOT NULL
                                AND rubrics.competition_id = submissions.competition_id
                                AND rubrics.is_active = ?
                                AND judge_assignments.competition_id = submissions.competition_id
                                AND judge_assignments.assignment_status = ?
                        ) < (
                            (
                                SELECT COUNT(*) FROM rubrics
                                WHERE rubrics.competition_id = submissions.competition_id
                                    AND rubrics.is_active = ?
                            ) * (
                                SELECT COUNT(*) FROM judge_assignments
                                WHERE judge_assignments.competition_id = submissions.competition_id
                                    AND judge_assignments.assignment_status = ?
                            )
                        )
                        SQL, [true, 'accepted', true, 'accepted']);
            })
            ->count();
    }
}
