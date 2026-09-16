<?php

namespace App\Livewire\Dpl;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGroups extends Component
{
    public $selectedGroupId = '';
    public $search = '';

    public function setLeader(int $groupId, int $studentId): void
    {
        $group = Auth::user()->dplGroups()
            ->where('groups.id', $groupId)
            ->first();

        if (! $group || $group->student_leader_id !== null) {
            return;
        }

        // Verify the student actually belongs to this group
        if (! $group->students()->where('users.id', $studentId)->exists()) {
            return;
        }

        $group->update(['student_leader_id' => $studentId]);
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

        if ($this->search) {
            $dplGroupsQuery->whereHas('students', function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nim', 'like', '%' . $this->search . '%')
                      ->orWhere('prodi', 'like', '%' . $this->search . '%');
            });
        }

        $groups = $dplGroupsQuery->get();
        $allGroups = $user->dplGroups()->get(); // For the dropdown

        $totalStudents = $groups->pluck('students')->flatten()->count();

        return view('livewire.dpl.student-groups', [
            'groups' => $groups,
            'allGroups' => $allGroups,
            'totalStudents' => $totalStudents,
        ]);
    }
}
