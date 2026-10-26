<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Active period
        $period = Period::active()->first() ?? Period::current();

        // Total groups in active period (or all if no period)
        $totalGroups = Group::when($period, fn ($q) => $q->where('period_id', $period->id))->count();

        // Total students assigned in active period
        $totalStudents = User::mahasiswa()
            ->when($period, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('period_id', $period->id)))
            ->count();

        // Total DPLs assigned in active period
        $totalDpls = User::dpl()
            ->when($period, fn ($q) => $q->whereHas('dplGroups', fn ($g) => $g->where('period_id', $period->id)))
            ->count();

        // Document stats
        $lrkLockedGroups = Group::when($period, fn ($q) => $q->where('period_id', $period->id))
            ->where('is_lrk_locked', true)
            ->count();

        $lpkLockedGroups = Group::when($period, fn ($q) => $q->where('period_id', $period->id))
            ->where('is_lpk_locked', true)
            ->count();

        // Total proposed programs in active period
        $totalPrograms = Program::when($period, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('period_id', $period->id)))->count();

        return view('livewire.admin.dashboard', [
            'user' => $user,
            'period' => $period,
            'totalGroups' => $totalGroups,
            'totalStudents' => $totalStudents,
            'totalDpls' => $totalDpls,
            'lrkLockedGroups' => $lrkLockedGroups,
            'lpkLockedGroups' => $lpkLockedGroups,
            'totalPrograms' => $totalPrograms,
        ]);
    }
}
