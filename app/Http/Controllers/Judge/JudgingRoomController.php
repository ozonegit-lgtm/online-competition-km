<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Rubric;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JudgingRoomController extends Controller
{
    public function index(): View
    {
        $rooms = JudgingSession::query()
            ->whereHas(
                'competition.judgeAssignments',
                function ($query) {
                    $query->where('judge_id', Auth::id());
                }
            )
            ->with([
                'competition' => function ($query) {
                    $query
                        ->with('template')
                        ->withCount([
                            'submissions',
                            'rubrics',
                        ]);
                },
                'competition.judgeAssignments' => function ($query) {
                    $query->where('judge_id', Auth::id());
                },
            ])
            ->latest()
            ->paginate(10);

        return view('judge.judging-rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    public function show(JudgingSession $session): View
    {
        $assignment = $this->getAcceptedAssignment($session);

        $session->load([
            'competition.rubrics' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            },
            'currentSubmission.files',
            'currentFile',
        ]);

        $scores = collect();

        if ($session->current_submission_id) {
            $scores = Score::query()
                ->where('judge_assignment_id', $assignment->id)
                ->where(
                    'submission_id',
                    $session->current_submission_id
                )
                ->get()
                ->keyBy('rubric_id');
        }

        return view('judge.judging-rooms.show', [
            'session' => $session,
            'assignment' => $assignment,
            'competition' => $session->competition,
            'submission' => $session->currentSubmission,
            'currentFile' => $session->currentFile,
            'rubrics' => $session->competition->rubrics,
            'scores' => $scores,
        ]);
    }

    public function state(
        JudgingSession $session
    ): JsonResponse {
        $this->getAcceptedAssignment($session);

        $session->refresh();

        return response()->json([
            'id' => $session->id,
            'status' => $session->status,
            'state_version' => $session->state_version,
            'current_submission_id' =>
                $session->current_submission_id,
            'current_file_id' =>
                $session->current_file_id,
            'current_page' => $session->current_page,
            'scroll_progress' =>
                (float) $session->scroll_progress,
            'zoom' => (float) $session->zoom,
            'started_at' =>
                $session->started_at?->toISOString(),
            'ended_at' =>
                $session->ended_at?->toISOString(),
        ]);
    }

    public function saveDraft(
        Request $request,
        JudgingSession $session
    ): RedirectResponse {
        $assignment = $this->getAcceptedAssignment($session);

        $this->ensureSessionCanBeScored($session);

        $rubrics = $this->getActiveRubrics($session);

        $validated = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*.score' => ['required', 'numeric', 'min:0'],
            'scores.*.comment' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->validateSubmittedRubrics(
            $validated['scores'],
            $rubrics
        );

        DB::transaction(function () use (
            $validated,
            $rubrics,
            $assignment,
            $session
        ) {
            foreach ($rubrics as $rubric) {
                $input = $validated['scores'][$rubric->id];

                Score::query()->updateOrCreate(
                    [
                        'submission_id' =>
                            $session->current_submission_id,
                        'rubric_id' => $rubric->id,
                        'judge_assignment_id' =>
                            $assignment->id,
                    ],
                    [
                        'score' => $input['score'],
                        'comment' =>
                            $input['comment'] ?? null,
                        'submitted_at' => null,
                    ]
                );
            }
        });

        return back()->with(
            'success',
            'บันทึกร่างคะแนนเรียบร้อยแล้ว'
        );
    }

    public function submit(
        JudgingSession $session
    ): RedirectResponse {
        $assignment = $this->getAcceptedAssignment($session);

        $this->ensureSessionCanBeScored($session);

        $rubrics = $this->getActiveRubrics($session);

        $scores = Score::query()
            ->where('judge_assignment_id', $assignment->id)
            ->where(
                'submission_id',
                $session->current_submission_id
            )
            ->whereIn('rubric_id', $rubrics->modelKeys())
            ->get();

        if ($scores->count() !== $rubrics->count()) {
            return back()->with(
                'error',
                'กรุณาบันทึกร่างคะแนนให้ครบทุกเกณฑ์ก่อนยืนยัน'
            );
        }

        if ($scores->every(
            fn (Score $score) => $score->submitted_at !== null
        )) {
            return back()->with(
                'success',
                'คะแนนของผลงานนี้ถูกยืนยันแล้ว'
            );
        }

        DB::transaction(function () use ($scores) {
            $submittedAt = now();

            foreach ($scores as $score) {
                $score->update([
                    'submitted_at' => $submittedAt,
                ]);
            }
        });

        return back()->with(
            'success',
            'ยืนยันส่งคะแนนเรียบร้อยแล้ว'
        );
    }

    private function getAssignment(
        JudgingSession $session
    ): JudgeAssignment {
        return JudgeAssignment::query()
            ->where(
                'competition_id',
                $session->competition_id
            )
            ->where('judge_id', Auth::id())
            ->firstOrFail();
    }

    private function getAcceptedAssignment(
        JudgingSession $session
    ): JudgeAssignment {
        $assignment = $this->getAssignment($session);

        abort_unless(
            $assignment->assignment_status === 'accepted',
            403,
            'กรุณารับงานตัดสินก่อนเข้าห้อง'
        );

        return $assignment;
    }

    private function ensureSessionCanBeScored(
        JudgingSession $session
    ): void {
        $session->refresh();

        abort_unless(
            $session->isLive(),
            422,
            'บันทึกคะแนนได้เฉพาะขณะที่ห้องกำลัง Live'
        );

        abort_unless(
            $session->current_submission_id !== null,
            422,
            'ยังไม่ได้เลือกผลงานสำหรับให้คะแนน'
        );

        abort_unless(
            $session->currentSubmission()
                ->where(
                    'competition_id',
                    $session->competition_id
                )
                ->exists(),
            422,
            'ผลงานไม่อยู่ในการแข่งขันนี้'
        );
    }

    private function getActiveRubrics(
        JudgingSession $session
    ) {
        $rubrics = Rubric::query()
            ->where(
                'competition_id',
                $session->competition_id
            )
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        abort_if(
            $rubrics->isEmpty(),
            422,
            'การแข่งขันนี้ยังไม่มีเกณฑ์ให้คะแนน'
        );

        return $rubrics;
    }

    private function validateSubmittedRubrics(
        array $submittedScores,
        $rubrics
    ): void {
        $submittedIds = collect(array_keys($submittedScores))
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        $rubricIds = $rubrics
            ->modelKeys()
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        if ($submittedIds->all() !== $rubricIds->all()) {
            throw ValidationException::withMessages([
                'scores' =>
                    'ข้อมูลเกณฑ์การให้คะแนนไม่ถูกต้อง',
            ]);
        }

        foreach ($rubrics as $rubric) {
            $score = (float) $submittedScores[
                $rubric->id
            ]['score'];

            if ($score > (float) $rubric->max_score) {
                throw ValidationException::withMessages([
                    "scores.{$rubric->id}.score" =>
                        "คะแนนต้องไม่เกิน {$rubric->max_score}",
                ]);
            }
        }
    }
}