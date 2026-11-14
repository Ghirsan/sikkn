<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Models\Concerns\HasUlid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['ulid', 'name', 'email', 'password', 'must_change_password', 'role', 'nim', 'nip', 'faculty_id', 'study_program_id', 'group_id', 'phone', 'emergency_phone', 'emergency_name', 'emergency_relationship'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUlid, Notifiable, TwoFactorAuthenticatable;

    public const EMERGENCY_RELATIONSHIPS = [
        'Orang Tua',
        'Wali',
        'Saudara Kandung',
        'Suami / Istri',
        'Kerabat',
    ];

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
            'must_change_password' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    /**
     * Determine whether an email address is a system-generated placeholder.
     */
    public static function isPlaceholderEmail(?string $email): bool
    {
        if (blank($email)) {
            return true;
        }

        $normalized = Str::lower(trim($email));

        return str_ends_with($normalized, '@student.sikkn.ac.id')
            || str_ends_with($normalized, '@lecturer.sikkn.ac.id');
    }

    /**
     * Check if the user currently has a system-generated placeholder email.
     */
    public function hasPlaceholderEmail(): bool
    {
        return self::isPlaceholderEmail($this->email);
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
    public function isSaintek(): bool
    {
        $short = strtoupper((string) ($this->faculty?->short_name ?? ''));
        if ($short === 'SV') {
            $nim = (string) $this->nim;
            if (str_starts_with($nim, '4003') || str_starts_with($nim, '4004')) {
                return true;
            }
            if (str_starts_with($nim, '4001') || str_starts_with($nim, '4002')) {
                return false;
            }
        }

        $saintek = ['FT', 'FK', 'FPP', 'FPIK', 'FSM', 'FKM'];
        if (in_array($short, $saintek, true)) {
            return true;
        }

        $soshum = ['FH', 'FEB', 'FISIP', 'FIB', 'FPSI', 'FPSIKOLOGI'];
        if (in_array($short, $soshum, true)) {
            return false;
        }

        // Fallback by NIM prefix if faculty relation not loaded or different
        $nim = (string) $this->nim;
        if (str_starts_with($nim, '4003') || str_starts_with($nim, '4004')) {
            return true;
        }
        if (str_starts_with($nim, '4001') || str_starts_with($nim, '4002') || str_starts_with($nim, '11') || str_starts_with($nim, '12')) {
            return false;
        }
        if (str_starts_with($nim, '2')) {
            return true;
        }

        return false;
    }

    public function rumpun(): string
    {
        return $this->isSaintek() ? 'Saintek' : 'Soshum';
    }

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
     * Get all grades for this student.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    /**
     * Get the current/latest grade for this student.
     */
    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class, 'student_id')->latestOfMany();
    }

    /**
     * Get the program participations for this student.
     */
    public function programParticipants(): HasMany
    {
        return $this->hasMany(ProgramParticipant::class, 'student_id');
    }

    /**
     * Get the program outputs produced by this student.
     */
    public function participantOutputs(): HasManyThrough
    {
        return $this->hasManyThrough(
            ParticipantOutput::class,
            ProgramParticipant::class,
            'student_id',
            'program_participant_id'
        );
    }

    /**
     * Alias for participantOutputs.
     */
    public function programOutputs(): HasManyThrough
    {
        return $this->participantOutputs();
    }

    /**
     * Get grades submitted by this DPL.
     */
    public function dplGrades(): HasMany
    {
        return $this->hasMany(Grade::class, 'dpl_id');
    }

    /**
     * Get the schedule events created for this student.
     */
    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class, 'student_id');
    }

    /**
     * Determine whether this student has operational KKN records that prohibit deletion.
     */
    public function hasStudentActivityRecords(): bool
    {
        $countsLoaded = isset(
            $this->daily_logs_count,
            $this->program_participants_count,
            $this->programs_count,
            $this->mentoring_logs_count,
            $this->schedule_events_count,
            $this->grades_count,
        );

        if ($countsLoaded) {
            return (int) $this->daily_logs_count > 0
                || (int) $this->program_participants_count > 0
                || (int) $this->programs_count > 0
                || (int) $this->mentoring_logs_count > 0
                || (int) $this->schedule_events_count > 0
                || (int) $this->grades_count > 0;
        }

        return $this->dailyLogs()->exists()
            || $this->programParticipants()->exists()
            || $this->programs()->exists()
            || $this->mentoringLogs()->exists()
            || $this->scheduleEvents()->exists()
            || $this->grades()->exists();
    }

    /**
     * Determine whether this DPL has submitted grades or personal operational records.
     */
    public function hasDplActivityRecords(): bool
    {
        $countsLoaded = isset(
            $this->dpl_grades_count,
            $this->programs_count,
            $this->daily_logs_count,
            $this->mentoring_logs_count,
            $this->program_participants_count,
            $this->schedule_events_count,
        );

        if ($countsLoaded) {
            return (int) $this->dpl_grades_count > 0
                || (int) $this->programs_count > 0
                || (int) $this->daily_logs_count > 0
                || (int) $this->mentoring_logs_count > 0
                || (int) $this->program_participants_count > 0
                || (int) $this->schedule_events_count > 0;
        }

        return $this->dplGrades()->exists()
            || $this->programs()->exists()
            || $this->dailyLogs()->exists()
            || $this->mentoringLogs()->exists()
            || $this->programParticipants()->exists()
            || $this->scheduleEvents()->exists();
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeRole(Builder $query, UserRole|string $role): Builder
    {
        $value = $role instanceof UserRole ? $role->value : $role;

        return $query->where('role', $value);
    }

    public function scopeMahasiswa(Builder $query): Builder
    {
        return $this->scopeRole($query, UserRole::Mahasiswa);
    }

    public function scopeDpl(Builder $query): Builder
    {
        return $this->scopeRole($query, UserRole::Dpl);
    }

    public function scopeP2kkn(Builder $query): Builder
    {
        return $this->scopeRole($query, UserRole::P2kkn);
    }

    public function scopeProdi(Builder $query): Builder
    {
        return $this->scopeRole($query, UserRole::Prodi);
    }

    public function scopeFakultas(Builder $query): Builder
    {
        return $this->scopeRole($query, UserRole::Fakultas);
    }

    public function scopeForStudyProgram(Builder $query, int $studyProgramId): Builder
    {
        return $query->where('study_program_id', $studyProgramId);
    }

    public function scopeForFaculty(Builder $query, int $facultyId): Builder
    {
        return $query->where('faculty_id', $facultyId);
    }

    public function scopeInGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('nim', 'like', "%{$term}%")
                ->orWhere('nip', 'like', "%{$term}%")
                ->orWhereHas('studyProgram', function (Builder $sq) use ($term) {
                    $sq->where('name', 'like', "%{$term}%");
                });
        });
    }
}
