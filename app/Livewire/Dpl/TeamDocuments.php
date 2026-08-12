<?php

namespace App\Livewire\Dpl;

use App\Enums\ProgramStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamDocuments extends Component
{
    public string $selectedGroupId = '';

    public function render()
    {
        $user = Auth::user();
        
        $groupsQuery = $user->dplGroups()->with(['programs.student', 'programs.participants', 'period']);
        
        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }
        
        $groups = $groupsQuery->get();
        $allGroups = $user->dplGroups()->get();

        $groupData = collect();
        
        foreach ($groups as $group) {
            $participants = \App\Models\ProgramParticipant::whereHas('program', fn($q) => $q->where('group_id', $group->id))->with(['program', 'student'])->get();
            
            $total = $participants->count();
            $approved = $participants->where('status', \App\Enums\ProgramStatus::Approved)->count();
            $lrkReady = $total > 0 && $approved === $total;

            $groupData->push((object) [
                'group' => $group,
                'totalPrograms' => $total,
                'approvedCount' => $approved,
                'lrkReady' => $lrkReady,
                'isLrkLocked' => $group->is_lrk_locked,
                'isLpkLocked' => $group->is_lpk_locked,
                'approvedPrograms' => $participants->where('status', \App\Enums\ProgramStatus::Approved),
                'pendingPrograms' => $participants->where('status', \App\Enums\ProgramStatus::Submitted),
                'revisionPrograms' => $participants->where('status', \App\Enums\ProgramStatus::NeedsRevision),
                'allPrograms' => $participants,
            ]);
        }

        return view('livewire.dpl.team-documents', [
            'groupData' => $groupData,
            'allGroups' => $allGroups,
        ]);
    }
}
