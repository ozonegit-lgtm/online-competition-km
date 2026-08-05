<?php

namespace App\Policies;

use App\Models\Competition;
use App\Models\User;

class CompetitionPolicy
{
    /**
     * Super Admin ดูการแข่งขันทั้งหมดได้
     * Competition Admin ดูการแข่งขันของตัวเองได้
     * Judge ดูการแข่งขันที่ได้รับมอบหมายได้
     */
    public function view(
        User $user,
        Competition $competition
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isOwner($user, $competition)) {
            return true;
        }

        return $this->hasAcceptedAssignment(
            $user,
            $competition
        );
    }

    /**
     * แก้ไขและจัดการการแข่งขันได้เฉพาะเจ้าของ
     */
    public function manage(
        User $user,
        Competition $competition
    ): bool {
        return $this->isOwner($user, $competition);
    }

    /**
     * เฉพาะ Super Admin เท่านั้นที่แต่งตั้งกรรมการ
     */
    public function assignJudges(
        User $user,
        Competition $competition
    ): bool {
        return $this->isSuperAdmin($user);
    }

    /**
     * สิทธิ์เข้าห้องตัดสิน
     *
     * Super Admin เข้าเพื่อตรวจสอบได้
     * Competition Admin เจ้าของการแข่งขันเข้าได้
     * Judge ต้องตอบรับ Assignment แล้ว
     */
    public function enterJudgingRoom(
        User $user,
        Competition $competition
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->isOwner($user, $competition)) {
            return true;
        }

        return $this->hasAcceptedAssignment(
            $user,
            $competition
        );
    }

    private function isOwner(
        User $user,
        Competition $competition
    ): bool {
        return (int) $competition->created_by
            === (int) $user->id;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->role?->role_name === 'Super Admin';
    }

    private function hasAcceptedAssignment(
        User $user,
        Competition $competition
    ): bool {
        return $competition->judgeAssignments()
            ->where('judge_id', $user->id)
            ->where('assignment_status', 'accepted')
            ->exists();
    }
}