<?php

namespace App\Livewire\Dpl;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGroups extends Component
{
    public $selectedGroupId = '';

    public function render()
    {
        $user = Auth::user();
        
        $dplGroupsQuery = $user->dplGroups()->with(['students', 'period', 'dpls', 'leadDpl', 'studentLeader']);
        
        if ($this->selectedGroupId) {
            $dplGroupsQuery->where('groups.id', $this->selectedGroupId);
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
