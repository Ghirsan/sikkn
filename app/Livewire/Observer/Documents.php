<?php

namespace App\Livewire\Observer;

use App\Livewire\Concerns\ScopesObserverQuery;
use App\Models\Group;
use Livewire\Component;
use Livewire\WithPagination;

class Documents extends Component
{
    use ScopesObserverQuery;
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Group::with(['period', 'students']);
        $query = $this->applyObserverGroupScope($query);

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $baseQuery = Group::query();
        $baseQuery = $this->applyObserverGroupScope($baseQuery);

        $totalGroups = (clone $baseQuery)->count();
        $lrkLocked = (clone $baseQuery)->where('is_lrk_locked', true)->count();
        $lpkLocked = (clone $baseQuery)->where('is_lpk_locked', true)->count();
        $inProgress = (clone $baseQuery)->where('is_lrk_locked', false)->count();

        return view('livewire.observer.documents', [
            'groups' => $query->latest()->paginate(10),
            'stats' => [
                'total' => $totalGroups,
                'lrk_locked' => $lrkLocked,
                'lpk_locked' => $lpkLocked,
                'in_progress' => $inProgress,
            ],
        ]);
    }
}
