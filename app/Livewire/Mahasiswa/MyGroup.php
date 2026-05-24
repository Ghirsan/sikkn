<?php

namespace App\Livewire\Mahasiswa;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MyGroup extends Component
{
    public function render()
    {
        $user = Auth::user();
        $group = $user->group?->load(['dpl', 'students', 'period']);

        return view('livewire.mahasiswa.my-group', [
            'group' => $group,
            'members' => $group?->students ?? collect(),
            'dpl' => $group?->dpl,
            'period' => $group?->period,
        ]);
    }
}
