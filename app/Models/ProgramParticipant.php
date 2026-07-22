<?php

namespace App\Models;

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
        'lpk_revision_note',
        'execution_date',
        'problem_potential',
        'location',
        'method',
        'target_audience',
        'output_target',
        'execution_description',
        'documentation_image_path',
        'documentation_caption',
        'output_type',
        'output_title',
        'output_file_path',
        'output_url',
        'output_description',
        'sdg_category',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ProgramStatus::class,
            'lpk_status' => \App\Enums\ProgramStatus::class,
            'execution_date' => 'date',
            'sdg_category' => \App\Enums\SdgCategory::class,
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
        $program = $participant->program ?? Program::find($participant->program_id);
        if (!$program || !$participant->student_id) {
            return null;
        }

        $typePrefix = match ($program->type) {
            \App\Enums\ProgramType::Multidisiplin => 'M',
            \App\Enums\ProgramType::SosialKemasyarakatan => 'SK',
            \App\Enums\ProgramType::Lainnya => 'L',
            default => 'X',
        };

        // Count existing participants for this student + type to determine sequence
        $existingCount = static::whereHas('program', function ($q) use ($program) {
                $q->where('type', $program->type);
                if ($program->type === \App\Enums\ProgramType::Multidisiplin) {
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

    public function isEditable(): bool
    {
        return in_array($this->status, [\App\Enums\ProgramStatus::Draft, \App\Enums\ProgramStatus::NeedsRevision]);
    }
}
