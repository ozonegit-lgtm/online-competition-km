<?php

namespace App\Policies;

use App\Models\Score;
use App\Models\Submission;
use App\Models\User;

class ScorePolicy
{
    /**
     * ตรวจว่า Judge ให้คะแนนผลงานนี้ได้หรือไม่
     */
    public function scoreSubmission(
        User $user,
        Submission $submission
    ): bool {
        return $user->judgeAssignments()
            ->where('competition_id', $submission->competition_id)
            ->where('assignment_status', 'accepted')
            ->exists();
    }

    /**
     * ดูคะแนน
     *
     * Super Admin ดูได้
     * Competition Admin เจ้าของการแข่งขันดูคะแนนที่ส่งแล้วได้
     * Judge ดูคะแนนของตัวเองได้
     */
    public function view(
        User $user,
        Score $score
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        if ($this->ownsScore($user, $score)) {
            return true;
        }

        $isCompetitionOwner =
            (int) $score->submission?->competition?->created_by
            === (int) $user->id;

        return $isCompetitionOwner
            && $score->submitted_at !== null;
    }

    /**
     * แก้ไขได้เฉพาะคะแนนของตัวเอง
     * และต้องยังไม่กดยืนยันคะแนน
     */
    public function update(
        User $user,
        Score $score
    ): bool {
        return $this->ownsScore($user, $score)
            && $score->submitted_at === null;
    }

    /**
     * ยืนยันคะแนนได้เฉพาะเจ้าของคะแนน
     * และต้องยังไม่เคยยืนยัน
     */
    public function submit(
        User $user,
        Score $score
    ): bool {
        return $this->ownsScore($user, $score)
            && $score->submitted_at === null;
    }

    private function ownsScore(
        User $user,
        Score $score
    ): bool {
        return (int) $score->judgeAssignment?->judge_id
            === (int) $user->id;
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->role?->role_name === 'Super Admin';
    }
}