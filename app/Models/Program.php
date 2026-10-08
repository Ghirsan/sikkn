<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'student_id',
        'title',
        'program_type_id',
        'sequence',
    ];

    protected static function booted()
    {
        static::deleted(function ($program) {
            static::resequencePrograms($program->group_id, $program->programType?->code, $program->student_id);
        });
    }

    public static function resequencePrograms($groupId, $type, $studentId = null)
    {
        if (! $groupId || ! $type) {
            return;
        }

        // Ambil sisa program di kelompok dan tipe yang sama, urutkan dari yang pertama dibuat
        $query = static::where('group_id', $groupId)->whereType($type);

        if ($studentId) {
            $query->where('student_id', $studentId);
        } else {
            $query->whereNull('student_id');
        }

        $programs = $query->orderBy('id')->get();

        $seq = 1;
        foreach ($programs as $prog) {
            if ($prog->sequence !== $seq) {
                $prog->sequence = $seq;
                $prog->saveQuietly(); // Hindari trigger event update berulang jika ada

                // Perbarui kode partisipan yang berkaitan dengan program ini
                foreach ($prog->participants as $participant) {
                    $participant->setRelation('program', $prog);
                    $participant->participant_code = ProgramParticipant::generateParticipantCode($participant);
                    $participant->saveQuietly();
                }
            }
            $seq++;
        }
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

        $typePrefix = match ($this->programType?->code) {
            'multidisiplin' => 'M',
            'sosial_kemasyarakatan' => 'SK',
            'lainnya' => 'L',
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

    public function isVideoProfile(): bool
    {
        $title = $this->title;
        if (! $title) {
            return false;
        }

        return $this->programType?->code === 'multidisiplin' && (
            str_contains(strtolower($title), 'video')
        );
    }

    /**
     * Get the group this program belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the type of this program.
     */
    public function programType(): BelongsTo
    {
        return $this->belongsTo(ProgramType::class);
    }

    /**
     * Scope a query to only include programs of a given type by code.
     */
    public function scopeWhereType($query, $code)
    {
        return $query->whereHas('programType', function ($q) use ($code) {
            $q->where('code', $code);
        });
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
    public function participants(): HasMany
    {
        return $this->hasMany(ProgramParticipant::class);
    }
}
