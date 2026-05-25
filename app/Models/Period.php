<?php

namespace App\Models;

use App\Enums\PeriodStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    use HasFactory;

    protected $fillable = [
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
            'semester' => \App\Enums\Semester::class,
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
     * Scope to active periods.
     */
    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Dynamically determine the status based on dates.
     */
    public function getStatusAttribute(): PeriodStatus
    {
        $now = now()->startOfDay();
        $start = $this->start_date->startOfDay();
        $end = $this->end_date->startOfDay();

        if ($now->lt($start)) {
            return PeriodStatus::Inactive;
        } elseif ($now->gt($end)) {
            return PeriodStatus::Completed;
        }

        return PeriodStatus::Active;
    }
}
