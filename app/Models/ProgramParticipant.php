<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Services\ExternalImagePreviewUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramParticipant extends Model
{
    protected $fillable = [
        'program_id',
        'student_id',
        'participant_code',
        'participant_title',
        'role_in_program',
        'responsibility',
        'status',
        'revision_note',
        'achievement',
        'obstacle',
        'solution',
        'lpk_status',
        'execution_date',
        'problem_potential',
        'location',
        'method',
        'target_audience',
        'output_target',
        'execution_description',
        'documentation_image_path',
        'documentation_caption',
        'sdg_category_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProgramStatus::class,
            'lpk_status' => ProgramStatus::class,
            'execution_date' => 'date',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($participant) {
            if (empty($participant->participant_code)) {
                $participant->participant_code = static::generateParticipantCode($participant);
            }
        });
    }

    public static function extractInitials(?string $name): string
    {
        if (blank($name)) {
            return 'XX';
        }

        $words = preg_split('/\s+/', trim((string) $name));
        $initials = '';
        foreach ($words as $word) {
            $char = mb_substr($word, 0, 1);
            if (ctype_alpha($char)) {
                $initials .= strtoupper($char);
            }
        }

        if (strlen($initials) === 0) {
            return 'XX';
        }

        if (strlen($initials) === 1) {
            $second = mb_substr($words[0] ?? '', 1, 1);
            if ($second && ctype_alpha($second)) {
                $initials .= strtoupper($second);
            } else {
                $initials .= 'X';
            }
        }

        if (strlen($initials) > 3) {
            $initials = substr($initials, 0, 3);
        }

        return $initials;
    }

    public static function generateParticipantCode($participant)
    {
        $program = $participant->program ?? Program::with('programType')->find($participant->program_id);
        if (! $program || ! $participant->student_id) {
            return null;
        }

        if (! $program->relationLoaded('programType')) {
            $program->load('programType');
        }

        $student = $participant->student ?? User::find($participant->student_id);
        $initials = static::extractInitials($student?->name);

        $typePrefix = match ($program->programType?->code) {
            'multidisiplin' => 'M',
            'sosial_kemasyarakatan' => 'SK',
            'lainnya' => 'L',
            default => 'X',
        };

        $groupId = $program->group_id;

        // Count existing participants for this student + type within the same group to determine sequence
        $existingCount = static::whereHas('program', function ($q) use ($program, $groupId) {
            $q->whereType($program->programType?->code);
            if ($groupId) {
                $q->where('group_id', $groupId);
            }
        })
            ->where('student_id', $participant->student_id)
            ->where('id', '!=', $participant->id ?? 0)
            ->count();

        $sequence = $existingCount + 1;

        // Ensure uniqueness by checking for existing codes within the same group (group_id)
        $baseCode = "{$typePrefix}{$sequence}-{$initials}";
        $disambiguator = 2;
        $code = $baseCode;
        while (static::whereHas('program', function ($q) use ($groupId) {
            if ($groupId) {
                $q->where('group_id', $groupId);
            }
        })->where('participant_code', $code)->where('id', '!=', $participant->id ?? 0)->exists()) {
            $code = "{$typePrefix}{$sequence}-{$initials}{$disambiguator}";
            $disambiguator++;
        }

        return $code;
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(ParticipantOutput::class, 'program_participant_id');
    }

    public function sdgCategory(): BelongsTo
    {
        return $this->belongsTo(SdgCategory::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [ProgramStatus::Draft, ProgramStatus::NeedsRevision]);
    }

    public function hasFilledLpk(): bool
    {
        return ! empty($this->achievement) && ! empty($this->documentation_image_path);
    }

    public function documentationImageUrl(): ?string
    {
        if (! $this->documentation_image_path) {
            return null;
        }

        if (! filter_var($this->documentation_image_path, FILTER_VALIDATE_URL)) {
            return asset('storage/'.$this->documentation_image_path);
        }

        try {
            return app(ExternalImagePreviewUrl::class)->resolve($this->documentation_image_path);
        } catch (\InvalidArgumentException) {
            return $this->documentation_image_path;
        }
    }

    public function getDocumentationImageUrlAttribute(): ?string
    {
        return $this->documentationImageUrl();
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', ProgramStatus::Approved);
    }

    public function scopeNeedsRevision(Builder $query): Builder
    {
        return $query->where('status', ProgramStatus::NeedsRevision);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ProgramStatus::Draft);
    }

    public function scopeLpkApproved(Builder $query): Builder
    {
        return $query->where('lpk_status', ProgramStatus::Approved);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->whereHas('program', function (Builder $q) use ($groupId) {
            $q->where('group_id', $groupId);
        });
    }
}
