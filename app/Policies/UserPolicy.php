<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view/download the student's logbook.
     */
    public function viewLogbook(User $user, User $student): bool
    {
        if ($student->role !== UserRole::Mahasiswa) {
            return false;
        }

        if ($user->id === $student->id) {
            return true;
        }

        return $this->canAccessStudentAcademicRecords($user, $student);
    }

    /**
     * Determine whether the user can view/download the student's mentoring logs.
     */
    public function viewMentoringLogs(User $user, User $student): bool
    {
        if ($student->role !== UserRole::Mahasiswa) {
            return false;
        }

        if ($user->id === $student->id) {
            return true;
        }

        return $this->canAccessStudentAcademicRecords($user, $student);
    }

    /**
     * Determine whether the user can grade the student.
     */
    public function gradeStudent(User $user, User $student): bool
    {
        if ($student->role !== UserRole::Mahasiswa) {
            return false;
        }

        if ($user->hasRole(UserRole::P2kkn)) {
            return true;
        }

        if ($user->hasRole(UserRole::Dpl)) {
            return (bool) ($student->group_id && $user->dplGroups()->where('groups.id', $student->group_id)->exists());
        }

        return false;
    }

    /**
     * Shared logic for DPL, Admin, and Observers accessing a student's records.
     */
    private function canAccessStudentAcademicRecords(User $user, User $student): bool
    {
        if ($user->hasRole(UserRole::P2kkn)) {
            return true;
        }

        if ($user->hasRole(UserRole::Dpl)) {
            return (bool) ($student->group_id && $user->dplGroups()->where('groups.id', $student->group_id)->exists());
        }

        if ($user->hasRole(UserRole::Prodi) && $user->study_program_id) {
            return $student->study_program_id === $user->study_program_id;
        }

        if ($user->hasRole(UserRole::Fakultas) && $user->faculty_id) {
            return $student->faculty_id === $user->faculty_id;
        }

        return false;
    }
}
