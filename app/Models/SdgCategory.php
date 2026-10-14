<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SdgCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'code' => 'integer',
        ];
    }

    public function programParticipants(): HasMany
    {
        return $this->hasMany(ProgramParticipant::class);
    }

    public function label(): string
    {
        return $this->name;
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('code');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%");
        });
    }
}
