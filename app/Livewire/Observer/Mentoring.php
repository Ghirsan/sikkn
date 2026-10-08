<?php

namespace App\Livewire\Observer;

use App\Enums\UserRole;
use App\Livewire\Concerns\ScopesObserverQuery;
use App\Models\MentoringLog;
use App\Models\StudyProgram;
use Livewire\Component;
use Livewire\WithPagination;

class Mentoring extends Component
{
    use ScopesObserverQuery;
    use WithPagination;

    public string $search = '';

    public string $studyProgramId = '';

    public function render()
    {
        $query = MentoringLog::with(['student.studyProgram', 'student.faculty', 'student.group', 'program'])
            ->whereHas('student', function ($q) {
                $this->applyObserverScope($q, '');
            });

        if ($this->search) {
            $query->whereHas('student', function ($sq) {
                $sq->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%');
            })->orWhere('topic', 'like', '%'.$this->search.'%');
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

        return view('livewire.observer.mentoring', [
            'logs' => $query->latest('date')->paginate(20),
            'studyPrograms' => $studyPrograms,
        ]);
    }
}
