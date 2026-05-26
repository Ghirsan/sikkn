<?php

namespace App\Livewire\Dpl;

use App\Enums\ProgramStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamDocuments extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group?->load(['programs.student']);

        $groupData = collect();
        if ($group) {
            $total = $group->programs->count();
            $approved = $group->programs->where('status', ProgramStatus::Approved)->count();

            $groupData = collect([(object) [
                'group' => $group,
                'totalPrograms' => $total,
                'approvedCount' => $approved,
                'allApproved' => $total > 0 && $approved === $total,
                'approvedPrograms' => $group->programs->where('status', ProgramStatus::Approved),
            ]]);
        }

        return view('livewire.dpl.team-documents', [
            'groupData' => $groupData,
        ]);
    }
}
