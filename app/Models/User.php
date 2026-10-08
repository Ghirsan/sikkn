<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'role', 'nim', 'nip', 'faculty_id', 'study_program_id', 'group_id', 'phone', 'emergency_phone'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            // Auto-resolve from NIM for students
            if ($user->isDirty('nim') && $user->nim) {
                $code = substr($user->nim, 0, 6);
                $studyProgram = StudyProgram::where('code', $code)->first();
                if ($studyProgram) {
                    $user->study_program_id = $studyProgram->id;
                    $user->faculty_id = $studyProgram->faculty_id;
                }
            }
            
            // Auto-fill faculty_id if study_program_id was manually assigned (e.g., DPL, Prodi admin)
            if ($user->isDirty('study_program_id') && $user->study_program_id && ! $user->isDirty('faculty_id')) {
                $facultyId = StudyProgram::where('id', $user->study_program_id)->value('faculty_id');
                if ($facultyId) {
                    $user->faculty_id = $facultyId;
                }
            }
        });
    }

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
     * Build a WhatsApp URL from a stored phone number.
     */
    public function whatsappUrl(?string $phone = null): ?string
    {
        $phone ??= $this->phone;

        if (blank($phone)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '+')) {
            return $digits !== '' ? "https://wa.me/{$digits}" : null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return $digits !== '' ? "https://wa.me/{$digits}" : null;
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
        if ($this->role !== UserRole::Dpl) {
            return false;
        }

        return $this->dplGroups()->where('lead_dpl_id', $this->id)->exists();
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
     * Get the study program this user belongs to.
     */
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    /**
     * Get the faculty this user belongs to.
     */
    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    /**
     * Get the user's prodi name gracefully.
     */
    public function getProdiAttribute(): ?string
    {
        return $this->studyProgram?->name;
    }

    /**
     * Get the user's fakultas name gracefully.
     */
    public function getFakultasAttribute(): ?string
    {
        return $this->faculty?->name ?? $this->studyProgram?->faculty?->name;
    }

    /**
     * Get the user's fakultas short name (initials) gracefully.
     */
    public function getFakultasShortAttribute(): ?string
    {
        return $this->faculty?->short_name ?? $this->studyProgram?->faculty?->short_name;
    }

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
     * Get the groups this DPL supervises.
     */
    public function dplGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'dpl_group', 'dpl_id', 'group_id');
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
