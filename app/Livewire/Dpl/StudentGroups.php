<?php

namespace App\Livewire\Dpl;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGroups extends Component
{
    public function render()
    {
        $user = Auth::user();
        $groups = $user->supervisedGroups()->with(['students', 'period'])->get();

        return view('livewire.dpl.student-groups', [
            'groups' => $groups,
            'totalStudents' => $groups->sum(fn ($g) => $g->students->count()),
        ]);
    }
}
