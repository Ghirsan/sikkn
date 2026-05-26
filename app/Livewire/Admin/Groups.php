<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use Livewire\Component;

class Groups extends Component
{
    public string $search = '';

    public function render()
    {
        $query = Group::with(['period', 'dpls'])->withCount('students');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('village', 'like', '%'.$this->search.'%');
        }

        $groups = $query->latest()->get();

        return view('livewire.admin.groups', [
            'groups' => $groups,
            'stats' => [
                'total' => Group::count(),
                'with_dpl' => Group::whereHas('dpls')->count(),
                'without_dpl' => Group::whereDoesntHave('dpls')->count(),
            ],
        ]);
    }
}
