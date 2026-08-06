<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Score;
use Illuminate\View\View;

class JudgingRoomController extends Controller
{
    /**
     * รายการห้องตัดสินที่กรรมการได้รับมอบหมาย
     */
    public function index(): View
    {
        $rooms = JudgingSession::query()
            ->whereHas(
                'competition.judgeAssignments',
                function ($query) {
                    $query->where(
                        'judge_id',
                        auth()->id()
                    );
                }
            )
            ->with([
                'competition' => function ($query) {
                    $query->withCount([
                        'submissions',
                        'rubrics',
                    ]);
                },
                'competition.judgeAssignments' => function ($query) {
                    $query->where(
                        'judge_id',
                        auth()->id()
                    );
                },
            ])
            ->latest()
            ->paginate(10);

        return view('judge.judging-rooms.index', [
            'rooms' => $rooms,
        ]);
    }

    /**
     * แสดงห้องตัดสิน
     */
    public function show(JudgingSession $session): View
    {
        $assignment = JudgeAssignment::query()
            ->where('competition_id', $session->competition_id)
            ->where('judge_id', auth()->id())
            ->firstOrFail();

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
                ->where(
                    'judge_assignment_id',
                    $assignment->id
                )
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
}