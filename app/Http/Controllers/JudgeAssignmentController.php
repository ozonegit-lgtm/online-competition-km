<?php

namespace App\Http\Controllers;

use App\Models\JudgeAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class JudgeAssignmentController extends Controller
{
    /**
     * กรรมการตอบรับงานตัดสิน
     */
    public function accept(
        JudgeAssignment $assignment
    ): RedirectResponse {
        $this->authorizeAssignment($assignment);

        if ($assignment->submitted_at) {
            return back()->with(
                'error',
                'งานตัดสินนี้ส่งคะแนนเรียบร้อยแล้ว'
            );
        }

        if ($assignment->assignment_status === 'accepted') {
            return back()->with(
                'success',
                'คุณรับงานตัดสินนี้แล้ว'
            );
        }

        $assignment->update([
            'assignment_status' => 'accepted',
            'accepted_at' => now(),
            'declined_at' => null,
        ]);

        return back()->with(
            'success',
            'รับงานตัดสินเรียบร้อยแล้ว'
        );
    }

    /**
     * กรรมการปฏิเสธงานตัดสิน
     */
    public function decline(
        JudgeAssignment $assignment
    ): RedirectResponse {
        $this->authorizeAssignment($assignment);

        if ($assignment->submitted_at) {
            return back()->with(
                'error',
                'ไม่สามารถปฏิเสธงานที่ส่งคะแนนแล้ว'
            );
        }

        if ($assignment->scores()->exists()) {
            return back()->with(
                'error',
                'ไม่สามารถปฏิเสธงานที่เริ่มบันทึกคะแนนแล้ว'
            );
        }

        if ($assignment->assignment_status === 'declined') {
            return back()->with(
                'success',
                'คุณปฏิเสธงานตัดสินนี้แล้ว'
            );
        }

        $assignment->update([
            'assignment_status' => 'declined',
            'accepted_at' => null,
            'declined_at' => now(),
        ]);

        return back()->with(
            'success',
            'ปฏิเสธงานตัดสินเรียบร้อยแล้ว'
        );
    }

    /**
     * ตรวจว่า Assignment เป็นของผู้ใช้ปัจจุบัน
     */
    private function authorizeAssignment(
        JudgeAssignment $assignment
    ): void {
        abort_unless(
            (int) $assignment->judge_id ===
                (int) Auth::id(),
            403,
            'คุณไม่มีสิทธิ์จัดการงานตัดสินนี้'
        );
    }
}