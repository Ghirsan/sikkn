<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Livewire\Component;

class Students extends Component
{
    public string $search = '';

    public function render()
    {
        $query = User::where('role', UserRole::Mahasiswa)->with(['group.period']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%')
                    ->orWhere('prodi', 'like', '%'.$this->search.'%');
            });
        }

        return view('livewire.admin.students', [
            'students' => $query->latest()->paginate(20),
            'totalStudents' => User::where('role', UserRole::Mahasiswa)->count(),
        ]);
    }
}
