<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'dpl_id',
        'aspect_a',
        'aspect_b',
        'aspect_c',
        'final_grade',
        'grade_letter',
    ];

    protected function casts(): array
    {
        return [
            'aspect_a' => 'decimal:2',
            'aspect_b' => 'decimal:2',
            'aspect_c' => 'decimal:2',
            'final_grade' => 'decimal:2',
        ];
    }

    /**
     * Get the student being graded.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the DPL who gave the grade.
     */
    public function dpl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dpl_id');
    }

    /**
     * Calculate the final grade and letter from aspects.
     */
    public function calculateGrade(): void
    {
        $this->final_grade = round(($this->aspect_a + $this->aspect_b + $this->aspect_c) / 3, 2);
        $this->grade_letter = match (true) {
            $this->final_grade >= 85 => 'A',
            $this->final_grade >= 80 => 'AB',
            $this->final_grade >= 70 => 'B',
            $this->final_grade >= 65 => 'BC',
            $this->final_grade >= 60 => 'C',
            $this->final_grade >= 50 => 'D',
            default => 'E',
        };
    }
}
