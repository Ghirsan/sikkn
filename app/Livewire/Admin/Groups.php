<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use Livewire\Component;

class Groups extends Component
{
    public string $search = '';

    public bool $showAssignModal = false;
    public ?Group $assigningGroup = null;
    public array $selectedDpls = [];
    public ?int $leadDplId = null;

    public function openAssignModal(Group $group)
    {
        $this->assigningGroup = $group;
        $this->selectedDpls = $group->dpls()->pluck('users.id')->map(fn($id) => (string) $id)->toArray();
        $this->leadDplId = $group->lead_dpl_id;
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assigningGroup = null;
        $this->selectedDpls = [];
        $this->leadDplId = null;
    }

    public function saveAssignments()
    {
        if (!$this->assigningGroup) {
            return;
        }

        $this->validate([
            'selectedDpls' => 'array',
            'leadDplId' => 'nullable|integer',
        ]);

        // Sync pivot table
        $this->assigningGroup->dpls()->sync($this->selectedDpls);

        // Ensure lead DPL is one of the assigned DPLs
        if ($this->leadDplId && !in_array((string)$this->leadDplId, $this->selectedDpls)) {
            $this->leadDplId = null; // Unset if they are not in the assigned list
        }

        // Update group's lead_dpl_id
        $this->assigningGroup->update(['lead_dpl_id' => $this->leadDplId]);

        $this->closeAssignModal();
        flux()->toast('DPL berhasil ditugaskan ke kelompok.');
    }

    public function render()
    {
        $query = Group::with(['period', 'dpls'])->withCount('students');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('village', 'like', '%'.$this->search.'%');
        }

        $groups = $query->latest()->get();
        $availableDpls = \App\Models\User::where('role', \App\Enums\Role::Dpl)->get();

        return view('livewire.admin.groups', [
            'groups' => $groups,
            'availableDpls' => $availableDpls,
            'stats' => [
                'total' => Group::count(),
                'with_dpl' => Group::whereHas('dpls')->count(),
                'without_dpl' => Group::whereDoesntHave('dpls')->count(),
            ],
        ]);
    }
}
