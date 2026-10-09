<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class Groups extends Component
{
    public ?int $periodId = null;

    public string $search = '';

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id;
    }

    public bool $showAssignModal = false;

    public ?Group $assigningGroup = null;

    public array $selectedDpls = [];

    public ?int $leadDplId = null;

    public function openAssignModal(Group $group)
    {
        $this->assigningGroup = $group;
        $this->selectedDpls = $group->dpls()->pluck('users.id')->map(fn ($id) => (string) $id)->toArray();
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
        if (! $this->assigningGroup) {
            return;
        }

        $this->validate([
            'selectedDpls' => 'array',
            'leadDplId' => 'nullable|integer',
        ]);

        // Sync pivot table
        $this->assigningGroup->dpls()->sync($this->selectedDpls);

        // Ensure lead DPL is one of the assigned DPLs
        if ($this->leadDplId && ! in_array((string) $this->leadDplId, $this->selectedDpls)) {
            $this->leadDplId = null; // Unset if they are not in the assigned list
        }

        // Update group's lead_dpl_id
        $this->assigningGroup->update(['lead_dpl_id' => $this->leadDplId]);

        $this->closeAssignModal();
        \Flux\Flux::toast('DPL berhasil ditugaskan ke kelompok.');
    }

    public bool $showDatesModal = false;

    public ?Group $editingGroup = null;

    public $startDate = null;

    public $endDate = null;

    public function openDatesModal(Group $group)
    {
        $this->editingGroup = $group;
        $this->startDate = $group->start_date?->format('Y-m-d');
        $this->endDate = $group->end_date?->format('Y-m-d');
        $this->showDatesModal = true;
    }

    public function closeDatesModal()
    {
        $this->showDatesModal = false;
        $this->editingGroup = null;
        $this->startDate = null;
        $this->endDate = null;
    }

    public function saveDates()
    {
        if (! $this->editingGroup) {
            return;
        }

        $period = $this->editingGroup->period;

        $this->validate([
            'startDate' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->lt($period->start_date)) {
                        $fail('Tanggal mulai tidak boleh sebelum tanggal mulai periode ('.$period->start_date->format('d/m/Y').').');
                    }
                },
            ],
            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->gt($period->end_date)) {
                        $fail('Tanggal selesai tidak boleh setelah tanggal selesai periode ('.$period->end_date->format('d/m/Y').').');
                    }
                },
            ],
        ]);

        $this->editingGroup->update([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->closeDatesModal();
        \Flux\Flux::toast('Waktu KKN berhasil disimpan.');
    }

    public function render()
    {
        $query = Group::with(['period', 'dpls'])->withCount('students');

        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('village', 'like', '%'.$this->search.'%');
            });
        }

        $groups = $query->latest()->get();
        $availableDpls = User::where('role', UserRole::Dpl)->get();

        return view('livewire.admin.groups', [
            'groups' => $groups,
            'availableDpls' => $availableDpls,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $groups->count(),
                'with_dpl' => $groups->filter(fn ($g) => $g->dpls->count() > 0)->count(),
                'without_dpl' => $groups->filter(fn ($g) => $g->dpls->count() == 0)->count(),
            ],
        ]);
    }
}
