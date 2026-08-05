<?php

namespace App\Policies;

use App\Models\JudgingSession;
use App\Models\User;

class JudgingSessionPolicy
{
    /**
     * ตรวจสิทธิ์ดูห้องตัดสิน
     */
    public function view(
        User $user,
        JudgingSession $session
    ): bool {
        $competition = $session->competition;

        if (! $competition) {
            return false;
        }

        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isOwner($user, $session)) {
            return true;
        }

        return $competition->judgeAssignments()
            ->where('judge_id', $user->id)
            ->where('assignment_status', 'accepted')
            ->exists();
    }

    /**
     * เริ่ม พัก เปลี่ยนผลงาน และจบห้อง
     * ทำได้เฉพาะ Competition Admin เจ้าของการแข่งขัน
     */
    public function control(
        User $user,
        JudgingSession $session
    ): bool {
        return $this->isOwner($user, $session);
    }

    /**
     * อัปเดตสถานะการนำเสนอ
     */
    public function updatePresentation(
        User $user,
        JudgingSession $session
    ): bool {
        return $this->isOwner($user, $session);
    }

    private function isOwner(
        User $user,
        JudgingSession $session
    ): bool {
        return (int) $session->competition?->created_by
            === (int) $user->id;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->role?->role_name === 'Super Admin';
    }
}