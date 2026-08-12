<?php

namespace App\Livewire\Dpl;

use App\Enums\ProgramStatus;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReviewPrograms extends Component
{
    public string $filterStatus = '';
    public string $selectedGroupId = '';

    public function approve(int $participantId): void
    {
        $participant = $this->getAuthorizedParticipant($participantId);
        $participant->update(['status' => ProgramStatus::Approved, 'revision_note' => null]);
    }

    public string $revisionNote = '';

    public int $revisingParticipantId = 0;

    public function startRevision(int $participantId): void
    {
        $this->revisingParticipantId = $participantId;
        $this->revisionNote = '';
    }

    public function submitRevision(): void
    {
        $this->validate(['revisionNote' => 'required|min:10']);

        $participant = $this->getAuthorizedParticipant($this->revisingParticipantId);
        $participant->update([
            'status' => ProgramStatus::NeedsRevision,
            'revision_note' => $this->revisionNote,
        ]);

        $this->revisingParticipantId = 0;
        $this->revisionNote = '';
    }

    public function render()
    {
        $user = Auth::user();
        $groupIds = $user->dplGroups()->pluck('groups.id');

        if ($this->selectedGroupId) {
            $groupIds = collect([$this->selectedGroupId]);
        }

        $query = ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })->with(['student', 'program.group']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.dpl.review-programs', [
            'participants' => $query->latest()->get(),
            'allGroups' => $user->dplGroups()->get(),
            'stats' => [
                'pending' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::Submitted)->count(),
                
                'approved' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::Approved)->count(),
                
                'revision' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->where('status', ProgramStatus::NeedsRevision)->count(),
                
                'total' => ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
                    $q->whereIn('group_id', $groupIds);
                })->count(),
            ],
        ]);
    }

    private function getAuthorizedParticipant(int $participantId): ProgramParticipant
    {
        $groupIds = Auth::user()->dplGroups()->pluck('groups.id');

        return ProgramParticipant::whereHas('program', function ($q) use ($groupIds) {
            $q->whereIn('group_id', $groupIds);
        })->findOrFail($participantId);
    }
}
