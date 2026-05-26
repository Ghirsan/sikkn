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
        $group = $user->group?->load(['students.grade', 'period']);
        $periodCompleted = $group?->period?->status === PeriodStatus::Completed;

        $students = $group?->students ?? collect();
        $graded = $students->filter(fn ($s) => $s->grade !== null)->count();

        return view('livewire.dpl.student-grades', [
            'groups' => $group ? collect([$group]) : collect(),
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
