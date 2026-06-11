<?php

namespace App\Livewire\Dpl;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentLogs extends Component
{
    public function approveDailyLog(int $logId): void
    {
        $studentIds = $this->getStudentIds();
        $log = DailyLog::whereIn('student_id', $studentIds)->findOrFail($logId);
        $log->update(['status' => LogStatus::Approved]);
    }

    public function render()
    {
        $studentIds = $this->getStudentIds();
        $logs = DailyLog::whereIn('student_id', $studentIds)->with(['student', 'activities'])->latest('date')->get();

        return view('livewire.dpl.student-logs', [
            'logs' => $logs,
            'stats' => [
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
                'approved' => $logs->where('status', LogStatus::Approved)->count(),
                'total' => $logs->count(),
            ],
        ]);
    }

    private function getStudentIds()
    {
        $group = Auth::user()->group;

        return $group ? $group->students->pluck('id') : collect();
    }
}
