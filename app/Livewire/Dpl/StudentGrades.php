<?php

namespace App\Livewire\Dpl;

use App\Enums\PeriodStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGrades extends Component
{
    public string $selectedGroupId = '';

    public function render()
    {
        $user = Auth::user();
        
        $groupsQuery = $user->dplGroups()->with(['students.grade', 'period']);
        
        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }
        
        $groups = $groupsQuery->get();
        $allGroups = $user->dplGroups()->get();
        
        // If there's no period, or if ANY group is not completed, we consider period not completed.
        // It's safer to just check if every group is completed.
        $periodCompleted = $groups->isNotEmpty() && $groups->every(fn($g) => $g->period?->status === PeriodStatus::Completed);

        $students = $groups->pluck('students')->flatten();
        $graded = $students->filter(fn ($s) => $s->grade !== null)->count();

        return view('livewire.dpl.student-grades', [
            'groups' => $groups,
            'allGroups' => $allGroups,
            'students' => $students,
            'periodCompleted' => $periodCompleted,
            'stats' => [
                'total' => $students->count(),
                'graded' => $graded,
                'ungraded' => $students->count() - $graded,
            ],
        ]);
    }
}
