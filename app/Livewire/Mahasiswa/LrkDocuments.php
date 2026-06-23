<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LrkDocuments extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group;

        $participants = $group ? \App\Models\ProgramParticipant::with(['program', 'student'])
            ->whereHas('program', function($q) use ($group) {
                $q->where('group_id', $group->id);
            })->get() : collect();

        $totalParticipants = $participants->count();
        $approvedParticipants = $participants->where('status', ProgramStatus::Approved);
        $approvedCount = $approvedParticipants->count();
        $allApproved = $totalParticipants > 0 && $approvedCount === $totalParticipants;

        return view('livewire.mahasiswa.lrk-documents', [
            'group' => $group,
            'approvedParticipants' => $approvedParticipants,
            'totalParticipants' => $totalParticipants,
            'approvedCount' => $approvedCount,
            'allApproved' => $allApproved,
        ]);
    }
}
