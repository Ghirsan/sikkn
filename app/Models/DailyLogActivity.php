<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLogActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_log_id',
        'start_time',
        'end_time',
        'activity_description',
        'image_path',
    ];

    /**
     * Get the log that owns this activity.
     */
    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }
}
