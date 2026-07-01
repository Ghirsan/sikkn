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
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
        ];
    }

    protected static function booted()
    {
        static::deleted(function ($program) {
            static::resequencePrograms($program->group_id, $program->type, $program->student_id);
        });
    }

    public static function resequencePrograms($groupId, $type, $studentId = null)
    {
        if (!$groupId || !$type) return;

        // Ambil sisa program di kelompok dan tipe yang sama, urutkan dari yang pertama dibuat
        $query = static::where('group_id', $groupId)->where('type', $type);
        
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
