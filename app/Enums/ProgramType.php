<?php

namespace App\Enums;

enum ProgramType: string
{
    case Multidisiplin = 'multidisiplin';
    case PengabdianMasyarakat = 'pengabdian_masyarakat';

    public function label(): string
    {
        return match ($this) {
            self::Multidisiplin => 'Multidisiplin',
            self::PengabdianMasyarakat => 'Pengabdian Masyarakat',
        };
    }
}
