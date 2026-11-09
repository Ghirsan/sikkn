<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\ProgramStatus;
use App\Enums\UserRole;
use App\Services\ExternalImagePreviewUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
        'lrk_status',
        'lrk_notes',
        'lrk_submitted_at',
        'lrk_reviewed_at',
        'is_lrk_locked',
        'is_lpk_locked',
        'location_map_path',
        'program_multidisiplin_text',
        'program_sosmas_text',
        'program_lainnya_text',
        'storyboard_text',
        'storyboard_scenes',
        'video_script_text',
        'video_script_scenes',
        'survey_documentation_text',
        'location_map_text',
        'macro_schedules',
        'survey_document_path',
        'start_date',
        'end_date',
        'student_leader_id',
        'lead_dpl_id',
    ];

    protected function casts(): array
    {
        return [
            'lrk_status' => DocumentStatus::class,
            'lrk_submitted_at' => 'datetime',
            'lrk_reviewed_at' => 'datetime',
            'is_lrk_locked' => 'boolean',
            'is_lpk_locked' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'storyboard_scenes' => 'array',
            'video_script_scenes' => 'array',
            'macro_schedules' => 'array',
        ];
    }

    public function getWeeksCount(): int
    {
        $start = $this->effective_start_date;
        $end = $this->effective_end_date;

        if (! $start || ! $end) {
            return 6;
        }

        $startDate = Carbon::parse($start)->startOfWeek(Carbon::MONDAY);
        $endDate = Carbon::parse($end)->endOfWeek(Carbon::SUNDAY);

        if ($endDate->lt($startDate)) {
            return 6;
        }

        $days = $startDate->diffInDays($endDate) + 1;
        $weeks = (int) round($days / 7);

        return max(1, min(16, $weeks));
    }

    public function getWeekKeys(): array
    {
        $count = $this->getWeeksCount();
        $keys = [];
        for ($i = 1; $i <= $count; $i++) {
            $keys[] = 'm'.$i;
        }

        return $keys;
    }

    public static function defaultMacroSchedules(?int $weeksCount = 6): array
    {
        $weeksCount = max(1, $weeksCount ?? 6);

        $makeRow = function (int $no, string $kegiatan, array $activeWeeks) use ($weeksCount) {
            $row = [
                'no' => $no,
                'kegiatan' => $kegiatan,
            ];
            for ($w = 1; $w <= $weeksCount; $w++) {
                $row['m'.$w] = in_array($w, $activeWeeks, true);
            }

            return $row;
        };

        $surveyWeeks = [1];
        $lrkWeeks = $weeksCount >= 2 ? [1, 2] : [1];

        if ($weeksCount <= 2) {
            $pelaksanaanWeeks = range(1, $weeksCount);
            $lpkWeeks = [$weeksCount];
            $monevWeeks = [$weeksCount];
        } else {
            $pelaksanaanWeeks = range(2, max(2, $weeksCount - 1));
            $lpkWeeks = [$weeksCount - 1, $weeksCount];
            $monevWeeks = range(max(2, $weeksCount - 2), max(2, $weeksCount - 1));
        }

        return [
            $makeRow(1, 'Survey Pendahuluan', $surveyWeeks),
            $makeRow(2, 'Penyusunan Proposal / LRK', $lrkWeeks),
            $makeRow(3, 'Pelaksanaan Program Kegiatan KKN', $pelaksanaanWeeks),
            $makeRow(4, 'Pelaporan & Penyusunan LPK', $lpkWeeks),
            $makeRow(5, 'Monev & Gelar Karya', $monevWeeks),
        ];
    }

    public static function defaultStoryboardScenes(): array
    {
        return [
            ['scene' => '1', 'visual' => 'Opening lanskap desa (aerial/drone shot) & judul video profil', 'audio' => 'Backsound musik instrumental kontemporer lembut', 'duration' => '10s'],
            ['scene' => '2', 'visual' => 'Sambutan hangat Kepala Desa & penayangan peta wilayah desa', 'audio' => 'Voice Over profil singkat dan potensi wilayah desa', 'duration' => '15s'],
            ['scene' => '3', 'visual' => 'Aktivitas mata pencaharian warga, UMKM, dan kearifan lokal', 'audio' => 'Narasi potensi ekonomi desa & wawancara warga lokal', 'duration' => '20s'],
            ['scene' => '4', 'visual' => 'Kontribusi kegiatan KKN Undip bersama masyarakat & penutup', 'audio' => 'Closing statement & ucapan terima kasih', 'duration' => '15s'],
        ];
    }

    public static function defaultVideoScriptScenes(): array
    {
        return [
            ['scene' => '1 (00:00 - 00:10)', 'visual' => 'Opening pemandangan alam desa dengan teks Selamat Datang', 'audio' => 'Selamat datang di Desa ..., perpaduan keasrian alam dan kehangatan warganya.'],
            ['scene' => '2 (00:10 - 00:25)', 'visual' => 'Kepala Desa memaparkan potensi desa di kantor balai desa', 'audio' => 'Kepala Desa: "Kami menyambut baik inisiatif mahasiswa KKN Undip dalam memajukan desa kami."'],
            ['scene' => '3 (00:25 - 00:45)', 'visual' => 'Sorotan produk unggulan UMKM dan keindahan lanskap desa', 'audio' => 'Narator: "Potensi unggulan desa ini terletak pada sektor pertanian dan UMKM kreatif masyarakat."'],
            ['scene' => '4 (00:45 - 01:00)', 'visual' => 'Kolaborasi warga dan mahasiswa, ditutup logo resmi Undip', 'audio' => 'Narator: "Bersama membangun desa berdaya saing. KKN Universitas Diponegoro mengabdi untuk negeri."'],
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
        if ($this->is_lrk_locked) {
            return false;
        }

        $status = $this->lrk_status ?? DocumentStatus::Draft;

        return in_array($status, [DocumentStatus::Draft, DocumentStatus::NeedsRevision], true);
    }

    public function isLpkEditable(): bool
    {
        return ! $this->is_lpk_locked;
    }

    public function isLrkFinalized(): bool
    {
        return (bool) $this->is_lrk_locked || $this->lrk_status === DocumentStatus::Approved;
    }

    public function isLrkSubmitted(): bool
    {
        return $this->lrk_status === DocumentStatus::Submitted;
    }

    public function isLrkNeedsRevision(): bool
    {
        return $this->lrk_status === DocumentStatus::NeedsRevision;
    }

    public function isLrkDraft(): bool
    {
        return $this->lrk_status === null || $this->lrk_status === DocumentStatus::Draft;
    }

    public function isLpkFinalized(): bool
    {
        return (bool) $this->is_lpk_locked;
    }

    public function isLrkReady(): bool
    {
        $participants = ProgramParticipant::whereHas('program', fn ($q) => $q->where('group_id', $this->id))
            ->with('program.programType')
            ->get();

        $total = $participants->count();
        if ($total === 0) {
            return false;
        }

        $approved = $participants->where('status', ProgramStatus::Approved)->count();
        if ($approved !== $total) {
            return false;
        }

        $students = $this->students;
        foreach ($students as $student) {
            $studentParticipants = $participants->where('student_id', $student->id);
            $hasSosmas = $studentParticipants->contains(fn ($p) => $p->program?->programType?->code === 'sosial_kemasyarakatan');
            $hasLainnya = $studentParticipants->contains(fn ($p) => $p->program?->programType?->code === 'lainnya');
            $multidisiplinCount = $studentParticipants->filter(fn ($p) => $p->program?->programType?->code === 'multidisiplin')->count();

            if (! ($hasSosmas && $hasLainnya && $multidisiplinCount >= 2)) {
                return false;
            }
        }

        return true;
    }

    public function isLpkReady(): bool
    {
        $participants = ProgramParticipant::whereHas('program', fn ($q) => $q->where('group_id', $this->id))
            ->get();

        $total = $participants->count();
        if ($total === 0) {
            return false;
        }

        $lrkApproved = $participants->where('status', ProgramStatus::Approved)->count();
        $lpkApproved = $participants->where('lpk_status', ProgramStatus::Approved)->count();

        return $lrkApproved === $total && $lpkApproved === $total;
    }

    public function locationMapUrl(): ?string
    {
        if (! $this->location_map_path) {
            return null;
        }

        if (! filter_var($this->location_map_path, FILTER_VALIDATE_URL)) {
            return asset('storage/'.$this->location_map_path);
        }

        try {
            return app(ExternalImagePreviewUrl::class)->resolve($this->location_map_path);
        } catch (\InvalidArgumentException) {
            return $this->location_map_path;
        }
    }

    public function surveyDocumentUrl(): ?string
    {
        if (! $this->survey_document_path) {
            return null;
        }

        if (! filter_var($this->survey_document_path, FILTER_VALIDATE_URL)) {
            return asset('storage/'.$this->survey_document_path);
        }

        try {
            return app(ExternalImagePreviewUrl::class)->resolve($this->survey_document_path);
        } catch (\InvalidArgumentException) {
            return $this->survey_document_path;
        }
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
