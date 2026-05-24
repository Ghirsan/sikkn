<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use Livewire\Component;

class Groups extends Component
{
    public string $search = '';

    public function render()
    {
        $query = Group::with(['period', 'dpl'])->withCount('students');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('village', 'like', '%'.$this->search.'%');
        }

        $groups = $query->latest()->get();

        return view('livewire.admin.groups', [
            'groups' => $groups,
            'stats' => [
                'total' => Group::count(),
                'with_dpl' => Group::whereNotNull('dpl_id')->count(),
                'without_dpl' => Group::whereNull('dpl_id')->count(),
            ],
        ]);
    }
}
