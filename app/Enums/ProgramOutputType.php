<?php

namespace App\Enums;

enum ProgramOutputType: string
{
    case Pdf = 'pdf';
    case Video = 'video';
    case Image = 'image';
    case Lainnya = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Pdf => 'PDF',
            self::Video => 'Video',
            self::Image => 'Gambar',
            self::Lainnya => 'Lainnya',
        };
    }
}
