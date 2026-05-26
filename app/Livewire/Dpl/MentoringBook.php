<?php

namespace App\Livewire\Dpl;

use App\Enums\LogStatus;
use App\Models\MentoringLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MentoringBook extends Component
{
    public string $feedback = '';

    public int $feedbackLogId = 0;

    public function startFeedback(int $logId): void
    {
        $this->feedbackLogId = $logId;
        $this->feedback = '';
    }

    public function submitFeedback(): void
    {
        $this->validate(['feedback' => 'required|min:10']);

        $studentIds = $this->getStudentIds();
        $log = MentoringLog::whereIn('student_id', $studentIds)->findOrFail($this->feedbackLogId);
        $log->update([
            'dpl_feedback' => $this->feedback,
            'status' => LogStatus::Approved,
        ]);

        $this->feedbackLogId = 0;
        $this->feedback = '';
    }

    public function render()
    {
        $studentIds = $this->getStudentIds();
        $logs = MentoringLog::whereIn('student_id', $studentIds)->with(['student', 'group'])->latest('date')->get();

        return view('livewire.dpl.mentoring-book', [
            'logs' => $logs,
            'stats' => [
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
                'reviewed' => $logs->where('status', LogStatus::Approved)->count(),
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
