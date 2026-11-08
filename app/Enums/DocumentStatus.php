<?php

namespace App\Enums;

enum DocumentStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case NeedsRevision = 'needs_revision';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Diajukan',
            self::NeedsRevision => 'Perlu Revisi',
            self::Approved => 'Disetujui',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Submitted => 'amber',
            self::NeedsRevision => 'red',
            self::Approved => 'green',
        };
    }
}
