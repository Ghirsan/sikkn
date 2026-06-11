<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramDocumentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'image_path',
        'caption',
        'sort_order',
    ];

    /**
     * Get the program this documentation belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
