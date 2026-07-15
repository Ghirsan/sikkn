<?php

namespace App\Models;

use App\Enums\LogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentoringLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'student_id',
        'program_id',
        'date',
        'topic',
        'discussion_summary',
        'dpl_feedback',
        'status',
        'target_group',
        'student_count',
        'output',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => LogStatus::class,
        ];
    }

    /**
     * Get the group this log belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the student who wrote this log.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the program associated with this log.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
}
