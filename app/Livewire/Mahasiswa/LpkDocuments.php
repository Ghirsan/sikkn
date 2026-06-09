<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LpkDocuments extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group;

        $programs = $group ? $group->programs()->with('student', 'lpk')->where('status', ProgramStatus::Approved)->get() : collect();
        $totalPrograms = $group ? $group->programs()->count() : 0;
        
        $lpkApprovedCount = $programs->where(function ($program) {
            return $program->lpk && $program->lpk->status === ProgramStatus::Approved;
        })->count();
        
        $allLpkApproved = $totalPrograms > 0 && $lpkApprovedCount === $totalPrograms;

        // Note: It's important to know if all LRK are approved first, because LPK execution happens AFTER LRK is approved.
        $approvedCount = $programs->count();
        $allApproved = $totalPrograms > 0 && $approvedCount === $totalPrograms;

        return view('livewire.mahasiswa.lpk-documents', [
            'group' => $group,
            'programs' => $programs,
            'totalPrograms' => $totalPrograms,
            'lpkApprovedCount' => $lpkApprovedCount,
            'allLpkApproved' => $allLpkApproved,
            'allApproved' => $allApproved,
        ]);
    }
}
