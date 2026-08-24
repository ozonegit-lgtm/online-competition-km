<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
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
                'knowledgeItem',
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
                    $currentScore = round(
                        (float) $submission->final_score,
                        2
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
}