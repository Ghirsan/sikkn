<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'dpl_id',
        'name',
        'village',
        'district',
        'regency',
        'province',
    ];

    /**
     * Get the period this group belongs to.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Get the DPL assigned to this group.
     */
    public function dpl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dpl_id');
    }

    /**
     * Get the students in this group.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the programs for this group.
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * Get the mentoring logs for this group.
     */
    public function mentoringLogs(): HasMany
    {
        return $this->hasMany(MentoringLog::class);
    }

    /**
     * Get the full location string.
     */
    public function getLocationAttribute(): string
    {
        return collect([$this->village, $this->district, $this->regency, $this->province])
            ->filter()
            ->implode(', ');
    }
}
