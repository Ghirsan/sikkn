<?php

namespace App\Livewire\Concerns;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;

trait ScopesObserverQuery
{
    /**
     * Scope the query based on the current user's role (Prodi or Fakultas).
     *
     * @param  Builder  $query  The Eloquent query builder.
     * @param  string  $relationPrefix  Prefix for relations (e.g. 'student.' if the query is not on the User model itself).
     * @return Builder
     */
    protected function applyObserverScope(Builder $query, string $relationPrefix = '')
    {
        $user = auth()->user();

        $prefix = $relationPrefix ? $relationPrefix.'.' : '';

        if ($user->hasRole(UserRole::Prodi)) {
            return $query->where($prefix.'study_program_id', $user->study_program_id);
        }

        if ($user->hasRole(UserRole::Fakultas)) {
            return $query->where($prefix.'faculty_id', $user->faculty_id);
        }

        return $query;
    }

    /**
     * Scope the group query to groups containing at least one student matching the observer's scope.
     *
     * @param  Builder  $query  The Group query builder.
     * @return Builder
     */
    protected function applyObserverGroupScope(Builder $query)
    {
        return $query->whereHas('students', function ($q) {
            $this->applyObserverScope($q);
        });
    }
}
