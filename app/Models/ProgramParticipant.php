<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Services\ExternalImagePreviewUrl;
use Illuminate\Database\Eloquent\Model;

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

    public static function generateParticipantCode($participant)
    {
        $program = $participant->program ?? Program::with('programType')->find($participant->program_id);
        if (! $program || ! $participant->student_id) {
            return null;
        }

        $typePrefix = match ($program->programType?->code) {
            'multidisiplin' => 'M',
            'sosial_kemasyarakatan' => 'SK',
            'lainnya' => 'L',
            default => 'X',
        };

        // Count existing participants for this student + type to determine sequence
        $existingCount = static::whereHas('program', function ($q) use ($program) {
            $q->whereType($program->programType?->code);
            if ($program->programType?->code === 'multidisiplin') {
                $q->where('group_id', $program->group_id);
            }
        })
            ->where('student_id', $participant->student_id)
            ->count();

        $sequence = $existingCount + 1;

        // Ensure uniqueness by checking for existing codes
        $baseCode = "{$typePrefix}{$sequence}M{$participant->student_id}";
        while (static::where('participant_code', $baseCode)->exists()) {
            $sequence++;
            $baseCode = "{$typePrefix}{$sequence}M{$participant->student_id}";
        }

        return $baseCode;
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function outputs()
    {
        return $this->hasMany(ParticipantOutput::class, 'program_participant_id');
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

    public function sdgCategory()
    {
        return $this->belongsTo(SdgCategory::class);
    }
}
