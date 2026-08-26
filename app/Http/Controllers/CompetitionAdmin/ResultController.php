<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;


class ResultController extends Controller
{
        /**
     * ศูนย์รวมผลการแข่งขันของ Competition Admin
     */
    public function competitions(): View
    {
        $competitions = Competition::query()
            ->where('created_by', Auth::id())
            ->with([
                'category:id,category_name',

                'judgingSession:id,competition_id,status',

                'rubrics' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->select([
                            'id',
                            'competition_id',
                        ]);
                },

                'judgeAssignments' => function ($query) {
                    $query
                        ->where(
                            'assignment_status',
                            'accepted'
                        )
                        ->select([
                            'id',
                            'competition_id',
                        ]);
                },

                'submissions' => function ($query) {
                    $query
                        ->where(
                            'status',
                            '!=',
                            'disqualified'
                        )
                        ->select([
                            'id',
                            'competition_id',
                            'status',
                            'final_score',
                        ]);
                },
            ])
            ->latest()
            ->paginate(12);

        /*
         * เตรียมสถานะของแต่ละการแข่งขัน
         * สำหรับแสดงในหน้าศูนย์ผลการแข่งขัน
         */
        $competitions
            ->getCollection()
            ->transform(function (Competition $competition) {
                $session = $competition->judgingSession;

                $sessionFinished =
                    $session !== null &&
                    in_array(
                        $session->status,
                        [
                            'ended',
                            'closed',
                        ],
                        true
                    );

                $activeRubricCount =
                    $competition->rubrics->count();

                $acceptedJudgeCount =
                    $competition->judgeAssignments->count();

                $totalSubmissions =
                    $competition->submissions->count();

                /*
                 * final_score จะมีค่าเมื่อกรรมการ accepted
                 * ส่งคะแนน active Rubric ครบแล้ว
                 */
                $completedSubmissionCount =
                    $competition->submissions
                        ->filter(
                            fn ($submission) =>
                                $submission->final_score !== null
                        )
                        ->count();

                $isReadyForResults =
                    $sessionFinished &&
                    $activeRubricCount > 0 &&
                    $acceptedJudgeCount > 0 &&
                    $totalSubmissions > 0 &&
                    $completedSubmissionCount ===
                        $totalSubmissions;

                $competition->setAttribute(
                    'results_session_finished',
                    $sessionFinished
                );

                $competition->setAttribute(
                    'results_active_rubric_count',
                    $activeRubricCount
                );

                $competition->setAttribute(
                    'results_accepted_judge_count',
                    $acceptedJudgeCount
                );

                $competition->setAttribute(
                    'results_total_submissions',
                    $totalSubmissions
                );

                $competition->setAttribute(
                    'results_completed_submissions',
                    $completedSubmissionCount
                );

                $competition->setAttribute(
                    'results_ready',
                    $isReadyForResults
                );

                return $competition;
            });

        return view(
            'competition-admin.results.index',
            compact('competitions')
        );
    }
    /**
     * แสดงผลการแข่งขัน
     */
    public function index(Competition $competition)
    {
        /*
        |--------------------------------------------------------------------------
        | ตรวจสอบสิทธิ์
        |--------------------------------------------------------------------------
        */
        abort_unless(
            (int) $competition->created_by === (int) Auth::id(),
            403,
            'คุณไม่มีสิทธิ์เข้าถึงผลการแข่งขันนี้'
        );

        /*
        |--------------------------------------------------------------------------
        | ห้องตัดสิน
        |--------------------------------------------------------------------------
        */
        $session = $competition->judgingSession;

        $sessionFinished = $session
            && in_array(
                $session->status,
                ['ended', 'closed'],
                true
            );

        /*
        |--------------------------------------------------------------------------
        | Rubric ที่เปิดใช้งาน
        |--------------------------------------------------------------------------
        */
        $activeRubricIds = $competition->rubrics()
            ->where('is_active', true)
            ->pluck('id');

        $activeRubricCount = $activeRubricIds->count();

        /*
        |--------------------------------------------------------------------------
        | กรรมการที่ตอบรับ
        |--------------------------------------------------------------------------
        */
        $acceptedAssignmentIds = $competition->judgeAssignments()
            ->where('assignment_status', 'accepted')
            ->pluck('id');

        $acceptedJudgeCount = $acceptedAssignmentIds->count();

        /*
        |--------------------------------------------------------------------------
        | จำนวน Score ที่ต้องมีต่อหนึ่ง Submission
        |--------------------------------------------------------------------------
        |
        | ตัวอย่าง:
        | Rubric 3 ข้อ × กรรมการ 2 คน
        | เท่ากับต้องมีคะแนนที่ยืนยันแล้ว 6 รายการต่อผลงาน
        |
        */
        $expectedScoreCount =
            $activeRubricCount * $acceptedJudgeCount;

        /*
        |--------------------------------------------------------------------------
        | โหลด Submission
        |--------------------------------------------------------------------------
        |
        | ไม่นำผลงานที่ถูกตัดสิทธิ์มาคำนวณและจัดอันดับ
        |
        */
        $submissions = $competition->submissions()
            ->where('status', '!=', 'disqualified')
            ->with([
                    'files' => function ($query) {
                        $query
                            ->orderByDesc('is_primary')
                            ->orderBy('id');
                    },
                ])
            ->withCount([
                'scores as submitted_scores_count' => function ($query) use (
                    $activeRubricIds,
                    $acceptedAssignmentIds
                ) {
                    $query->whereNotNull('submitted_at');

                    if ($activeRubricIds->isNotEmpty()) {
                        $query->whereIn(
                            'rubric_id',
                            $activeRubricIds
                        );
                    }

                    if ($acceptedAssignmentIds->isNotEmpty()) {
                        $query->whereIn(
                            'judge_assignment_id',
                            $acceptedAssignmentIds
                        );
                    }
                },
            ])
            ->orderByDesc('final_score')
            ->orderBy('submitted_at')
            ->get();

        $totalSubmissions = $submissions->count();

        /*
        |--------------------------------------------------------------------------
        | ผลงานที่กรรมการส่งคะแนนครบ
        |--------------------------------------------------------------------------
        */
        $completedSubmissions = $submissions
            ->filter(function ($submission) use ($expectedScoreCount) {
                if ($expectedScoreCount <= 0) {
                    return false;
                }

                return (int) $submission->submitted_scores_count
                    >= $expectedScoreCount;
            })
            ->values();

        $completedSubmissionCount =
            $completedSubmissions->count();

        /*
        |--------------------------------------------------------------------------
        | ผลงานที่คะแนนยังไม่ครบ
        |--------------------------------------------------------------------------
        */
        $pendingSubmissions = $submissions
            ->reject(function ($submission) use ($expectedScoreCount) {
                if ($expectedScoreCount <= 0) {
                    return false;
                }

                return (int) $submission->submitted_scores_count
                    >= $expectedScoreCount;
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | จัดอันดับ
        |--------------------------------------------------------------------------
        |
        | จะแสดงอันดับเมื่อห้องตัดสินเป็น ended หรือ closed เท่านั้น
        |
        | กรณีคะแนนเท่ากัน:
        | 95 คะแนน = อันดับ 1
        | 95 คะแนน = อันดับ 1
        | 90 คะแนน = อันดับ 3
        |
        */
        if ($sessionFinished) {
            $lastScore = null;
            $lastRank = 0;

            $rankedSubmissions = $completedSubmissions
                ->sortByDesc(
                    fn ($submission) =>
                        (float) $submission->final_score
                )
                ->values()
                ->map(function ($submission, $index) use (
                    &$lastScore,
                    &$lastRank
                ) {
                    $currentScore = (int) round(
                        (float) $submission->final_score * 100
                    );

                    if (
                        $lastScore === null ||
                        $currentScore !== $lastScore
                    ) {
                        $lastRank = $index + 1;
                        $lastScore = $currentScore;
                    }

                    $submission->setAttribute(
                        'rank',
                        $lastRank
                    );

                    return $submission;
                });
        } else {
            $rankedSubmissions = collect();
        }
        /*
        * แสดงทุกผลงานที่มีอันดับไม่เกิน 3
        * จึงรองรับกรณีอันดับร่วมด้วย
        */
        $rankedSubmissions = $rankedSubmissions
            ->filter(
                fn ($submission) =>
                    (int) $submission->rank <= 3
            )
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ตรวจว่าผลการแข่งขันพร้อมหรือยัง
        |--------------------------------------------------------------------------
        */
        $isReadyForResults =
            $sessionFinished
            && $activeRubricCount > 0
            && $acceptedJudgeCount > 0
            && $totalSubmissions > 0
            && $completedSubmissionCount === $totalSubmissions;

        return view(
            'competition-admin.competitions.results.index',
            [
                'competition' => $competition,
                'session' => $session,

                'sessionFinished' => $sessionFinished,
                'isReadyForResults' => $isReadyForResults,

                'activeRubricCount' => $activeRubricCount,
                'acceptedJudgeCount' => $acceptedJudgeCount,
                'expectedScoreCount' => $expectedScoreCount,

                'totalSubmissions' => $totalSubmissions,
                'completedSubmissionCount' =>
                    $completedSubmissionCount,

                'rankedSubmissions' => $rankedSubmissions,
                'pendingSubmissions' => $pendingSubmissions,
            ]
        );
    }

    public function publish(Competition $competition): JsonResponse|RedirectResponse
    {
        abort_unless(
            (int) $competition->created_by === (int) Auth::id(),
            403
        );

        if (! $this->resultsArePublishable($competition)) {
            $message =
                'ยังเผยแพร่ผลไม่ได้ ต้องปิดห้องตัดสินและมีคะแนนครบทุกผลงาน';

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return back()->withErrors([
                'results' => $message,
            ]);
        }

    $competition->update([
        'publish_scores' => true,
        'result_announcement' => now(),
    ]);

    return back()->with(
        'success',
        'แสดงผลงานอันดับ 1–3 บนหน้าเว็บ KM แล้ว'
    );
}

public function unpublish(
    Competition $competition
): RedirectResponse {
    abort_unless(
        (int) $competition->created_by === (int) Auth::id(),
        403
    );

    $competition->update([
        'publish_scores' => false,
    ]);

    return back()->with(
        'success',
        'ซ่อนผลการแข่งขันจากหน้าเว็บ KM แล้ว'
    );
}

    private function resultsArePublishable(
        Competition $competition
    ): bool {
        $session = $competition->judgingSession()->first();

        if (
            ! $session ||
            ! in_array($session->status, ['ended', 'closed'], true)
        ) {
            return false;
        }

        $rubricIds = $competition->rubrics()
            ->where('is_active', true)
            ->pluck('id');

        $assignmentIds = $competition->judgeAssignments()
            ->where('assignment_status', 'accepted')
            ->pluck('id');

        if ($rubricIds->isEmpty() || $assignmentIds->isEmpty()) {
            return false;
        }

        $expectedScoreCount =
            $rubricIds->count() * $assignmentIds->count();

        $submissions = $competition->submissions()
            ->where('status', '!=', 'disqualified')
            ->withCount([
                'scores as confirmed_scores_count' =>
                    function ($query) use (
                        $rubricIds,
                        $assignmentIds
                    ) {
                        $query
                            ->whereNotNull('submitted_at')
                            ->whereIn('rubric_id', $rubricIds)
                            ->whereIn(
                                'judge_assignment_id',
                                $assignmentIds
                            );
                    },
            ])
            ->get();

        return $submissions->isNotEmpty()
            && $submissions->every(
                fn ($submission) =>
                    (int) $submission->confirmed_scores_count
                    >= $expectedScoreCount
            );
    }
}