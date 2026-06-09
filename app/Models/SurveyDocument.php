<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'image_path',
        'caption',
        'sort_order',
    ];

    /**
     * Get the group this document belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
