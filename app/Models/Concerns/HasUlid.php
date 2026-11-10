<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUlid
{
    /**
     * Boot the trait to automatically generate a ULID when creating a new record.
     */
    protected static function bootHasUlid(): void
    {
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    /**
     * Scope a query to find a model by its ULID.
     */
    public function scopeWhereUlid($query, string $ulid)
    {
        return $query->where('ulid', $ulid);
    }
}

