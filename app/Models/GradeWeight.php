<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'weight',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
        ];
    }

    protected static ?array $cachedWeights = null;

    protected static ?array $cachedPercentages = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::flushCache();
        });

        static::deleted(function () {
            static::flushCache();
        });
    }

    /**
     * Flush in-memory cached weights.
     */
    public static function flushCache(): void
    {
        static::$cachedWeights = null;
        static::$cachedPercentages = null;
    }

    /**
     * Default weights for the SIAP grading components (as multipliers, e.g. 0.30 = 30%).
     *
     * @return array<string, float>
     */
    public static function defaultWeights(): array
    {
        return [
            'aktivitas_partisipatif' => 0.30,
            'hasil_proyek' => 0.25,
            'tugas' => 0.20,
            'quiz' => 0.05,
            'uts' => 0.10,
            'uas' => 0.10,
        ];
    }

    /**
     * Get weight as a multiplier (0.0 to 1.0) regardless of whether stored as percentage or fraction.
     */
    public function getMultiplierAttribute(): float
    {
        $w = (float) $this->weight;

        return $w > 1.0 ? round($w / 100, 4) : $w;
    }

    /**
     * Get weight as a percentage (0 to 100).
     */
    public function getPercentageAttribute(): float
    {
        $w = (float) $this->weight;

        return $w <= 1.0 ? round($w * 100, 2) : $w;
    }

    /**
     * Get all component multipliers keyed by code.
     *
     * @return array<string, float>
     */
    public static function getWeights(bool $fresh = false): array
    {
        if (static::$cachedWeights !== null && ! $fresh) {
            return static::$cachedWeights;
        }

        $defaults = static::defaultWeights();

        try {
            $weights = static::all()
                ->mapWithKeys(function (GradeWeight $weight) {
                    return [$weight->code => $weight->multiplier];
                })
                ->toArray();

            return static::$cachedWeights = array_merge($defaults, $weights);
        } catch (\Throwable) {
            return $defaults;
        }
    }

    /**
     * Get all component percentages (0-100) keyed by code.
     *
     * @return array<string, float>
     */
    public static function getPercentages(bool $fresh = false): array
    {
        if (static::$cachedPercentages !== null && ! $fresh) {
            return static::$cachedPercentages;
        }

        $weights = static::getWeights($fresh);

        return static::$cachedPercentages = array_map(fn ($val) => round($val * 100, 2), $weights);
    }
}
