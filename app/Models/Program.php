<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'student_id',
        'title',
        'type',
        'theme',
        'multidisciplinary_number',
        'problem_potential',
        'target',
        'target_audience',
        'budget',
        'source_of_fund',
        'method',
        'output_target',
        'storyboard',
        'video_script',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProgramType::class,
            'budget' => 'decimal:2',
        ];
    }

    /**
     * Get the group this program belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the student who proposed this program (if it is a monodisiplin program).
     * For multidisciplin created by DPL, this is null.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the participants of this program.
     */
    public function participants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramParticipant::class);
    }

    /**
     * Get the dates for this program.
     */
    public function dates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramDate::class);
    }



    /**
     * Get the documentations for this program.
     */
    public function documentations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramDocumentation::class);
    }

    /**
     * Get the outputs for this program.
     */
    public function outputs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProgramOutput::class);
    }
}
