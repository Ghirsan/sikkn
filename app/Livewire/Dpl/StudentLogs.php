<?php

namespace App\Livewire\Dpl;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentLogs extends Component
{
    public string $filterStudent = '';
    public string $selectedGroupId = '';

    public function approveDailyLog(int $logId): void
    {
        $studentIds = $this->getStudentIds();
        $log = DailyLog::whereIn('student_id', $studentIds)->findOrFail($logId);
        $log->update(['status' => LogStatus::Approved]);
    }

    public function render()
    {
        $user = Auth::user();
        
        $groupsQuery = $user->dplGroups()->with('students');
        
        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }
        
        $groups = $groupsQuery->get();
        $allGroups = $user->dplGroups()->get();

        $students = $groups->pluck('students')->flatten();
        $studentIds = $students->pluck('id');

        $query = DailyLog::whereIn('student_id', $studentIds)->with(['student', 'activities']);

        if ($this->filterStudent) {
            $query->where('student_id', $this->filterStudent);
        }

        $logs = $query->latest('date')->get();

        return view('livewire.dpl.student-logs', [
            'logs' => $logs,
            'students' => $students,
            'allGroups' => $allGroups,
            'stats' => [
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
                'approved' => $logs->where('status', LogStatus::Approved)->count(),
                'total' => $logs->count(),
            ],
        ]);
    }

    private function getStudentIds()
    {
        $user = Auth::user();
        return $user->dplGroups()->with('students')->get()->pluck('students')->flatten()->pluck('id');
    }
}
