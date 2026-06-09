<?php

namespace App\Enums;

enum ProgramType: string
{
    case Multidisiplin = 'multidisiplin';
    case SosialKemasyarakatan = 'sosial_kemasyarakatan';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Multidisiplin => 'Multidisiplin',
            self::SosialKemasyarakatan => 'Sosial Kemasyarakatan',
            self::Lainnya => 'Lainnya',
        };
    }
}
