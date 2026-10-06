<?php

namespace App\Models;

use App\Enums\LogStatus;
use App\Services\ExternalImagePreviewUrl;
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

    /**
     * Get the activities for this daily log.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(DailyLogActivity::class);
    }

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => LogStatus::class,
        ];
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
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http')) {
            try {
                return app(ExternalImagePreviewUrl::class)->resolve($this->image_path);
            } catch (\Exception $e) {
                return null; // fallback if resolving fails
            }
        }

        return asset('storage/'.$this->image_path);
    }
}
