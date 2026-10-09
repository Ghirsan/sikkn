<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\User;
use Livewire\Component;

class Dpls extends Component
{
    public ?int $periodId = null;

    public string $search = '';

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id;
    }

    public function render()
    {
        $query = User::where('role', UserRole::Dpl)->with('dplGroups.period');

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

        $dpls = $query->latest()->get();
        $totalDpls = User::where('role', UserRole::Dpl)->count(); // global total available

        return view('livewire.admin.dpls', [
            'dpls' => $dpls,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $totalDpls, // total DPL di database
                'assigned' => $dpls->filter(fn ($dpl) => $dpl->dplGroups->count() > 0)->count(),
                'unassigned' => $totalDpls - User::where('role', UserRole::Dpl)->whereHas('dplGroups')->count(),
            ],
        ]);
    }
}
