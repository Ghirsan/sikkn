<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'student_id',
        'title',
        'type',
        'sequence',
        'execution_date',
        'problem_potential',
        'location',
        'target_audience',
        'method',
        'output_target',
        'storyboard',
        'video_script',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
            'execution_date' => 'date',
        ];
    }

    /**
     * Get the formatted program code.
     * Format: {Type}{Sequence}M{Student_ID}
     * Example: M1M1, SK1M2, L1M3
     */
    public function getProgramCodeFor(?int $studentId = null): string
    {
        if (! $studentId) {
            $studentId = $this->student_id;
        }

        // Jika student_id di tabel programs kosong (seperti pada program Multidisiplin)
        // maka otomatis mengambil ID user yang sedang login (mahasiswa terkait).
        if (! $studentId && auth()->check()) {
            $studentId = auth()->id();
        }

        if ($studentId) {
            $participant = $this->participants()->where('student_id', $studentId)->first();
            if ($participant && $participant->participant_code) {
                return $participant->participant_code;
            }
        }

        $typePrefix = match ($this->type) {
            ProgramType::Multidisiplin => 'M',
            ProgramType::SosialKemasyarakatan => 'SK',
            ProgramType::Lainnya => 'L',
            default => 'X',
        };

        $sequence = $this->sequence ?? 1;
        $studentId = $studentId ?? 0;

        return "{$typePrefix}{$sequence}M{$studentId}";
    }

    public function getProgramCodeAttribute(): string
    {
        return $this->getProgramCodeFor();
    }

    /**
     * Get the group this program belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the student who proposed this program (if it is a monodisiplin program).
     * For multidisciplin created by DPL, this is null.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the participants of this program.
     */
    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramParticipant::class);
    }





    /**
     * Get the documentations for this program.
     */
    public function documentations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramDocumentation::class);
    }

    /**
     * Get the outputs for this program.
     */
    public function outputs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramOutput::class);
    }
}
