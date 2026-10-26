<?php

namespace App\Livewire\Dpl;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Models\DailyLog;
use App\Models\Grade;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public ?int $selectedGroupId = null;

    public function render()
    {
        $user = Auth::user();

        // Active period
        $period = Period::active()->first() ?? Period::current();

        // Supervised groups scoped to active period if possible, otherwise fallback to all groups
        $dplGroups = $user->dplGroups()
            ->when($period, fn ($q) => $q->where('period_id', $period->id))
            ->with(['students', 'period', 'dpls', 'groupType', 'studentLeader', 'leadDpl'])
            ->get();

        if ($dplGroups->isEmpty()) {
            $dplGroups = $user->dplGroups()
                ->with(['students', 'period', 'dpls', 'groupType', 'studentLeader', 'leadDpl'])
                ->get();
        }

        // Filter by selected group if specified and valid
        $activeGroups = ($this->selectedGroupId && $dplGroups->contains('id', $this->selectedGroupId))
            ? $dplGroups->where('id', $this->selectedGroupId)
            : $dplGroups;

        $groupIds = $activeGroups->pluck('id');
        $students = $activeGroups->pluck('students')->flatten();
        $studentIds = $students->pluck('id');
        $totalStudents = $students->count();

        // Pending programs needing review
        $pendingPrograms = $groupIds->isNotEmpty()
            ? ProgramParticipant::whereHas('program', fn ($q) => $q->whereIn('group_id', $groupIds))
                ->where('status', ProgramStatus::Submitted)
                ->with(['program.programType', 'student'])
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $pendingProgramsCount = $groupIds->isNotEmpty()
            ? ProgramParticipant::whereHas('program', fn ($q) => $q->whereIn('group_id', $groupIds))
                ->where('status', ProgramStatus::Submitted)
                ->count()
            : 0;

        // Pending daily logs needing review
        $pendingLogs = $studentIds->isNotEmpty()
            ? DailyLog::whereIn('student_id', $studentIds)
                ->where('status', LogStatus::Pending)
                ->with(['student', 'activities'])
                ->latest('date')
                ->take(5)
                ->get()
            : collect();

        $pendingLogsCount = $studentIds->isNotEmpty()
            ? DailyLog::whereIn('student_id', $studentIds)
                ->where('status', LogStatus::Pending)
                ->count()
            : 0;

        // Pending mentoring logs needing review
        $pendingMentoring = $groupIds->isNotEmpty()
            ? MentoringLog::whereIn('group_id', $groupIds)
                ->where('status', LogStatus::Pending)
                ->with(['student', 'program'])
                ->latest('date')
                ->take(5)
                ->get()
            : collect();

        $pendingMentoringCount = $groupIds->isNotEmpty()
            ? MentoringLog::whereIn('group_id', $groupIds)
                ->where('status', LogStatus::Pending)
                ->count()
            : 0;

        // Grading completion status
        $gradedStudentsCount = $studentIds->isNotEmpty()
            ? Grade::whereIn('student_id', $studentIds)
                ->where(fn ($q) => $q->whereNotNull('grade_letter')->orWhere('final_grade', '>', 0))
                ->count()
            : 0;

        $gradingPercentage = $totalStudents > 0
            ? (int) round(($gradedStudentsCount / $totalStudents) * 100)
            : 0;

        return view('livewire.dpl.dashboard', [
            'user' => $user,
            'period' => $period,
            'dplGroups' => $dplGroups,
            'activeGroups' => $activeGroups,
            'totalStudents' => $totalStudents,
            'pendingPrograms' => $pendingPrograms,
            'pendingProgramsCount' => $pendingProgramsCount,
            'pendingLogs' => $pendingLogs,
            'pendingLogsCount' => $pendingLogsCount,
            'pendingMentoring' => $pendingMentoring,
            'pendingMentoringCount' => $pendingMentoringCount,
            'gradedStudentsCount' => $gradedStudentsCount,
            'gradingPercentage' => $gradingPercentage,
        ]);
    }
}
