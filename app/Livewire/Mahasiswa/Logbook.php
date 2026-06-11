<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logbook extends Component
{
    public function render()
    {
        $user = Auth::user();

        $logs = DailyLog::with('activities')
            ->where('student_id', $user->id)
            ->latest('date')
            ->get();

        return view('livewire.mahasiswa.logbook', [
            'logs' => $logs,
            'student' => $user,
            'stats' => [
                'total' => $logs->count(),
                'approved' => $logs->where('status', LogStatus::Approved)->count(),
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
            ],
        ]);
    }
}
