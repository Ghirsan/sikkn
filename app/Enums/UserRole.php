<?php

namespace App\Enums;

enum UserRole: string
{
    case Mahasiswa = 'mahasiswa';
    case Dpl = 'dpl';
    case P2kkn = 'p2kkn';
    case Prodi = 'prodi';
    case Fakultas = 'fakultas';

    /**
     * Get a human-readable label for the role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Mahasiswa => 'Mahasiswa',
            self::Dpl => 'Dosen Pembimbing Lapangan',
            self::P2kkn => 'Admin P2KKN',
            self::Prodi => 'Program Studi',
            self::Fakultas => 'Fakultas',
        };
    }

    /**
     * Get the short label for the role.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::Mahasiswa => 'Mahasiswa',
            self::Dpl => 'DPL',
            self::P2kkn => 'P2KKN',
            self::Prodi => 'Prodi',
            self::Fakultas => 'Fakultas',
        };
    }

    /**
     * Get the badge color for the role (for Flux UI).
     */
    public function color(): string
    {
        return match ($this) {
            self::Mahasiswa => 'blue',
            self::Dpl => 'green',
            self::P2kkn => 'amber',
            self::Prodi => 'purple',
            self::Fakultas => 'rose',
        };
    }

    /**
     * Get the icon name for the role.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Mahasiswa => 'academic-cap',
            self::Dpl => 'user-circle',
            self::P2kkn => 'shield-check',
            self::Prodi => 'building-library',
            self::Fakultas => 'building-office-2',
        };
    }

    /**
     * Check if this role is an administrative role.
     */
    public function isAdmin(): bool
    {
        return in_array($this, [self::P2kkn, self::Prodi, self::Fakultas]);
    }

    /**
     * Get the dashboard route name for this role.
     */
    public function dashboardRoute(): string
    {
        return 'dashboard';
    }
}
