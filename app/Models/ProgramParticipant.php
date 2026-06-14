<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramParticipant extends Model
{
    protected $fillable = [
        'program_id',
        'student_id',
        'role_in_program',
        'responsibility',
        'timeline',
        'status',
        'revision_note',
        'achievement',
        'obstacle',
        'solution',
        'actual_result',
        'realized_budget',
        'lpk_status',
        'lpk_revision_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ProgramStatus::class,
            'lpk_status' => \App\Enums\ProgramStatus::class,
            'realized_budget' => 'decimal:2',
        ];
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
