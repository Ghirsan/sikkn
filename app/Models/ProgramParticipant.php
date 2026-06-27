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
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ProgramStatus::class,
            'lpk_status' => \App\Enums\ProgramStatus::class,
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

        $sequence = $program->sequence ?? 1;
        return "{$typePrefix}{$sequence}M{$participant->student_id}";
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
