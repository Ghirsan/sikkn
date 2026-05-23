<?php

namespace App\Enums;

enum ProgramStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case NeedsRevision = 'needs_revision';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Direview DPL',
            self::NeedsRevision => 'Perlu Revisi',
            self::Approved => 'Disetujui DPL',
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
