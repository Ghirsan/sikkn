<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Dpls extends Component
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
        $query = User::where('role', UserRole::Dpl)->with(['studyProgram', 'dplGroups.period']);

        if ($this->periodId) {
            $query->whereHas('dplGroups', function ($q) {
                $q->where('period_id', $this->periodId);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nip', 'like', '%'.$this->search.'%');
            });
        }

        $dpls = $query->latest()->paginate(10);
        $totalDpls = User::where('role', UserRole::Dpl)->count();

        $assignedQuery = User::where('role', UserRole::Dpl)->whereHas('dplGroups', function ($q) {
            if ($this->periodId) {
                $q->where('period_id', $this->periodId);
            }
        });
        $assignedCount = $assignedQuery->count();

        return view('livewire.admin.dpls', [
            'dpls' => $dpls,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $totalDpls,
                'assigned' => $assignedCount,
                'unassigned' => max(0, $totalDpls - $assignedCount),
            ],
        ]);
    }
}
