<?php

namespace App\Models;

use App\Enums\LogStatus;
use App\Services\ExternalImagePreviewUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'important_notes',
        'image_path',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => LogStatus::class,
        ];
    }

    /**
     * Get the activities for this daily log.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(DailyLogActivity::class);
    }

    /**
     * Get the student who wrote this log.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the resolved image URL.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->image_path) {
                    return null;
                }

                if (str_starts_with($this->image_path, 'http')) {
                    try {
                        return app(ExternalImagePreviewUrl::class)->resolve($this->image_path);
                    } catch (\Exception $e) {
                        return null;
                    }
                }

                return asset('storage/'.$this->image_path);
            }
        );
    }

    /**
     * Sum total duration across activities in minutes.
     */
    protected function totalMinutes(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->activities->sum('duration_minutes')
        );
    }

    /**
     * Total duration formatted (e.g. '3 jam 15 menit' or '3 jam').
     */
    protected function totalHoursFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                $totalMinutes = $this->total_minutes;
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;

                return $minutes > 0 ? "{$hours} jam {$minutes} menit" : "{$hours} jam";
            }
        );
    }

    public function isEditable(): bool
    {
        return $this->status !== LogStatus::Approved;
    }

    public function isApproved(): bool
    {
        return $this->status === LogStatus::Approved;
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LogStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LogStatus::Pending);
    }

    public function scopeForStudent(Builder $query, int $studentId): Builder
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForGroup(Builder $query, int $groupId): Builder
    {
        return $query->whereHas('student', function (Builder $q) use ($groupId) {
            $q->where('group_id', $groupId);
        });
    }

    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
