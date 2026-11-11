<?php

namespace App\Livewire\Observer;

use App\Enums\UserRole;
use App\Livewire\Concerns\ScopesObserverQuery;
use App\Models\Grade;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\GradeExcelService;
use Livewire\Component;
use Livewire\WithPagination;

class Grades extends Component
{
    use ScopesObserverQuery;
    use WithPagination;

    public string $search = '';

    public string $studyProgramId = '';

    public string $statusFilter = 'all';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function exportSiapExcel(GradeExcelService $excelService)
    {
        $user = auth()->user();

        $query = User::where('role', UserRole::Mahasiswa)
            ->with(['group.period', 'studyProgram', 'faculty', 'grade']);

        $query = $this->applyObserverScope($query);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->studyProgramId) {
            $query->where('study_program_id', $this->studyProgramId);
        }

        if ($this->statusFilter === 'graded') {
            $query->whereHas('grade');
        } elseif ($this->statusFilter === 'ungraded') {
            $query->whereDoesntHave('grade');
        }

        $students = $query->orderBy('name')->get();
        $facultyName = $user->faculty ? $user->faculty->name : null;

        return $excelService->exportFacultySiapGrades($students, $facultyName);
    }

    public function render()
    {
        $query = User::where('role', UserRole::Mahasiswa)
            ->with(['group.period', 'studyProgram', 'faculty', 'grade']);

        $query = $this->applyObserverScope($query);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->studyProgramId) {
            $query->where('study_program_id', $this->studyProgramId);
        }

        if ($this->statusFilter === 'graded') {
            $query->whereHas('grade');
        } elseif ($this->statusFilter === 'ungraded') {
            $query->whereDoesntHave('grade');
        }

        $studyPrograms = collect();
        if (auth()->user()->hasRole(UserRole::Fakultas)) {
            $studyPrograms = StudyProgram::where('faculty_id', auth()->user()->faculty_id)->orderBy('name')->get();
        }

        $baseQuery = User::where('role', UserRole::Mahasiswa);
        $baseQuery = $this->applyObserverScope($baseQuery);

        if ($this->studyProgramId) {
            $baseQuery->where('study_program_id', $this->studyProgramId);
        }

        $totalStudents = (clone $baseQuery)->count();
        $gradedCount = (clone $baseQuery)->whereHas('grade')->count();
        $ungradedCount = max(0, $totalStudents - $gradedCount);

        $studentIds = (clone $baseQuery)->pluck('id');
        $averageGrade = Grade::whereIn('student_id', $studentIds)->avg('final_grade');
        $averageGradeStr = $averageGrade ? number_format($averageGrade, 1) : '-';

        return view('livewire.observer.grades', [
            'students' => $query->latest()->paginate(10),
            'studyPrograms' => $studyPrograms,
            'stats' => [
                'total' => $totalStudents,
                'graded' => $gradedCount,
                'ungraded' => $ungradedCount,
                'average' => $averageGradeStr,
            ],
        ]);
    }
}
