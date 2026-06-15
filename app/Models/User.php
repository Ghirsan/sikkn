<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'nim', 'nip', 'prodi', 'fakultas', 'group_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Check if the user has the given role.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(UserRole ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if the user is an admin (P2KKN, Prodi, or Fakultas).
     */
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    /**
     * Check if the user is the lead DPL for their group.
     */
    public function isLeadDpl(): bool
    {
        return $this->role === UserRole::Dpl 
            && $this->group_id !== null 
            && $this->id === $this->group->lead_dpl_id;
    }

    /**
     * Check if the user is the student leader for their group.
     */
    public function isStudentLeader(): bool
    {
        return $this->role === UserRole::Mahasiswa 
            && $this->group_id !== null 
            && $this->id === $this->group->student_leader_id;
    }

    // ── Relationships ────────────────────────────────────────────

    /**
     * Get the group this student belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Alias: get the group this DPL supervises (same FK as student).
     */
    public function supervisedGroup(): BelongsTo
    {
        return $this->group();
    }

    /**
     * Get the programs proposed by this student.
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'student_id');
    }

    /**
     * Get the daily logs for this student.
     */
    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class, 'student_id');
    }

    /**
     * Get the mentoring logs for this student.
     */
    public function mentoringLogs(): HasMany
    {
        return $this->hasMany(MentoringLog::class, 'student_id');
    }

    /**
     * Get the grade for this student.
     */
    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class, 'student_id');
    }

    /**
     * Get the program outputs produced by this student.
     */
    public function programOutputs(): HasMany
    {
        return $this->hasMany(ProgramOutput::class, 'student_id');
    }
}
