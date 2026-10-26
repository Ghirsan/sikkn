<?php

namespace App\Livewire\Observer;

use App\Models\DailyLog;
use App\Models\Period;
use App\Models\ProgramParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProdiDashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $studyProgram = $user->studyProgram;

        // Active period
        $period = Period::active()->first() ?? Period::current();

        $studyProgramId = $user->study_program_id;

        // Students from this prodi
        $totalStudents = $studyProgramId
            ? User::mahasiswa()
                ->where('study_program_id', $studyProgramId)
                ->when($period, fn ($q) => $q->whereHas('group', fn ($g) => $g->where('period_id', $period->id)))
                ->count()
            : 0;

        // DPLs from this prodi
        $totalDpls = $studyProgramId
            ? User::dpl()
                ->where('study_program_id', $studyProgramId)
                ->count()
            : 0;

        // Programs proposed by students from this prodi
        $totalPrograms = $studyProgramId
            ? ProgramParticipant::whereHas('student', function ($q) use ($studyProgramId, $period) {
                $q->where('study_program_id', $studyProgramId)
                    ->when($period, fn ($sq) => $sq->whereHas('group', fn ($g) => $g->where('period_id', $period->id)));
            })->count()
            : 0;

        // Daily logs submitted by students from this prodi
        $totalLogs = $studyProgramId
            ? DailyLog::whereHas('student', function ($q) use ($studyProgramId, $period) {
                $q->where('study_program_id', $studyProgramId)
                    ->when($period, fn ($sq) => $sq->whereHas('group', fn ($g) => $g->where('period_id', $period->id)));
            })->count()
            : 0;

        return view('livewire.observer.prodi-dashboard', [
            'user' => $user,
            'studyProgram' => $studyProgram,
            'period' => $period,
            'totalStudents' => $totalStudents,
            'totalDpls' => $totalDpls,
            'totalPrograms' => $totalPrograms,
            'totalLogs' => $totalLogs,
        ]);
    }
}
