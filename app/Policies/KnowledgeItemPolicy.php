<?php

namespace App\Policies;

use App\Models\KnowledgeItem;
use App\Models\User;

class KnowledgeItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdministrator($user);
    }

    public function view(User $user, KnowledgeItem $knowledgeItem): bool
    {
        return $this->canManage($user, $knowledgeItem);
    }

    public function update(User $user, KnowledgeItem $knowledgeItem): bool
    {
        return $this->canManage($user, $knowledgeItem);
    }

    public function publish(User $user, KnowledgeItem $knowledgeItem): bool
    {
        return $this->canManage($user, $knowledgeItem);
    }

    public function unpublish(User $user, KnowledgeItem $knowledgeItem): bool
    {
        return $this->canManage($user, $knowledgeItem);
    }

    public function delete(User $user, KnowledgeItem $knowledgeItem): bool
    {
        return $this->canManage($user, $knowledgeItem);
    }

    public function feature(User $user, KnowledgeItem $knowledgeItem): bool
    {
        return $this->isSuperAdmin($user);
    }

    private function canManage(
        User $user,
        KnowledgeItem $knowledgeItem
    ): bool {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $this->isCompetitionAdmin($user)
            && $knowledgeItem->created_by !== null
            && (int) $knowledgeItem->created_by === (int) $user->id;
    }

    private function isAdministrator(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || $this->isCompetitionAdmin($user);
    }

    private function isSuperAdmin(User $user): bool
    {
        return $user->role?->role_name === 'Super Admin';
    }

    private function isCompetitionAdmin(User $user): bool
    {
        return $user->role?->role_name === 'Competition Admin';
    }
}
