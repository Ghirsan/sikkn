<?php

namespace App\Models;

use App\Enums\LogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'date',
        'duration_minutes',
        'activity_description',
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
     * Get the student who wrote this log.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
