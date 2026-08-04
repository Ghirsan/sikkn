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
        $group = $user->group?->load(['programs.student', 'programs.participants', 'period']);

        $groupData = collect();
        if ($group) {
            $participants = \App\Models\ProgramParticipant::whereHas('program', fn($q) => $q->where('group_id', $group->id))->with(['program', 'student'])->get();
            
            $total = $participants->count();
            $approved = $participants->where('status', \App\Enums\ProgramStatus::Approved)->count();
            $lrkReady = $total > 0 && $approved === $total;

            $groupData = collect([(object) [
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
            ]]);
        }

        return view('livewire.dpl.team-documents', [
            'groupData' => $groupData,
        ]);
    }
}
