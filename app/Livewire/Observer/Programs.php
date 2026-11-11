<?php

namespace App\Livewire\Observer;

use App\Enums\ProgramStatus;
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

        $baseQuery = ProgramParticipant::whereHas('student', function ($q) {
            $this->applyObserverScope($q, '');
            if ($this->studyProgramId) {
                $q->where('study_program_id', $this->studyProgramId);
            }
        });

        $totalPrograms = (clone $baseQuery)->count();
        $lrkApproved = (clone $baseQuery)->where('status', ProgramStatus::Approved)->count();
        $lpkApproved = (clone $baseQuery)->where('lpk_status', ProgramStatus::Approved)->count();
        $pending = (clone $baseQuery)->where(function ($q) {
            $q->where('status', ProgramStatus::Submitted)
                ->orWhere('lpk_status', ProgramStatus::Submitted);
        })->count();

        return view('livewire.observer.programs', [
            'participants' => $query->latest()->paginate(10),
            'studyPrograms' => $studyPrograms,
            'stats' => [
                'total' => $totalPrograms,
                'lrk_approved' => $lrkApproved,
                'lpk_approved' => $lpkApproved,
                'pending' => $pending,
            ],
        ]);
    }
}
