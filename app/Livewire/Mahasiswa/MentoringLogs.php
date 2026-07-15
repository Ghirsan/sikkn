<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\MentoringLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MentoringLogs extends Component
{
    public $viewLogData = null;

    public function viewLog($id)
    {
        $this->viewLogData = MentoringLog::with(['program', 'student.group'])->where('student_id', Auth::id())->findOrFail($id);
        \Flux::modal('mentoring-view-modal')->show();
    }

    public function render()
    {
        $user = Auth::user();

        $logs = MentoringLog::with('program')->where('student_id', $user->id)->latest('date')->get();

        return view('livewire.mahasiswa.mentoring-logs', [
            'logs' => $logs,
            'student' => $user,
            'group' => $user->group()->with(['period', 'leadDpl'])->first(),
            'stats' => [
                'total' => $logs->count(),
                'reviewed' => $logs->where('status', LogStatus::Approved)->count(),
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
            ],
        ]);
    }
}
