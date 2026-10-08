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

        return view('livewire.observer.documents', [
            'groups' => $query->latest()->paginate(20),
        ]);
    }
}
