<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'period_id',
        'name',
        'group_type_id',
        'village',
        'district',
        'regency',
        'province',
        'partner_name',
        'village_head',
        'background',
        'is_lrk_locked',
        'is_lpk_locked',
        'location_map_path',
        'program_multidisiplin_text',
        'program_sosmas_text',
        'program_lainnya_text',
        'storyboard_text',
        'video_script_text',
        'survey_documentation_text',
        'location_map_text',
        'survey_document_path',
        'start_date',
        'end_date',
        'student_leader_id',
        'lead_dpl_id',
    ];

    protected function casts(): array
    {
        return [
            'is_lrk_locked' => 'boolean',
            'is_lpk_locked' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
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
     * Get the type of this group.
     */
    public function groupType(): BelongsTo
    {
        return $this->belongsTo(GroupType::class);
    }

    /**
     * Get the DPLs assigned to this group.
     */
    public function dpls(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'dpl_group', 'group_id', 'dpl_id');
    }

    /**
     * Get the students in this group.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class)->where('role', UserRole::Mahasiswa);
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
     * Get the lead DPL assigned to this group.
     */
    public function leadDpl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_dpl_id');
    }

    /**
     * Get the student leader of this group.
     */
    public function studentLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_leader_id');
    }

    /**
     * Get all grades for students in this group.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class, 'group_id');
    }

    /**
     * Get the full location string.
     */
    protected function location(): Attribute
    {
        return Attribute::make(
            get: fn () => collect([$this->village, $this->district, $this->regency, $this->province])
                ->filter()
                ->implode(', ')
        );
    }

    /**
     * Get effective start date, falling back to period start date.
     */
    protected function effectiveStartDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date ?? $this->period?->start_date
        );
    }

    /**
     * Get effective end date, falling back to period end date.
     */
    protected function effectiveEndDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_date ?? $this->period?->end_date
        );
    }

    public function isLrkEditable(): bool
    {
        return ! $this->is_lrk_locked;
    }

    public function isLpkEditable(): bool
    {
        return ! $this->is_lpk_locked;
    }

    public function scopeForPeriod(Builder $query, int $periodId): Builder
    {
        return $query->where('period_id', $periodId);
    }

    public function scopeWithRoster(Builder $query): Builder
    {
        return $query->with(['period', 'groupType', 'leadDpl', 'studentLeader', 'dpls']);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('village', 'like', "%{$term}%")
                ->orWhere('district', 'like', "%{$term}%")
                ->orWhere('regency', 'like', "%{$term}%")
                ->orWhere('partner_name', 'like', "%{$term}%");
        });
    }

    public function scopeLrkLocked(Builder $query, bool $locked = true): Builder
    {
        return $query->where('is_lrk_locked', $locked);
    }

    public function scopeLpkLocked(Builder $query, bool $locked = true): Builder
    {
        return $query->where('is_lpk_locked', $locked);
    }

    /**
     * Check if this group belongs to the current period and can be graded.
     */
    public function isGradingOpen(): bool
    {
        return $this->period?->isCurrent() ?? false;
    }
}
