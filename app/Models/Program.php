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
        'theme',
        'multidisciplinary_number',
        'problem_potential',
        'role_in_program',
        'responsibility',
        'target',
        'target_audience',
        'budget',
        'source_of_fund',
        'method',
        'output_target',
        'storyboard',
        'video_script',
        'timeline',
        'status',
        'revision_note',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
            'status' => ProgramStatus::class,
            'budget' => 'decimal:2',
        ];
    }

    /**
     * Get the group this program belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the student who proposed this program.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Check if this program can be edited.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [ProgramStatus::Draft, ProgramStatus::NeedsRevision]);
    }
}
