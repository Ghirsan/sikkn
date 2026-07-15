<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramType;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyGroup extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group?->load([
            'dpls',
            'students',
            'period',
            'programs',
            'leadDpl',
            'studentLeader',
        ]);

        $members = $group?->students ?? collect();
        $dpls = $group?->dpls ?? collect();
        $programs = $group?->programs ?? collect();

        // Group programs by type
        $programsByType = $programs->groupBy('type');

        // Stats
        $stats = [
            'memberCount' => $members->count(),
            'dplCount' => $dpls->count(),
            'programCount' => $programs->count(),
            'lrkStatus' => $group?->is_lrk_locked ? 'Terkunci' : 'Terbuka',
            'lpkStatus' => $group?->is_lpk_locked ? 'Terkunci' : 'Terbuka',
        ];

        return view('livewire.mahasiswa.my-group', [
            'group' => $group,
            'members' => $members,
            'dpls' => $dpls,
            'period' => $group?->period,
            'programs' => $programs,
            'programsByType' => $programsByType,
            'stats' => $stats,
            'currentUser' => $user,
        ]);
    }
}
