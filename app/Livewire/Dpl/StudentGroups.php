<?php

namespace App\Livewire\Dpl;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGroups extends Component
{
    public $selectedGroupId = '';
    public $search = '';

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

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
        $this->isReplacing = !empty($currentLeaderName);
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
                        $q->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('nim', 'like', '%' . $this->search . '%')
                          ->orWhere('prodi', 'like', '%' . $this->search . '%');
                    });
                }
            },
            'period', 'dpls', 'leadDpl', 'studentLeader'
        ]);
        
        if ($this->selectedGroupId) {
            $dplGroupsQuery->where('groups.id', $this->selectedGroupId);
        }

        if ($this->search && empty($this->selectedGroupId)) {
            $dplGroupsQuery->whereHas('students', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nim', 'like', '%' . $this->search . '%')
                      ->orWhere('prodi', 'like', '%' . $this->search . '%');
            });
        }

        $groups = $dplGroupsQuery->get();

        foreach ($groups as $group) {
            $group->setRelation('students', $group->students->sort(function ($a, $b) use ($group) {
                $aIsLeader = $a->id === $group->student_leader_id;
                $bIsLeader = $b->id === $group->student_leader_id;
            
                if ($aIsLeader && !$bIsLeader) return -1;
                if (!$aIsLeader && $bIsLeader) return 1;
            
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
