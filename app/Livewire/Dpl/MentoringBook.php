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
    public string $selectedGroupId = '';

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
        $user = Auth::user();

        $groupsQuery = $user->dplGroups()->with('students');
        
        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }
        
        $groups = $groupsQuery->get();
        $allGroups = $user->dplGroups()->get();

        $students = $groups->pluck('students')->flatten();
        $studentIds = $students->pluck('id');

        $query = MentoringLog::whereIn('student_id', $studentIds)->with(['student', 'group', 'program']);

        if ($this->filterStudent) {
            $query->where('student_id', $this->filterStudent);
        }

        $logs = $query->latest('date')->get();

        return view('livewire.dpl.mentoring-book', [
            'logs' => $logs,
            'students' => $students,
            'allGroups' => $allGroups,
            'stats' => [
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
                'reviewed' => $logs->where('status', LogStatus::Approved)->count(),
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
