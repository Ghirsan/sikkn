<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use App\Enums\Semester;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'year',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'semester' => Semester::class,
            'year' => 'integer',
        ];
    }

    /**
     * Get the groups for this period.
     */
    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Get a formatted display name for the period.
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name
                ? $this->name.' ('.$this->semester->value.' '.$this->year.')'
                : 'Semester '.$this->semester->value.' '.$this->year,
        );
    }

    /**
     * Dynamically determine the status based on dates.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                $now = now()->startOfDay();
                $start = $this->start_date?->startOfDay();
                $end = $this->end_date?->startOfDay();

                if (! $start || ! $end) {
                    return PeriodStatus::Inactive;
                }

                if ($now->lt($start)) {
                    return PeriodStatus::Inactive;
                } elseif ($now->gt($end)) {
                    return PeriodStatus::Completed;
                }

                return PeriodStatus::Active;
            }
        );
    }

    /**
     * Scope to active periods.
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    /**
     * Scope to inactive / upcoming periods.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now()->toDateString());
    }

    /**
     * Scope to completed periods.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    /**
     * Scope to order by descending start date.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('start_date');
    }

    /**
     * Get the current period.
     * The current period is the latest period ordered by start_date and id.
     */
    public static function current(): ?self
    {
        return static::orderByDesc('start_date')->orderByDesc('id')->first();
    }

    /**
     * Determine if this period is the current period.
     */
    public function isCurrent(): bool
    {
        $current = static::current();

        return $current !== null && $current->id === $this->id;
    }
}
