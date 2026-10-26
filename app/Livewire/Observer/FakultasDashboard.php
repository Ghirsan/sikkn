<?php

namespace App\Livewire\Observer;

use App\Models\Period;
use App\Models\ProgramParticipant;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class FakultasDashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $faculty = $user->faculty ?? $user->studyProgram?->faculty;
        $facultyId = $faculty?->id ?? $user->faculty_id;

        // Active period
        $period = Period::active()->first() ?? Period::current();

        // Total students from this faculty in active period
        $totalStudents = $facultyId
            ? User::mahasiswa()
                ->where('faculty_id', $facultyId)
                ->when($period, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('period_id', $period->id)))
                ->count()
            : 0;

        // Study programs under this faculty
        $totalStudyPrograms = $facultyId
            ? StudyProgram::where('faculty_id', $facultyId)->count()
            : 0;

        // DPLs from this faculty
        $totalDpls = $facultyId
            ? User::dpl()
                ->where('faculty_id', $facultyId)
                ->count()
            : 0;

        // Programs proposed by students of this faculty
        $totalPrograms = $facultyId
            ? ProgramParticipant::whereHas('student', function ($q) use ($facultyId, $period) {
                $q->where('faculty_id', $facultyId)
                    ->when($period, fn ($sq) => $sq->whereHas('group', fn ($g) => $g->where('period_id', $period->id)));
            })->count()
            : 0;

        return view('livewire.observer.fakultas-dashboard', [
            'user' => $user,
            'faculty' => $faculty,
            'period' => $period,
            'totalStudents' => $totalStudents,
            'totalStudyPrograms' => $totalStudyPrograms,
            'totalDpls' => $totalDpls,
            'totalPrograms' => $totalPrograms,
        ]);
    }
}
