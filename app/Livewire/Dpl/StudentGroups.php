<?php

namespace App\Livewire\Dpl;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGroups extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group?->load(['students', 'period', 'dpls']);

        return view('livewire.dpl.student-groups', [
            'groups' => $group ? collect([$group]) : collect(),
            'totalStudents' => $group?->students->count() ?? 0,
        ]);
    }
}
