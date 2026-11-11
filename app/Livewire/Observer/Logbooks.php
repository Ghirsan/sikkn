<?php

namespace App\Livewire\Observer;

use App\Enums\LogStatus;
use App\Enums\UserRole;
use App\Livewire\Concerns\ScopesObserverQuery;
use App\Models\DailyLog;
use App\Models\DailyLogActivity;
use App\Models\StudyProgram;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class Logbooks extends Component
{
    use ScopesObserverQuery;
    use WithPagination;

    public string $search = '';

    public string $studyProgramId = '';

    public function render()
    {
        $query = DailyLog::with(['student.studyProgram', 'student.faculty', 'student.group', 'activities'])
            ->whereHas('student', function ($q) {
                $this->applyObserverScope($q, '');
            });

        if ($this->search) {
            $query->whereHas('student', function ($sq) {
                $sq->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%');
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

        $baseQuery = DailyLog::whereHas('student', function ($q) {
            $this->applyObserverScope($q, '');
            if ($this->studyProgramId) {
                $q->where('study_program_id', $this->studyProgramId);
            }
        });

        $totalLogs = (clone $baseQuery)->count();
        $approvedLogs = (clone $baseQuery)->where('status', LogStatus::Approved)->count();
        $pendingLogs = (clone $baseQuery)->where('status', LogStatus::Pending)->count();

        $logIds = (clone $baseQuery)->pluck('id');
        $activities = DailyLogActivity::whereIn('daily_log_id', $logIds)->select(['start_time', 'end_time'])->get();
        $totalMinutes = 0;
        foreach ($activities as $act) {
            $start = Carbon::parse($act->start_time);
            $end = Carbon::parse($act->end_time);
            $totalMinutes += $start->diffInMinutes($end);
        }
        $totalHours = floor($totalMinutes / 60);

        return view('livewire.observer.logbooks', [
            'logs' => $query->latest('date')->paginate(10),
            'studyPrograms' => $studyPrograms,
            'stats' => [
                'total' => $totalLogs,
                'approved' => $approvedLogs,
                'pending' => $pendingLogs,
                'hours' => $totalHours.' Jam',
            ],
        ]);
    }
}
