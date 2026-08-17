<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParticipantOutput extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_participant_id',
        'output_code',
        'name',
        'type',
        'file_path',
        'url',
    ];

    public function participant()
    {
        return $this->belongsTo(ProgramParticipant::class, 'program_participant_id');
    }

    /**
     * Generate output code based on participant code and existing outputs.
     * e.g., if participant_code = 'M1M1', outputs will be 'LM1M1' for 1st, 
     * 'LM1M1A', 'LM1M1B' for subsequent (but if multiple, first becomes A).
     */
    public static function generateOutputCode(ProgramParticipant $participant, int $index, int $totalCount)
    {
        $baseCode = 'L' . $participant->participant_code;
        
        if ($totalCount <= 1) {
            return $baseCode;
        }

        $alphabet = range('A', 'Z');
        $suffix = '';
        if ($index < count($alphabet)) {
            $suffix = $alphabet[$index];
        } else {
            // In case there are more than 26 outputs, fall back to AA, AB, etc.
            $suffix = $alphabet[floor($index / 26) - 1] . $alphabet[$index % 26];
        }

        return $baseCode . $suffix;
    }
}
