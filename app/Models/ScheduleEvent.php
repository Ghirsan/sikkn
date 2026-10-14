<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'program_id',
        'student_id',
        'title',
        'date',
        'week_number',
        'description',
        'event_code',
        'is_realized',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_realized' => 'boolean',
            'week_number' => 'integer',
        ];
    }

    /**
     * Get the group this event belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the program this event is for.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the student responsible for this event.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function scopeRealized(Builder $query, bool $realized = true): Builder
    {
        return $query->where('is_realized', $realized);
    }

    public function scopeForWeek(Builder $query, int $week): Builder
    {
        return $query->where('week_number', $week);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->where('group_id', $groupId);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForProgram(Builder $query, int $programId): Builder
    {
        return $query->where('program_id', $programId);
    }
}
