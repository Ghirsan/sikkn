<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\MentoringLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MentoringLogs extends Component
{
    public function render()
    {
        $user = Auth::user();

        $logs = MentoringLog::where('student_id', $user->id)->latest('date')->get();

        return view('livewire.mahasiswa.mentoring-logs', [
            'logs' => $logs,
            'stats' => [
                'total' => $logs->count(),
                'reviewed' => $logs->where('status', LogStatus::Approved)->count(),
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
            ],
        ]);
    }
}
