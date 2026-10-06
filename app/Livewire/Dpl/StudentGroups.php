<?php

namespace App\Livewire\Dpl;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGroups extends Component
{
    public $selectedGroupId = '';

    public $search = '';

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    // Edit Form State
    public ?int $editGroupId = null;

    public string $editVillage = '';

    public string $editDistrict = '';

    public string $editRegency = '';

    public string $editProvince = '';

    public string $editPartnerName = '';

    public string $editVillageHead = '';

    public ?string $editStartDate = null;

    public ?string $editEndDate = null;

    protected function rules()
    {
        return [
            'editVillage' => 'nullable|string|max:255',
            'editDistrict' => 'nullable|string|max:255',
            'editRegency' => 'nullable|string|max:255',
            'editProvince' => 'nullable|string|max:255',
            'editPartnerName' => 'nullable|string|max:255',
            'editVillageHead' => 'nullable|string|max:255',
            'editStartDate' => 'nullable|date',
            'editEndDate' => 'nullable|date|after_or_equal:editStartDate',
        ];
    }

    // Method to trigger the modal and load data
    public function editGroupDetails(int $groupId): void
    {
        $group = Auth::user()->dplGroups()->where('groups.id', $groupId)->firstOrFail();

        $this->editGroupId = $group->id;
        $this->editVillage = $group->village ?? '';
        $this->editDistrict = $group->district ?? '';
        $this->editRegency = $group->regency ?? '';
        $this->editProvince = $group->province ?? '';
        $this->editPartnerName = $group->partner_name ?? '';
        $this->editVillageHead = $group->village_head ?? '';
        $this->editStartDate = $group->start_date ? $group->start_date->format('Y-m-d') : null;
        $this->editEndDate = $group->end_date ? $group->end_date->format('Y-m-d') : null;

        $this->modal('edit-group-modal')->show();
    }

    // Method to save changes
    public function updateGroupDetails(): void
    {
        $this->validate();

        $group = Auth::user()->dplGroups()->where('groups.id', $this->editGroupId)->firstOrFail();

        $group->update([
            'village' => $this->editVillage,
            'district' => $this->editDistrict,
            'regency' => $this->editRegency,
            'province' => $this->editProvince,
            'partner_name' => $this->editPartnerName,
            'village_head' => $this->editVillageHead,
            'start_date' => $this->editStartDate,
            'end_date' => $this->editEndDate,
        ]);

        $this->modal('edit-group-modal')->close();

        Flux::toast(
            variant: 'success',
            heading: __('Detail Kelompok Diperbarui'),
            text: __('Detail kelompok berhasil disimpan.'),
        );
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    // Pending leader assignment
    public ?int $pendingGroupId = null;

    public ?int $pendingStudentId = null;

    public string $pendingStudentName = '';

    public string $pendingCurrentLeaderName = '';

    public bool $isReplacing = false;

    public function confirmLeader(int $groupId, int $studentId, string $studentName, string $currentLeaderName = ''): void
    {
        $this->pendingGroupId = $groupId;
        $this->pendingStudentId = $studentId;
        $this->pendingStudentName = $studentName;
        $this->pendingCurrentLeaderName = $currentLeaderName;
        $this->isReplacing = ! empty($currentLeaderName);
        $this->modal('confirm-leader')->show();
    }

    public function setLeader(): void
    {
        if (! $this->pendingGroupId || ! $this->pendingStudentId) {
            return;
        }

        $group = Auth::user()->dplGroups()
            ->where('groups.id', $this->pendingGroupId)
            ->first();

        if (! $group) {
            return;
        }

        // Verify the student actually belongs to this group
        if (! $group->students()->where('users.id', $this->pendingStudentId)->exists()) {
            return;
        }

        $group->update(['student_leader_id' => $this->pendingStudentId]);

        $this->modal('confirm-leader')->close();
        $this->resetLeaderState();

        Flux::toast(
            variant: 'success',
            heading: __('Ketua Ditetapkan'),
            text: __('Ketua berhasil ditetapkan.'),
        );
    }

    public function resetLeaderState(): void
    {
        $this->pendingGroupId = null;
        $this->pendingStudentId = null;
        $this->pendingStudentName = '';
        $this->pendingCurrentLeaderName = '';
        $this->isReplacing = false;
    }

    public function render()
    {
        $user = Auth::user();

        $dplGroupsQuery = $user->dplGroups()->with([
            'students' => function ($query) {
                if ($this->search) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('nim', 'like', '%'.$this->search.'%')
                            ->orWhere('prodi', 'like', '%'.$this->search.'%');
                    });
                }
            },
            'period', 'dpls', 'leadDpl', 'studentLeader',
        ]);

        if ($this->selectedGroupId) {
            $dplGroupsQuery->where('groups.id', $this->selectedGroupId);
        }

        if ($this->search && empty($this->selectedGroupId)) {
            $dplGroupsQuery->whereHas('students', function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%')
                    ->orWhere('prodi', 'like', '%'.$this->search.'%');
            });
        }

        $groups = $dplGroupsQuery->get();

        foreach ($groups as $group) {
            $group->setRelation('students', $group->students->sort(function ($a, $b) use ($group) {
                $aIsLeader = $a->id === $group->student_leader_id;
                $bIsLeader = $b->id === $group->student_leader_id;

                if ($aIsLeader && ! $bIsLeader) {
                    return -1;
                }
                if (! $aIsLeader && $bIsLeader) {
                    return 1;
                }

                $valA = $a->{$this->sortBy};
                $valB = $b->{$this->sortBy};

                $cmp = strcasecmp($valA, $valB);

                return $this->sortDirection === 'asc' ? $cmp : -$cmp;
            })->values());
        }

        $allGroups = $user->dplGroups()->get(); // For the dropdown

        $totalStudents = $groups->pluck('students')->flatten()->count();

        return view('livewire.dpl.student-groups', [
            'groups' => $groups,
            'allGroups' => $allGroups,
            'totalStudents' => $totalStudents,
        ]);
    }
}
