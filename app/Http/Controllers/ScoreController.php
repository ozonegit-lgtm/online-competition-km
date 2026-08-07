<?php

namespace App\Http\Controllers;

use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Score;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ScoreController extends Controller
{
    /**
     * บันทึกร่างคะแนนของผลงานปัจจุบัน
     */
    public function saveDraft(
        Request $request,
        JudgingSession $session
    ): RedirectResponse {
        $assignment = $this->getAssignment($session);

        $this->ensureRoomIsLive($session);

        abort_unless(
            $session->current_submission_id,
            422,
            'ยังไม่มีผลงานที่กำลังตัดสิน'
        );

        $validated = $request->validate([
            'scores' => [
                'required',
                'array',
                'min:1',
            ],
            'scores.*.score' => [
                'required',
                'numeric',
                'min:0',
            ],
            'scores.*.comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $rubrics = $session
            ->competition
            ->rubrics()
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        DB::transaction(function () use (
            $validated,
            $rubrics,
            $session,
            $assignment
        ) {
            foreach ($validated['scores'] as $rubricId => $data) {
                $rubric = $rubrics->get((int) $rubricId);

                abort_unless(
                    $rubric,
                    422,
                    'พบเกณฑ์การให้คะแนนที่ไม่ถูกต้อง'
                );

                Validator::make(
                    [
                        'score' => $data['score'],
                    ],
                    [
                        'score' => [
                            'required',
                            'numeric',
                            'min:0',
                            'max:' . $rubric->max_score,
                        ],
                    ],
                    [
                        'score.max' =>
                            "คะแนน {$rubric->criteria_name} ต้องไม่เกิน {$rubric->max_score}",
                    ]
                )->validate();

                $existingScore = Score::query()
                    ->where(
                        'submission_id',
                        $session->current_submission_id
                    )
                    ->where('rubric_id', $rubric->id)
                    ->where(
                        'judge_assignment_id',
                        $assignment->id
                    )
                    ->first();

                if ($existingScore?->submitted_at) {
                    abort(
                        422,
                        'คะแนนของผลงานนี้ถูกยืนยันแล้ว'
                    );
                }

                Score::updateOrCreate(
                    [
                        'submission_id' =>
                            $session->current_submission_id,
                        'rubric_id' => $rubric->id,
                        'judge_assignment_id' =>
                            $assignment->id,
                    ],
                    [
                        'score' => $data['score'],
                        'comment' =>
                            $data['comment'] ?? null,
                        'submitted_at' => null,
                    ]
                );
            }

            // กดบันทึกครั้งแรกถือว่าได้รับงานตัดสิน
        });

        return back()->with(
            'success',
            'บันทึกร่างคะแนนแล้ว'
        );
    }

    /**
     * ยืนยันคะแนนของผลงานปัจจุบัน
     */
    public function submit(
        Request $request,
        JudgingSession $session
    ): RedirectResponse {
        $assignment = $this->getAssignment($session);

        $this->ensureRoomIsLive($session);

        abort_unless(
            $session->current_submission_id,
            422,
            'ยังไม่มีผลงานที่กำลังตัดสิน'
        );

        $rubrics = $session
            ->competition
            ->rubrics()
            ->where('is_active', true)
            ->get();

        if ($rubrics->isEmpty()) {
            return back()->with(
                'error',
                'การแข่งขันนี้ยังไม่มีเกณฑ์การให้คะแนน'
            );
        }

        $scores = Score::query()
            ->where(
                'submission_id',
                $session->current_submission_id
            )
            ->where(
                'judge_assignment_id',
                $assignment->id
            )
            ->whereIn('rubric_id', $rubrics->pluck('id'))
            ->get();

        if ($scores->count() !== $rubrics->count()) {
            return back()->with(
                'error',
                'กรุณากรอกและบันทึกคะแนนให้ครบทุกเกณฑ์ก่อนยืนยัน'
            );
        }

        if ($scores->contains(
            fn ($score) => $score->submitted_at !== null
        )) {
            return back()->with(
                'error',
                'คะแนนของผลงานนี้ถูกยืนยันแล้ว'
            );
        }

        DB::transaction(function () use (
            $scores,
            $assignment,
            $session,
            $rubrics
        ) {
            $submittedAt = now();

            Score::query()
                ->whereKey($scores->pluck('id'))
                ->update([
                    'submitted_at' => $submittedAt,
                ]);


            /*
             * assignment.submitted_at หมายถึงกรรมการ
             * ส่งคะแนนครบทุกผลงานของการแข่งขันแล้ว
             */
            $submissionCount = $session
                ->competition
                ->submissions()
                ->whereIn('status', [
                    'submitted',
                    'under_review',
                    'completed',
                ])
                ->count();

            $expectedScoreCount =
                $submissionCount * $rubrics->count();

            $submittedScoreCount = Score::query()
                ->where(
                    'judge_assignment_id',
                    $assignment->id
                )
                ->whereNotNull('submitted_at')
                ->count();

            if (
                $expectedScoreCount > 0 &&
                $submittedScoreCount >= $expectedScoreCount
            ) {
                $assignment->update([
                    'submitted_at' => $submittedAt,
                ]);
            }
        });

        return back()->with(
            'success',
            'ยืนยันคะแนนเรียบร้อยแล้ว'
        );
    }

    /**
     * ดึง Assignment ของกรรมการปัจจุบัน
     */
    private function getAssignment(
        JudgingSession $session
    ): JudgeAssignment {
        $assignment = JudgeAssignment::query()
            ->where(
                'competition_id',
                $session->competition_id
            )
            ->where(
                'judge_id',
                Auth::id()
            )
            ->firstOrFail();

        abort_unless(
            $assignment->assignment_status === 'accepted',
            403,
            'กรุณารับงานตัดสินก่อนให้คะแนน'
        );

        abort_if(
            $assignment->assignment_status === 'declined',
            403,
            'คุณได้ปฏิเสธงานตัดสินนี้แล้ว'
        );

        return $assignment;
    }

    /**
     * อนุญาตให้บันทึกคะแนนเฉพาะตอน Live
     */
    private function ensureRoomIsLive(
        JudgingSession $session
    ): void {
        abort_unless(
            $session->isLive(),
            422,
            'สามารถบันทึกคะแนนได้เฉพาะตอนห้องกำลัง Live'
        );
    }
}