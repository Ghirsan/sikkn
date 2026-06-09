<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'title',
        'date',
        'week_number',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get the group this event belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
