<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Url;

class Logbook extends Component
{
    #[Url]
    public $selectedWeek = 'all';

    // View Modal State
    public $viewLogData = null;

    public function viewLog($id)
    {
        $log = DailyLog::with('activities')->where('student_id', Auth::id())->findOrFail($id);
        
        $user = Auth::user();
        $group = $user->group()->with(['period'])->first();
        $period = $group?->period;
        
        $dayDiff = $period ? $period->start_date->diffInDays($log->date) : 0;
        $log->day_number = $dayDiff + 1;
        $log->week_number = floor($dayDiff / 7) + 1;
        
        $this->viewLogData = $log;
        \Flux::modal('log-view-modal')->show();
    }

    public function render()
    {
        $user = Auth::user();
        
        $group = $user->group()->with(['period'])->first();
        $period = $group?->period;
        
        $query = DailyLog::with('activities')->where('student_id', $user->id);

        $logs = $query->orderBy('date', 'asc')->get();

        $logsGroupedByWeek = [];
        if ($period) {
            foreach ($logs as $log) {
                $dayDiff = $period->start_date->diffInDays($log->date);
                $weekNum = floor($dayDiff / 7) + 1;
                $log->day_number = $dayDiff + 1;
                
                if (!isset($logsGroupedByWeek[$weekNum])) {
                    $logsGroupedByWeek[$weekNum] = [];
                }
                $logsGroupedByWeek[$weekNum][] = $log;
            }
        }

        // Apply week filter if selected
        $filteredLogsGrouped = [];
        if ($this->selectedWeek !== 'all') {
            if (isset($logsGroupedByWeek[(int)$this->selectedWeek])) {
                $filteredLogsGrouped[(int)$this->selectedWeek] = $logsGroupedByWeek[(int)$this->selectedWeek];
            }
        } else {
            $filteredLogsGrouped = $logsGroupedByWeek;
        }

        krsort($filteredLogsGrouped); // Latest weeks first

        return view('livewire.mahasiswa.logbook', [
            'logsGroupedByWeek' => $filteredLogsGrouped,
            'allWeeks' => array_keys($logsGroupedByWeek),
            'student' => $user,
            'logs' => $logs,
            'stats' => [
                'total' => $logs->count(),
                'approved' => $logs->where('status', LogStatus::Approved)->count(),
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
                'totalHours' => $this->calculateTotalHours($logs),
            ],
        ]);
    }

    private function calculateTotalHours($logs): string
    {
        $totalMinutes = 0;

        foreach ($logs as $log) {
            foreach ($log->activities as $activity) {
                $start = Carbon::parse($activity->start_time);
                $end = Carbon::parse($activity->end_time);
                $totalMinutes += $start->diffInMinutes($end);
            }
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return $minutes > 0 ? "{$hours} jam {$minutes} menit" : "{$hours} jam";
    }
}
