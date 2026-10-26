<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Enums\ProgramStatus;
use App\Models\DailyLog;
use App\Models\MentoringLog;
use App\Models\Period;
use App\Models\ProgramParticipant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();

        // Load group with relationships
        $group = $user->group?->load([
            'period',
            'dpls',
            'leadDpl',
            'studentLeader',
        ]);

        // Active period
        $period = $group?->period ?? Period::active()->first() ?? Period::current();

        // Calculate days remaining in active period
        $daysRemaining = null;
        $periodStatusLabel = null;
        if ($period && $period->end_date) {
            $today = Carbon::today();
            $endDate = $period->end_date->copy()->startOfDay();
            $startDate = $period->start_date ? $period->start_date->copy()->startOfDay() : null;

            if ($startDate && $today->lt($startDate)) {
                $daysToStart = (int) $today->diffInDays($startDate);
                $periodStatusLabel = $daysToStart > 0 ? "Dimulai dalam {$daysToStart} hari" : 'Dimulai hari ini';
            } elseif ($today->gt($endDate)) {
                $periodStatusLabel = 'Periode telah berakhir';
            } else {
                $daysRemaining = (int) $today->diffInDays($endDate);
                $periodStatusLabel = $daysRemaining > 0 ? "{$daysRemaining} hari tersisa" : 'Hari terakhir periode';
            }
        }

        // Assigned DPLs
        $dpls = $group?->dpls ?? collect();

        // Program participations
        $programParticipants = ProgramParticipant::where('student_id', $user->id)
            ->with(['program.programType', 'program.group'])
            ->latest()
            ->get();

        $programsCount = $programParticipants->count();
        $programsApproved = $programParticipants->where('status', ProgramStatus::Approved)->count();
        $programsSubmitted = $programParticipants->where('status', ProgramStatus::Submitted)->count();
        $programsNeedsRevision = $programParticipants->where('status', ProgramStatus::NeedsRevision);
        $programsDraft = $programParticipants->where('status', ProgramStatus::Draft)->count();

        // Daily logs
        $dailyLogs = DailyLog::where('student_id', $user->id)
            ->with('activities')
            ->latest('date')
            ->get();

        $totalDailyLogs = $dailyLogs->count();
        $approvedDailyLogs = $dailyLogs->where('status', LogStatus::Approved)->count();
        $pendingDailyLogs = $dailyLogs->where('status', LogStatus::Pending)->count();

        // Calculate cumulative approved working hours
        $totalApprovedMinutes = $dailyLogs
            ->where('status', LogStatus::Approved)
            ->sum(fn ($log) => $log->total_minutes);

        $approvedHours = floor($totalApprovedMinutes / 60);
        $approvedRemainingMinutes = $totalApprovedMinutes % 60;
        $totalHoursFormatted = $approvedRemainingMinutes > 0
            ? "{$approvedHours} jam {$approvedRemainingMinutes} mnt"
            : "{$approvedHours} jam";

        // Mentoring logs
        $mentoringLogs = MentoringLog::where('student_id', $user->id)
            ->latest('date')
            ->get();

        $totalMentoring = $mentoringLogs->count();
        $approvedMentoring = $mentoringLogs->where('status', LogStatus::Approved)->count();
        $pendingMentoring = $mentoringLogs->where('status', LogStatus::Pending)->count();

        return view('livewire.mahasiswa.dashboard', [
            'user' => $user,
            'group' => $group,
            'period' => $period,
            'daysRemaining' => $daysRemaining,
            'periodStatusLabel' => $periodStatusLabel,
            'dpls' => $dpls,
            'programParticipants' => $programParticipants,
            'programsCount' => $programsCount,
            'programsApproved' => $programsApproved,
            'programsSubmitted' => $programsSubmitted,
            'programsNeedsRevision' => $programsNeedsRevision,
            'programsDraft' => $programsDraft,
            'recentPrograms' => $programParticipants->take(4),
            'dailyLogs' => $dailyLogs,
            'totalDailyLogs' => $totalDailyLogs,
            'approvedDailyLogs' => $approvedDailyLogs,
            'pendingDailyLogs' => $pendingDailyLogs,
            'totalHoursFormatted' => $totalHoursFormatted,
            'recentDailyLogs' => $dailyLogs->take(4),
            'mentoringLogs' => $mentoringLogs,
            'totalMentoring' => $totalMentoring,
            'approvedMentoring' => $approvedMentoring,
            'pendingMentoring' => $pendingMentoring,
        ]);
    }
}
