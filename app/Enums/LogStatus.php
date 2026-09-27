<?php

namespace App\Enums;

enum LogStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Pending => 'Menunggu Persetujuan',
            self::Approved => 'Disetujui',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Pending => 'amber',
            self::Approved => 'green',
        };
    }
    public function mentoringLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Pending => 'Menunggu Tanggapan',
            self::Approved => 'Ditanggapi',
        };
    }
}
