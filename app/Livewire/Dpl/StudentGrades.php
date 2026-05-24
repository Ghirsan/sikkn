<?php

namespace App\Livewire\Dpl;

use App\Enums\PeriodStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGrades extends Component
{
    public function render()
    {
        $user = Auth::user();
        $groups = $user->supervisedGroups()->with(['students.grade', 'period'])->get();
        $periodCompleted = $groups->first()?->period?->status === PeriodStatus::Completed;

        $students = $groups->pluck('students')->flatten();
        $graded = $students->filter(fn ($s) => $s->grade !== null)->count();

        return view('livewire.dpl.student-grades', [
            'groups' => $groups,
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
