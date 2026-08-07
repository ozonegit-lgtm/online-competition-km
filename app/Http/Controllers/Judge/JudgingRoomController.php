<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use App\Models\JudgeAssignment;
use App\Models\JudgingSession;
use App\Models\Score;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class JudgingRoomController extends Controller
{
    /**
     * แสดงห้องที่กรรมการได้รับมอบหมาย
     */
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
                    $query->withCount([
                        'submissions',
                        'rubrics',
                    ]);
                },
                'competition.judgeAssignments' => function ($query) {
                    $query->where(
                        'judge_id',
                        Auth::id()
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
     * แสดงรายละเอียดห้องและแบบให้คะแนน
     */
    public function show(
        JudgingSession $session
    ): View {
        $assignment = $this->getAssignment($session);
        abort_unless(
            $assignment->assignment_status === 'accepted',
            403,
            'คุณไม่มีสิทธิ์ติดตามสถานะห้องนี้'
        );

        abort_unless(
            $assignment->assignment_status === 'accepted',
            403,
            'กรุณารับงานตัดสินก่อนเข้าห้อง'
        );

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

    /**
     * ส่งสถานะห้องให้หน้า Judge polling
     */
    public function state(
        JudgingSession $session
    ): JsonResponse {
        $this->getAssignment($session);

        $session->refresh();

        return response()->json([
            'id' => $session->id,
            'status' => $session->status,
            'state_version' => $session->state_version,
            'current_submission_id' =>
                $session->current_submission_id,
            'current_file_id' =>
                $session->current_file_id,
            'current_page' =>
                $session->current_page,
            'scroll_progress' =>
                (float) $session->scroll_progress,
            'zoom' =>
                (float) $session->zoom,
            'started_at' =>
                $session->started_at?->toISOString(),
            'ended_at' =>
                $session->ended_at?->toISOString(),
        ]);
    }

    /**
     * ตรวจว่ากรรมการได้รับมอบหมายในห้องนี้
     */
    private function getAssignment(
        JudgingSession $session
    ): JudgeAssignment {
        return JudgeAssignment::query()
            ->where(
                'competition_id',
                $session->competition_id
            )
            ->where(
                'judge_id',
                Auth::id()
            )
            ->firstOrFail();
    }

    public function saveDarft(Request $request, JudgingSession $session): JsonRespone{
        $this->getAssignment($session);
        return respone()->json([
            'success' => true,
            'message' => 'Draft saved.',
        ]);
    }
    public function submit(Request $request, JudgingSession $session): Jsonrespone{
        $this->getAssignment($session);
        return respone()->json([
            'success' => true,
            'message' => 'score submitted'
        ]);
    }
}