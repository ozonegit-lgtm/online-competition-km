<?php

namespace App\Http\Controllers\CompetitionAdmin;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\JudgingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class JudgingSessionController extends Controller
{
    /**
     * แสดงรายการห้องตัดสินของผู้จัดการแข่งขัน
     */
    public function index(): View
    {
        $competitions = Competition::query()
            ->where('created_by', Auth::id())
            ->with('judgingSession')
            ->withCount([
                'submissions',
                'rubrics',
                'judgeAssignments',
            ])
            ->latest()
            ->paginate(10);

        return view(
            'competition-admin.judging-room.index',
            compact('competitions')
        );
    }

    /**
     * แสดงหน้าควบคุมห้องตัดสิน
     */
    public function show(Competition $competition): View
    {
        $this->authorizeCompetition($competition);

        // หนึ่งการแข่งขันมีหนึ่งห้องตัดสิน
        $session = JudgingSession::firstOrCreate(
            [
                'competition_id' => $competition->id,
            ],
            [
                'controller_user_id' => Auth::id(),
                'status' => JudgingSession::STATUS_WAITING,
                'current_page' => 1,
                'scroll_progress' => 0,
                'zoom' => 1,
                'state_version' => 0,
            ]
        );

        $competition->load([
            'rubrics' => function ($query) {
                $query
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            },
            'submissions' => function ($query) {
                $query
                ->whereIn('status', [
                        'submitted',
                        'under_review',
                    ])
                    ->with('files',)
                    ->oldest('submitted_at');
            },
            'judgeAssignments.judge',
        ]);

        $session->load([
            'currentSubmission.files',
            'currentFile',
        ]);

        return view(
            'competition-admin.judging-room.show',
            [
                'competition' => $competition,
                'session' => $session,
                'submissions' => $competition->submissions,
                'rubrics' => $competition->rubrics,
                'assignments' => $competition->judgeAssignments,
                'currentSubmission' => $session->currentSubmission,
                'currentFile' => $session->currentFile,
            ]
        );
    }

    /**
     * เริ่มการตัดสินแบบ Live
     */
    public function start(
        Competition $competition
    ): RedirectResponse {
        $this->authorizeCompetition($competition);

        $session = $this->getSession($competition);

        if (!$competition->rubrics()->where('is_active', true)->exists()) {
            return back()->with(
                'error',
                'ยังไม่สามารถเริ่ม Live ได้ เนื่องจากยังไม่มีเกณฑ์ที่เปิดใช้งาน'
            );
        }

        if (! $competition->judgeAssignments()
            ->where('assignment_status', 'accepted')
            ->exists()) {
            return back()->with(
                'error',
                'ยังไม่สามารถเริ่ม Live ได้ เนื่องจากยังไม่มีกรรมการตอบรับงานตัดสิน'
            );
        }

        $firstSubmission = $competition
            ->submissions()
            ->whereIn('status', [
                'submitted',
                'under_review',
            ])
            ->with('files')
            ->oldest('submitted_at')
            ->first();

        if (!$firstSubmission) {
            return back()->with(
                'error',
                'ยังไม่สามารถเริ่ม Live ได้ เนื่องจากยังไม่มีผลงาน'
            );
        }

        DB::transaction(function () use (
            $session,
            $firstSubmission,
            $competition
        ) {
            $submission = $competition
                ->submissions()
                ->whereKey($session->current_submission_id)
                ->whereIn('status', [
                    'submitted',
                    'under_review',
                ])
                ->first() ?? $firstSubmission;

            $currentFileId = $session->current_file_id;

            if (
                !$currentFileId ||
                !$submission->files()
                    ->whereKey($currentFileId)
                    ->exists()
            ) {
                $currentFileId = $submission
                    ->files()
                    ->oldest('id')
                    ->value('id');
            }

            $session->update([
                'controller_user_id' => Auth::id(),
                'current_submission_id' => $submission->id,
                'current_file_id' => $currentFileId,
                'status' => JudgingSession::STATUS_LIVE,
                'current_page' => 1,
                'scroll_progress' => 0,
                'zoom' => 1,
                'state_version' => $session->state_version + 1,
                'started_at' => $session->started_at ?? now(),
                'ended_at' => null,
            ]);

            $submission->update([
                'status' => 'under_review',
            ]);
        });

        return back()->with(
            'success',
            'เริ่มการตัดสินแบบ Live แล้ว'
        );
    }

    /**
     * หยุดการตัดสินชั่วคราว
     */
    public function pause(
        Competition $competition
    ): RedirectResponse {
        $this->authorizeCompetition($competition);

        $session = $this->getSession($competition);

        if (!$session->isLive()) {
            return back()->with(
                'error',
                'สามารถหยุดชั่วคราวได้เฉพาะห้องที่กำลัง Live'
            );
        }

        $this->updateSessionState($session, [
            'status' => JudgingSession::STATUS_PAUSED,
        ]);

        return back()->with(
            'success',
            'หยุดการตัดสินชั่วคราวแล้ว'
        );
    }

    /**
     * ดำเนินการ Live ต่อ
     */
    public function resume(
        Competition $competition
    ): RedirectResponse {
        $this->authorizeCompetition($competition);

        $session = $this->getSession($competition);

        if (!$session->isPaused()) {
            return back()->with(
                'error',
                'สามารถดำเนินการต่อได้เฉพาะห้องที่หยุดชั่วคราว'
            );
        }

        $this->updateSessionState($session, [
            'status' => JudgingSession::STATUS_LIVE,
        ]);

        return back()->with(
            'success',
            'ดำเนินการตัดสินต่อแล้ว'
        );
    }

    /**
     * เลือกผลงานที่กำลังนำเสนอ
     */
    public function selectSubmission(
        Request $request,
        Competition $competition
    ): RedirectResponse {
        $this->authorizeCompetition($competition);

        $validated = $request->validate([
            'submission_id' => [
                'required',
                'integer',
            ],
        ]);

        $submission = $competition
            ->submissions()
            ->whereKey($validated['submission_id'])
            ->whereIn('status', [
                'submitted',
                'under_review',
            ])
            ->with('files')
            ->firstOrFail();

        $session = $this->getSession($competition);

        if (
            $session->isEnded() ||
            $session->isClosed()
        ) {
            return back()->with(
                'error',
                'ไม่สามารถเปลี่ยนผลงานในห้องที่จบหรือปิดแล้ว'
            );
        }

        DB::transaction(function () use (
            $session,
            $submission
        ) {
            $this->updateSessionState($session, [
                'current_submission_id' => $submission->id,
                'current_file_id' => $submission
                    ->files()
                    ->oldest('id')
                    ->value('id'),
                'current_page' => 1,
                'scroll_progress' => 0,
                'zoom' => 1,
            ]);

            $submission->update([
                'status' => 'under_review',
            ]);
        });

        return back()->with(
            'success',
            'เปลี่ยนผลงานที่กำลังตัดสินแล้ว'
        );
    }

    /**
     * จบการตัดสิน
     */
    public function end(
        Competition $competition
    ): RedirectResponse {
        $this->authorizeCompetition($competition);

        $session = $this->getSession($competition);

        if (
            !$session->isLive() &&
            !$session->isPaused()
        ) {
            return back()->with(
                'error',
                'ห้องนี้ไม่ได้อยู่ระหว่างการตัดสิน'
            );
        }

        $this->updateSessionState($session, [
            'status' => JudgingSession::STATUS_ENDED,
            'ended_at' => now(),
        ]);

        return back()->with(
            'success',
            'จบการตัดสินแล้ว'
        );
    }

    /**
     * ปิดห้องตัดสิน
     */
    public function close(
        Competition $competition
    ): RedirectResponse {
        $this->authorizeCompetition($competition);

        $session = $this->getSession($competition);

        if (!$session->isEnded()) {
            return back()->with(
                'error',
                'ต้องจบการตัดสินก่อนปิดห้อง'
            );
        }

        $this->updateSessionState($session, [
            'status' => JudgingSession::STATUS_CLOSED,
        ]);

        return back()->with(
            'success',
            'ปิดห้องตัดสินแล้ว'
        );
    }

    /**
     * ดึงห้องตัดสินของการแข่งขัน
     */
    private function getSession(
        Competition $competition
    ): JudgingSession {
        return JudgingSession::firstOrCreate(
            [
                'competition_id' => $competition->id,
            ],
            [
                'controller_user_id' => Auth::id(),
                'status' => JudgingSession::STATUS_WAITING,
                'current_page' => 1,
                'scroll_progress' => 0,
                'zoom' => 1,
                'state_version' => 0,
            ]
        );
    }

    /**
     * อัปเดตสถานะพร้อมเพิ่ม version สำหรับ Live polling
     */
    private function updateSessionState(
        JudgingSession $session,
        array $attributes
    ): void {
        $attributes['state_version'] =
            $session->state_version + 1;

        $session->update($attributes);
    }

    /**
     * ตรวจว่าเป็นเจ้าของการแข่งขัน
     */
    private function authorizeCompetition(
        Competition $competition
    ): void {
        abort_unless(
            (int) $competition->created_by ===
                (int) Auth::id(),
            403,
            'คุณไม่มีสิทธิ์ควบคุมการแข่งขันนี้'
        );
    }
}