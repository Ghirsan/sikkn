<?php

namespace App\Livewire\Observer;

use App\Enums\UserRole;
use App\Livewire\Concerns\ScopesObserverQuery;
use App\Models\StudyProgram;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Dpls extends Component
{
    use ScopesObserverQuery;
    use WithPagination;

    public string $search = '';

    public string $studyProgramId = '';

    public function render()
    {
        $query = User::where('role', UserRole::Dpl)->with(['dplGroups.period', 'studyProgram', 'faculty']);
        $query = $this->applyObserverScope($query);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nip', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->studyProgramId) {
            $query->where('study_program_id', $this->studyProgramId);
        }

        $studyPrograms = collect();
        if (auth()->user()->hasRole(UserRole::Fakultas)) {
            $studyPrograms = StudyProgram::where('faculty_id', auth()->user()->faculty_id)->orderBy('name')->get();
        }

        $dpls = $query->latest()->paginate(10);

        $baseQuery = $this->applyObserverScope(User::where('role', UserRole::Dpl));
        if ($this->studyProgramId) {
            $baseQuery->where('study_program_id', $this->studyProgramId);
        }
        $totalDpls = (clone $baseQuery)->count();
        $assignedCount = (clone $baseQuery)->whereHas('dplGroups')->count();
        $unassignedCount = max(0, $totalDpls - $assignedCount);

        return view('livewire.observer.dpls', [
            'dpls' => $dpls,
            'studyPrograms' => $studyPrograms,
            'stats' => [
                'total' => $totalDpls,
                'assigned' => $assignedCount,
                'unassigned' => $unassignedCount,
            ],
        ]);
    }
}
