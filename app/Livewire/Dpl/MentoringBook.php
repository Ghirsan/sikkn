<?php

namespace App\Livewire\Dpl;

use App\Enums\LogStatus;
use App\Models\MentoringLog;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class MentoringBook extends Component
{
    public string $feedback = '';

    public int $feedbackLogId = 0;

    public string $filterStudent = '';

    public string $selectedGroupId = '';

    public string $filterStatus = '';

    public ?int $viewingLogId = null;

    public bool $showFeedbackForm = false;

    public function viewLog(int $id, bool $startFeedback = false): void
    {
        $studentIds = $this->getStudentIds();
        $log = MentoringLog::with(['student.studyProgram', 'student.group', 'program.programType', 'group'])
            ->whereIn('student_id', $studentIds)
            ->findOrFail($id);

        $this->viewingLogId = $log->id;
        $this->feedbackLogId = $log->id;
        $this->feedback = $log->dpl_feedback ?? '';
        $this->showFeedbackForm = $startFeedback;

        $this->modal('mentoring-view-modal')->show();
    }

    public function closeView(): void
    {
        $this->viewingLogId = null;
        $this->feedbackLogId = 0;
        $this->feedback = '';
        $this->showFeedbackForm = false;
        $this->resetErrorBag();
    }

    public function startFeedback(?int $logId = null): void
    {
        if ($logId) {
            $this->viewLog($logId, true);

            return;
        }

        $this->feedback = $this->viewLogData?->dpl_feedback ?? '';
        $this->showFeedbackForm = true;
    }

    public function cancelFeedback(): void
    {
        $this->showFeedbackForm = false;
        $this->feedback = $this->viewLogData?->dpl_feedback ?? '';
        $this->resetErrorBag();
    }

    public function startFeedbackFromModal(int $id): void
    {
        $this->startFeedback($id);
    }

    public function submitFeedback(): void
    {
        $this->validate(['feedback' => 'required|min:10']);

        $logId = $this->viewingLogId ?: $this->feedbackLogId;
        if (! $logId) {
            return;
        }

        $studentIds = $this->getStudentIds();
        $log = MentoringLog::whereIn('student_id', $studentIds)->findOrFail($logId);
        $log->update([
            'dpl_feedback' => $this->feedback,
            'status' => LogStatus::Approved,
        ]);

        $this->modal('mentoring-view-modal')->close();
        $this->closeView();

        Flux::toast(
            variant: 'success',
            heading: __('Tanggapan Terkirim'),
            text: __('Tanggapan pembimbingan berhasil dikirim.'),
        );
    }

    #[Computed]
    public function viewLogData(): ?MentoringLog
    {
        if (! $this->viewingLogId) {
            return null;
        }

        return MentoringLog::with(['student.studyProgram', 'student.group', 'program.programType', 'group'])
            ->whereIn('student_id', $this->getStudentIds())
            ->find($this->viewingLogId);
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

        $query = MentoringLog::whereIn('student_id', $studentIds)->with(['student', 'group', 'program.programType']);

        if ($this->filterStudent) {
            $query->where('student_id', $this->filterStudent);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
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
