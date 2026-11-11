<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Faculty extends Model
{
    protected $fillable = [
        'code',
        'name',
        'short_name',
    ];

    public function studyPrograms(): HasMany
    {
        return $this->hasMany(StudyProgram::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->users()->where('role', UserRole::Mahasiswa);
    }

    public function dpls(): HasMany
    {
        return $this->users()->where('role', UserRole::Dpl);
    }

    public function observerUsers(): HasMany
    {
        return $this->users()->where('role', UserRole::Fakultas);
    }

    public function observerUser(): HasOne
    {
        return $this->hasOne(User::class)->ofMany(
            ['id' => 'max'],
            fn (Builder $query) => $query->where('role', UserRole::Fakultas)
        );
    }

    /**
     * Build the default display name for a newly provisioned faculty account.
     */
    public function defaultObserverAccountName(): string
    {
        $name = trim($this->name);

        if (preg_match('/^(fakultas|sekolah)\b/i', $name)) {
            return $name;
        }

        return 'FAKULTAS '.$name;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('short_name', 'like', "%{$term}%");
        });
    }
}
