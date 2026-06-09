<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lpk extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'achievement',
        'obstacle',
        'solution',
        'realized_budget',
        'status',
        'revision_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProgramStatus::class,
            'realized_budget' => 'decimal:2',
        ];
    }

    /**
     * Get the program this LPK belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
