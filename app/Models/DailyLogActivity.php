<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
    ];

    /**
     * Get the log that owns this activity.
     */
    public function dailyLog(): BelongsTo
    {
        return $this->belongsTo(DailyLog::class);
    }

    /**
     * Calculate duration of the activity in minutes.
     */
    protected function durationMinutes(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->start_time || ! $this->end_time) {
                    return 0;
                }

                $start = Carbon::parse($this->start_time);
                $end = Carbon::parse($this->end_time);

                return (int) max(0, $start->diffInMinutes($end));
            }

        );
    }

    /**
     * Format duration as human-readable (e.g. '2 jam 30 menit' or '1 jam').
     */
    protected function durationFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $minutes = $this->duration_minutes;
                $hours = floor($minutes / 60);
                $remaining = $minutes % 60;

                if ($hours > 0 && $remaining > 0) {
                    return "{$hours} jam {$remaining} menit";
                } elseif ($hours > 0) {
                    return "{$hours} jam";
                }

                return "{$remaining} menit";
            }
        );
    }

    /**
     * Format time range (e.g. '08:00 - 10:00').
     */
    protected function timeRange(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->start_time || ! $this->end_time) {
                    return '-';
                }

                $start = Carbon::parse($this->start_time)->format('H:i');
                $end = Carbon::parse($this->end_time)->format('H:i');

                return "{$start} - {$end}";
            }
        );
    }
}
