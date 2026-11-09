<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    /**
     * Determine whether the user can view the group's LRK (preview or PDF).
     */
    public function viewLrk(User $user, Group $group): bool
    {
        return $this->canAccessGroupDocuments($user, $group);
    }

    /**
     * Determine whether the user can view the group's LPK (preview or PDF).
     */
    public function viewLpk(User $user, Group $group): bool
    {
        return $this->canAccessGroupDocuments($user, $group);
    }

    /**
     * Common authorization logic for group documents (LRK, LPK).
     */
    private function canAccessGroupDocuments(User $user, Group $group): bool
    {
        if ($user->hasRole(UserRole::P2kkn)) {
            return true;
        }

        if ($user->hasRole(UserRole::Mahasiswa)) {
            return $user->group_id === $group->id;
        }

        if ($user->hasRole(UserRole::Dpl)) {
            return $user->dplGroups()->where('groups.id', $group->id)->exists();
        }

        if ($user->hasRole(UserRole::Prodi) && $user->study_program_id) {
            return $group->students()->where('study_program_id', $user->study_program_id)->exists();
        }

        if ($user->hasRole(UserRole::Fakultas) && $user->faculty_id) {
            return $group->students()->where('faculty_id', $user->faculty_id)->exists();
        }

        return false;
    }
}

