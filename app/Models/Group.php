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
        'name',
        'type',
        'village',
        'district',
        'regency',
        'province',
        'partner_name',
        'village_head',
        'background',
        'lpk_background',
        'location_map_path',
    ];

    protected function casts(): array
    {
        return [
            'type' => \App\Enums\GroupType::class,
        ];
    }

    /**
     * Get the period this group belongs to.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * Get the DPLs assigned to this group.
     */
    public function dpls(): HasMany
    {
        return $this->hasMany(User::class)->where('role', \App\Enums\UserRole::Dpl);
    }

    /**
     * Get the students in this group.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', \App\Enums\UserRole::Mahasiswa);
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
     * Get the schedule events for this group.
     */
    public function scheduleEvents(): HasMany
    {
        return $this->hasMany(ScheduleEvent::class);
    }

    /**
     * Get the survey documents for this group.
     */
    public function surveyDocuments(): HasMany
    {
        return $this->hasMany(SurveyDocument::class);
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
