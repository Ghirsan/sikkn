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
        $group = $user->group()->first();
        
        $dayDiff = ($group && $group->start_date) ? Carbon::parse($group->start_date)->diffInDays($log->date) : 0;
        $log->day_number = $dayDiff + 1;
        $log->week_number = floor($dayDiff / 7) + 1;
        
        $this->viewLogData = $log;
        \Flux::modal('log-view-modal')->show();
    }

    public function submitLog($id)
    {
        $log = DailyLog::where('student_id', Auth::id())->findOrFail($id);
        if ($log->status === LogStatus::Draft) {
            $log->update(['status' => LogStatus::Pending]);
            session()->flash('success', 'Logbook berhasil diajukan.');
        }
    }

    public function render()
    {
        $user = Auth::user();
        
        $group = $user->group()->first();
        
        $query = DailyLog::with('activities')->where('student_id', $user->id);

        $logs = $query->orderBy('date', 'asc')->get();
        
        $logsByDate = $logs->keyBy(function($log) {
            return $log->date->format('Y-m-d');
        });

        $logsGroupedByWeek = [];
        $allWeeks = [];

        if ($group && $group->start_date && $group->end_date) {
            $startDate = Carbon::parse($group->start_date);
            $endDate = Carbon::parse($group->end_date);
            $today = Carbon::today();
            
            if ($endDate->gt($today)) {
                $endDate = $today;
            }
            
            $currentDate = $startDate->copy();
            
            $dayNumber = 1;
            while ($currentDate->lte($endDate)) {
                $dateStr = $currentDate->format('Y-m-d');
                $weekNum = floor(($dayNumber - 1) / 7) + 1;
                
                $log = $logsByDate->get($dateStr);
                
                if (!isset($logsGroupedByWeek[$weekNum])) {
                    $logsGroupedByWeek[$weekNum] = [];
                    $allWeeks[] = $weekNum;
                }
                
                $logsGroupedByWeek[$weekNum][] = [
                    'date' => $currentDate->copy(),
                    'dateStr' => $dateStr,
                    'day_number' => $dayNumber,
                    'week_number' => $weekNum,
                    'log' => $log,
                ];
                
                $currentDate->addDay();
                $dayNumber++;
            }
        }

        // Apply week filter if selected
        $filteredLogsGrouped = [];
        if ($this->selectedWeek !== 'all') {
            if (isset($logsGroupedByWeek[(int)$this->selectedWeek])) {
                $filteredLogsGrouped[(int)$this->selectedWeek] = array_reverse($logsGroupedByWeek[(int)$this->selectedWeek]);
            }
        } else {
            foreach ($logsGroupedByWeek as $weekNum => $days) {
                $filteredLogsGrouped[$weekNum] = array_reverse($days);
            }
        }

        krsort($filteredLogsGrouped); // Latest weeks first
        sort($allWeeks);

        return view('livewire.mahasiswa.logbook', [
            'logsGroupedByWeek' => $filteredLogsGrouped,
            'allWeeks' => $allWeeks,
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
