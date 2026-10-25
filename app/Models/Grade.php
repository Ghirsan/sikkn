<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'dpl_id',
        'group_id',
        'pembekalan',
        'gelar_karya',
        'kehadiran',
        'lrk',
        'integritas',
        'sosial_kemasyarakatan',
        'lpk',
        'ujian_akhir',
        'aktivitas_partisipatif',
        'hasil_proyek',
        'tugas',
        'quiz',
        'uts',
        'uas',
        'final_grade',
        'grade_letter',
        'keterangan',
    ];

    protected static function booted(): void
    {
        static::saving(function (Grade $grade) {
            if (! $grade->group_id && $grade->student_id) {
                $grade->group_id = $grade->student?->group_id;
            }
        });
    }

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
            'aktivitas_partisipatif' => 'decimal:2',
            'hasil_proyek' => 'decimal:2',
            'tugas' => 'decimal:2',
            'quiz' => 'decimal:2',
            'uts' => 'decimal:2',
            'uas' => 'decimal:2',
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
     * Get the group this grade belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function getAktivitasPartisipatifAttribute(): float
    {
        if (isset($this->attributes['aktivitas_partisipatif']) && $this->attributes['aktivitas_partisipatif'] !== null) {
            return (float) $this->attributes['aktivitas_partisipatif'];
        }

        return round((float) ($this->sosial_kemasyarakatan ?? 0) * 1.0, 2);
    }

    public function getHasilProyekAttribute(): float
    {
        if (isset($this->attributes['hasil_proyek']) && $this->attributes['hasil_proyek'] !== null) {
            return (float) $this->attributes['hasil_proyek'];
        }

        return round((float) ($this->lrk ?? 0) * 1.0, 2);
    }

    public function getTugasAttribute(): float
    {
        if (isset($this->attributes['tugas']) && $this->attributes['tugas'] !== null) {
            return (float) $this->attributes['tugas'];
        }

        return round(((float) ($this->kehadiran ?? 0) + (float) ($this->integritas ?? 0)) * 0.5, 2);
    }

    public function getQuizAttribute(): float
    {
        if (isset($this->attributes['quiz']) && $this->attributes['quiz'] !== null) {
            return (float) $this->attributes['quiz'];
        }

        return round(((float) ($this->pembekalan ?? 0) + (float) ($this->gelar_karya ?? 0)) * 0.5, 2);
    }

    public function getUtsAttribute(): float
    {
        if (isset($this->attributes['uts']) && $this->attributes['uts'] !== null) {
            return (float) $this->attributes['uts'];
        }

        return round((float) ($this->lpk ?? 0) * 1.0, 2);
    }

    public function getUasAttribute(): float
    {
        if (isset($this->attributes['uas']) && $this->attributes['uas'] !== null) {
            return (float) $this->attributes['uas'];
        }

        return round((float) ($this->ujian_akhir ?? 0) * 1.0, 2);
    }

    /**
     * Calculate and persist the 6 SIAP components, final grade, and letter from aspects and global weights.
     */
    public function calculateGrade(): void
    {
        $this->aktivitas_partisipatif = round((float) $this->sosial_kemasyarakatan * 1.0, 2);
        $this->hasil_proyek = round((float) $this->lrk * 1.0, 2);
        $this->tugas = round(((float) $this->kehadiran + (float) $this->integritas) * 0.5, 2);
        $this->quiz = round(((float) $this->pembekalan + (float) $this->gelar_karya) * 0.5, 2);
        $this->uts = round((float) $this->lpk * 1.0, 2);
        $this->uas = round((float) $this->ujian_akhir * 1.0, 2);

        $weights = GradeWeight::getWeights();

        $this->final_grade = round(
            ($this->aktivitas_partisipatif * ($weights['aktivitas_partisipatif'] ?? 0.30)) +
            ($this->hasil_proyek * ($weights['hasil_proyek'] ?? 0.25)) +
            ($this->tugas * ($weights['tugas'] ?? 0.20)) +
            ($this->quiz * ($weights['quiz'] ?? 0.05)) +
            ($this->uts * ($weights['uts'] ?? 0.10)) +
            ($this->uas * ($weights['uas'] ?? 0.10)),
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

    public function isPassing(): bool
    {
        return $this->final_grade >= 50 && $this->grade_letter !== 'E';
    }

    public function scopeForDpl(Builder $query, int $dplId): Builder
    {
        return $query->where('dpl_id', $dplId);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function scopePassed(Builder $query): Builder
    {
        return $query->where('final_grade', '>=', 50)->where('grade_letter', '!=', 'E');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('final_grade', '<', 50)->orWhere('grade_letter', 'E');
        });
    }

    public function scopeForStudyProgram(Builder $query, int $studyProgramId): Builder
    {
        return $query->whereHas('student', function (Builder $q) use ($studyProgramId) {
            $q->where('study_program_id', $studyProgramId);
        });
    }

    public function scopeForFaculty(Builder $query, int $facultyId): Builder
    {
        return $query->whereHas('student', function (Builder $q) use ($facultyId) {
            $q->where('faculty_id', $facultyId);
        });
    }
}
