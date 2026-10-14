<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Students extends Component
{
    use WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::where('role', UserRole::Mahasiswa)->with(['group.period']);

        if ($this->periodId) {
            $query->whereHas('group', function ($q) {
                $q->where('period_id', $this->periodId);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%')
                    ->orWhereHas('studyProgram', function ($query) {
                        $query->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $students = $query->latest()->paginate(10);

        return view('livewire.admin.students', [
            'students' => $students,
            'periods' => Period::orderByDesc('start_date')->get(),
            'totalStudents' => $students->total(),
        ]);
    }
}
