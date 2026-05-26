<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Component;

class Dpls extends Component
{
    public string $search = '';

    public function render()
    {
        $query = User::where('role', UserRole::Dpl)->with('group');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nip', 'like', '%'.$this->search.'%');
            });
        }

        $dpls = $query->latest()->get();

        return view('livewire.admin.dpls', [
            'dpls' => $dpls,
            'stats' => [
                'total' => $dpls->count(),
                'assigned' => $dpls->whereNotNull('group_id')->count(),
                'unassigned' => $dpls->whereNull('group_id')->count(),
            ],
        ]);
    }
}
