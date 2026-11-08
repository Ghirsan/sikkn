<?php

namespace App\Livewire\Dpl;

use App\Enums\DocumentStatus;
use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Models\ProgramParticipant;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TeamDocuments extends Component
{
    public string $selectedGroupId = '';

    public ?int $reviewingGroupId = null;

    public ?Group $reviewingGroup = null;

    public string $lrkReviewNote = '';

    public bool $showRevisionInput = false;

    public function inspectLrk(int $groupId): void
    {
        $user = Auth::user();
        $group = $user->dplGroups()->with(['period', 'students', 'studentLeader'])->findOrFail($groupId);

        $this->reviewingGroupId = $groupId;
        $this->reviewingGroup = $group;
        $this->lrkReviewNote = $group->lrk_notes ?? '';
        $this->showRevisionInput = false;

        Flux::modal('review-lrk-modal')->show();
    }

    public function closeInspectLrk(): void
    {
        $this->reviewingGroupId = null;
        $this->reviewingGroup = null;
        $this->lrkReviewNote = '';
        $this->showRevisionInput = false;
    }

    public function toggleRevisionInput(): void
    {
        $this->showRevisionInput = ! $this->showRevisionInput;
    }

    public function approveLrk(): void
    {
        if (! $this->reviewingGroupId) {
            return;
        }

        $user = Auth::user();
        $group = $user->dplGroups()->findOrFail($this->reviewingGroupId);

        if (! $group->isLrkReady()) {
            Flux::toast(variant: 'danger', heading: 'Belum Siap', text: 'LRK belum dapat disetujui karena seluruh rencana program belum lengkap dan disetujui.');

            return;
        }

        $group->update([
            'lrk_status' => DocumentStatus::Approved,
            'is_lrk_locked' => true,
            'lrk_reviewed_at' => now(),
        ]);

        $this->reviewingGroup = $group->fresh();
        Flux::modal('review-lrk-modal')->close();
        Flux::toast(variant: 'success', heading: 'LRK Disetujui', text: 'Dokumen LRK berhasil disetujui dan siap dicetak.');
    }

    public function requestLrkRevision(): void
    {
        if (! $this->reviewingGroupId) {
            return;
        }

        $user = Auth::user();
        $group = $user->dplGroups()->findOrFail($this->reviewingGroupId);

        $this->validate([
            'lrkReviewNote' => ['required', 'string', 'min:5'],
        ], [
            'lrkReviewNote.required' => 'Catatan revisi wajib diisi jika meminta perbaikan.',
            'lrkReviewNote.min' => 'Catatan revisi minimal berisi 5 karakter.',
        ]);

        $group->update([
            'lrk_status' => DocumentStatus::NeedsRevision,
            'lrk_notes' => trim($this->lrkReviewNote),
            'is_lrk_locked' => false,
            'lrk_reviewed_at' => now(),
        ]);

        $this->reviewingGroup = $group->fresh();
        Flux::modal('review-lrk-modal')->close();
        Flux::toast(variant: 'warning', heading: 'Revisi Diminta', text: 'Catatan revisi telah dikirimkan ke ketua kelompok.');
    }

    public function revertLrkToDraft(): void
    {
        if (! $this->reviewingGroupId) {
            return;
        }

        $user = Auth::user();
        $group = $user->dplGroups()->findOrFail($this->reviewingGroupId);

        $group->update([
            'lrk_status' => DocumentStatus::Draft,
            'is_lrk_locked' => false,
        ]);

        $this->reviewingGroup = $group->fresh();
        Flux::modal('review-lrk-modal')->close();
        Flux::toast(variant: 'info', heading: 'Status LRK Dibatalkan', text: 'Dokumen LRK dikembalikan ke status draf.');
    }

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
            $participants = ProgramParticipant::whereHas('program', fn ($q) => $q->where('group_id', $group->id))->with(['program', 'student'])->get();

            $total = $participants->count();
            $approved = $participants->where('status', ProgramStatus::Approved)->count();
            $lrkReady = $total > 0 && $approved === $total;

            $groupData->push((object) [
                'group' => $group,
                'totalPrograms' => $total,
                'approvedCount' => $approved,
                'lrkReady' => $lrkReady,
                'isLrkLocked' => $group->is_lrk_locked,
                'isLpkLocked' => $group->is_lpk_locked,
                'lrkStatus' => $group->lrk_status ?? ($group->is_lrk_locked ? DocumentStatus::Approved : DocumentStatus::Draft),
                'approvedPrograms' => $participants->where('status', ProgramStatus::Approved),
                'pendingPrograms' => $participants->where('status', ProgramStatus::Submitted),
                'revisionPrograms' => $participants->where('status', ProgramStatus::NeedsRevision),
                'allPrograms' => $participants,
            ]);
        }

        $stats = [
            'total' => $groupData->sum('totalPrograms'),
            'approved' => $groupData->sum('approvedCount'),
            'pending' => $groupData->sum(fn ($d) => $d->pendingPrograms->count()),
            'ready_groups' => $groupData->where('lrkReady', true)->count(),
        ];

        return view('livewire.dpl.team-documents', [
            'groupData' => $groupData,
            'allGroups' => $allGroups,
            'stats' => $stats,
        ]);
    }
}
