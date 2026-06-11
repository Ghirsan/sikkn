<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramOutput extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'student_id',
        'output_code',
        'output_type',
        'title',
        'file_path',
        'url',
        'description',
    ];

    /**
     * Get the program this output belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the student who produced this output.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
