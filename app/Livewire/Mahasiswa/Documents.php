<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Documents extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group;

        $programs = $group ? $group->programs()->with('student')->where('status', ProgramStatus::Approved)->get() : collect();
        $totalPrograms = $group ? $group->programs()->count() : 0;
        $approvedCount = $programs->count();
        $allApproved = $totalPrograms > 0 && $approvedCount === $totalPrograms;

        return view('livewire.mahasiswa.documents', [
            'group' => $group,
            'programs' => $programs,
            'totalPrograms' => $totalPrograms,
            'approvedCount' => $approvedCount,
            'allApproved' => $allApproved,
        ]);
    }
}
