<?php

namespace App\Livewire\Dpl;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class StudentLogs extends Component
{
    use WithPagination;

    public string $filterStudent = '';

    public string $selectedGroupId = '';

    public string $filterStatus = '';

    public string $selectedWeek = 'all';

    public string $sortBy = 'date';

    public string $sortDirection = 'desc';

    public array $selectedLogs = [];

    public bool $selectAll = false;

    public $viewLogData = null;

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedLogs = $this->buildQuery()
                ->where('status', LogStatus::Pending)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedLogs = [];
        }
    }

    public function updatedSelectedGroupId()
    {
        $this->selectedWeek = 'all';
        $this->filterStudent = '';
    }

    public $logIdToApprove = null;

    public function confirmApprove(int $logId): void
    {
        $this->logIdToApprove = $logId;
        \Flux::modal('confirm-approve-modal')->show();
    }

    public function executeApprove(): void
    {
        if ($this->logIdToApprove) {
            $this->approveDailyLog($this->logIdToApprove);
            $this->logIdToApprove = null;
        }
        \Flux::modal('confirm-approve-modal')->close();
    }

    public function approveDailyLog(int $logId): void
    {
        $studentIds = $this->getStudentIds();
        $log = DailyLog::whereIn('student_id', $studentIds)->findOrFail($logId);
        if ($log->status === LogStatus::Pending) {
            $log->update(['status' => LogStatus::Approved]);

            if ($this->viewLogData && $this->viewLogData->id === $logId) {
                // Refresh the modal data
                $this->viewLogData = DailyLog::with(['activities', 'student', 'student.group'])->find($logId);
            }

            \Flux\Flux::toast(variant: 'success', heading: __('Logbook Disetujui'), text: __('Logbook berhasil disetujui.'));
        }
    }

    public function executeBulkApprove()
    {
        $this->bulkApprove();
        \Flux::modal('confirm-bulk-approve-modal')->close();
    }

    public function bulkApprove()
    {
        if (empty($this->selectedLogs)) {
            return;
        }

        $studentIds = $this->getStudentIds();
        DailyLog::whereIn('student_id', $studentIds)
            ->whereIn('id', $this->selectedLogs)
            ->where('status', LogStatus::Pending)
            ->update(['status' => LogStatus::Approved]);

        $this->selectedLogs = [];
        $this->selectAll = false;

        \Flux\Flux::toast(variant: 'success', heading: __('Logbook Disetujui'), text: __('Logbook yang dipilih berhasil disetujui.'));
    }

    public function viewLog($id)
    {
        $studentIds = $this->getStudentIds();
        $log = DailyLog::with(['activities', 'student', 'student.group'])->whereIn('student_id', $studentIds)->findOrFail($id);

        $group = $log->student->group;
        $dayDiff = ($group && $group->start_date) ? Carbon::parse($group->start_date)->diffInDays($log->date) : 0;
        $log->day_number = $dayDiff + 1;
        $log->week_number = floor($dayDiff / 7) + 1;

        $this->viewLogData = $log;
        \Flux::modal('log-view-modal')->show();
    }

    private function buildQuery()
    {
        $user = Auth::user();

        $groupsQuery = $user->dplGroups()->with('students');

        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }

        $groups = $groupsQuery->get();
        $studentIds = $groups->pluck('students')->flatten()->pluck('id');

        $query = DailyLog::whereIn('student_id', $studentIds)->with(['student', 'activities']);

        if ($this->filterStudent) {
            $query->where('student_id', $this->filterStudent);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->selectedGroupId && $this->selectedWeek !== 'all') {
            $group = Group::find($this->selectedGroupId);
            if ($group && $group->start_date) {
                $startDate = Carbon::parse($group->start_date)->addDays(((int) $this->selectedWeek - 1) * 7);
                $endDate = $startDate->copy()->addDays(6);
                $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }
        }

        return $query;
    }

    public function render()
    {
        $user = Auth::user();
        $allGroups = $user->dplGroups()->get();

        $groupsForFilter = $this->selectedGroupId
            ? $allGroups->where('id', $this->selectedGroupId)
            : $allGroups;

        $students = $groupsForFilter->pluck('students')->flatten();

        $query = $this->buildQuery();

        $stats = [
            'pending' => (clone $query)->where('status', LogStatus::Pending)->count(),
            'approved' => (clone $query)->where('status', LogStatus::Approved)->count(),
            'total' => (clone $query)->count(),
        ];

        $logs = $query->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        // Calculate total stats independent of filters (optional) or dependent on filter.
        // We will base stats on the current filter view, or all logs? Previously it was based on $logs.

        $weeks = [];
        if ($this->selectedGroupId) {
            $selectedGroup = $allGroups->firstWhere('id', $this->selectedGroupId);
            if ($selectedGroup && $selectedGroup->start_date && $selectedGroup->end_date) {
                $startDate = Carbon::parse($selectedGroup->start_date);
                $endDate = Carbon::parse($selectedGroup->end_date);
                $today = Carbon::today();
                if ($endDate->gt($today)) {
                    $endDate = $today;
                }
                $days = $startDate->diffInDays($endDate) + 1;
                $totalWeeks = ceil($days / 7);
                for ($i = 1; $i <= $totalWeeks; $i++) {
                    $weeks[] = $i;
                }
            }
        }

        $totalHours = null;
        if ($this->filterStudent) {
            $studentLogs = DailyLog::with('activities')
                ->where('student_id', $this->filterStudent)
                ->get();
            $totalMinutes = 0;
            foreach ($studentLogs as $log) {
                foreach ($log->activities as $activity) {
                    $start = Carbon::parse($activity->start_time);
                    $end = Carbon::parse($activity->end_time);
                    $totalMinutes += $start->diffInMinutes($end);
                }
            }
            $totalHours = floor($totalMinutes / 60);
        }

        return view('livewire.dpl.student-logs', [
            'logs' => $logs,
            'students' => $students,
            'allGroups' => $allGroups,
            'weeks' => $weeks,
            'totalHours' => $totalHours,
            'stats' => $stats,
        ]);
    }

    private function getStudentIds()
    {
        $user = Auth::user();

        return $user->dplGroups()->with('students')->get()->pluck('students')->flatten()->pluck('id');
    }
}
