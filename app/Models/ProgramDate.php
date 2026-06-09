<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the program this date belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
