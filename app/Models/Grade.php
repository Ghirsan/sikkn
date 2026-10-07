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
        'pembekalan',
        'gelar_karya',
        'kehadiran',
        'lrk',
        'integritas',
        'sosial_kemasyarakatan',
        'lpk',
        'ujian_akhir',
        'final_grade',
        'grade_letter',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'pembekalan' => 'decimal:2',
            'gelar_karya' => 'decimal:2',
            'kehadiran' => 'decimal:2',
            'lrk' => 'decimal:2',
            'integritas' => 'decimal:2',
            'sosial_kemasyarakatan' => 'decimal:2',
            'lpk' => 'decimal:2',
            'ujian_akhir' => 'decimal:2',
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

    public function getAktivitasPartisipatifAttribute(): float
    {
        return $this->sosial_kemasyarakatan * 1.0;
    }

    public function getHasilProyekAttribute(): float
    {
        return $this->lrk * 1.0;
    }

    public function getTugasAttribute(): float
    {
        return ($this->kehadiran + $this->integritas) * 0.5;
    }

    public function getQuizAttribute(): float
    {
        return ($this->pembekalan + $this->gelar_karya) * 0.5;
    }

    public function getUtsAttribute(): float
    {
        return $this->lpk * 1.0;
    }

    public function getUasAttribute(): float
    {
        return $this->ujian_akhir * 1.0;
    }

    /**
     * Calculate the final grade and letter from aspects.
     */
    public function calculateGrade(): void
    {
        $this->final_grade = round(
            ($this->aktivitas_partisipatif * 0.30) +
            ($this->hasil_proyek * 0.25) +
            ($this->tugas * 0.20) +
            ($this->quiz * 0.05) +
            ($this->uts * 0.10) +
            ($this->uas * 0.10),
            2
        );

        $this->grade_letter = match (true) {
            $this->final_grade >= 80 => 'A',
            $this->final_grade >= 70 => 'B',
            $this->final_grade >= 60 => 'C',
            $this->final_grade >= 50 => 'D',
            default => 'E',
        };
    }
}
