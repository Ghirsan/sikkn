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

    public string $filterStudent = '';

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
        $group = Auth::user()->group;
        $students = $group ? $group->students : collect();
        $studentIds = $students->pluck('id');

        $query = MentoringLog::whereIn('student_id', $studentIds)->with(['student', 'group', 'program']);

        if ($this->filterStudent) {
            $query->where('student_id', $this->filterStudent);
        }

        $logs = $query->latest('date')->get();

        return view('livewire.dpl.mentoring-book', [
            'logs' => $logs,
            'students' => $students,
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
