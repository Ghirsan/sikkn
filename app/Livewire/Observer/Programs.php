<?php

namespace App\Livewire\Observer;

use App\Enums\UserRole;
use App\Livewire\Concerns\ScopesObserverQuery;
use App\Models\ProgramParticipant;
use App\Models\StudyProgram;
use Livewire\Component;
use Livewire\WithPagination;

class Programs extends Component
{
    use ScopesObserverQuery;
    use WithPagination;

    public string $search = '';

    public string $studyProgramId = '';

    public function render()
    {
        $query = ProgramParticipant::with(['student.studyProgram', 'student.faculty', 'program.group', 'program.programType'])
            ->whereHas('student', function ($q) {
                $this->applyObserverScope($q, '');
            });

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('student', function ($sq) {
                    $sq->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('nim', 'like', '%'.$this->search.'%');
                })->orWhereHas('program', function ($sq) {
                    $sq->where('title', 'like', '%'.$this->search.'%');
                });
            });
        }

        if ($this->studyProgramId) {
            $query->whereHas('student', function ($q) {
                $q->where('study_program_id', $this->studyProgramId);
            });
        }

        $studyPrograms = collect();
        if (auth()->user()->hasRole(UserRole::Fakultas)) {
            $studyPrograms = StudyProgram::where('faculty_id', auth()->user()->faculty_id)->orderBy('name')->get();
        }

        return view('livewire.observer.programs', [
            'participants' => $query->latest()->paginate(20),
            'studyPrograms' => $studyPrograms,
        ]);
    }
}
