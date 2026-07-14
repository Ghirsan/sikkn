<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;

class Logbook extends Component
{
    #[Url]
    public $selectedWeek = 'all';

    public function render()
    {
        $user = Auth::user();

        $group = $user->group()->with(['period'])->first();
        $period = $group?->period;

        $logsQuery = DailyLog::with('activities')
            ->where('student_id', $user->id);
            
        if ($period) {
            $logsQuery->whereBetween('date', [$period->start_date, $period->end_date]);
        }
            
        $logs = $logsQuery->orderBy('date', 'asc')->get();

        $logsGroupedByWeek = [];
        $weekNumbers = [];
        
        foreach ($logs as $log) {
            if ($period) {
                $dayDiff = $period->start_date->diffInDays($log->date);
                $weekNumber = floor($dayDiff / 7) + 1;
                $log->day_number = $dayDiff + 1;
            } else {
                $weekNumber = 1;
                $log->day_number = 1;
            }
            
            if (!isset($logsGroupedByWeek[$weekNumber])) {
                $logsGroupedByWeek[$weekNumber] = [];
            }
            $logsGroupedByWeek[$weekNumber][] = $log;
            
            if (!in_array($weekNumber, $weekNumbers)) {
                $weekNumbers[] = $weekNumber;
            }
        }
        
        sort($weekNumbers);

        $filteredLogsGrouped = $logsGroupedByWeek;
        if ($this->selectedWeek !== 'all') {
            $week = (int) str_replace('week-', '', $this->selectedWeek);
            $filteredLogsGrouped = isset($logsGroupedByWeek[$week]) ? [$week => $logsGroupedByWeek[$week]] : [];
        }

        return view('livewire.mahasiswa.logbook', [
            'logs' => $logs,
            'logsGroupedByWeek' => $filteredLogsGrouped,
            'weekNumbers' => $weekNumbers,
            'student' => $user,
            'stats' => [
                'total' => $logs->count(),
                'approved' => $logs->where('status', LogStatus::Approved)->count(),
                'pending' => $logs->where('status', LogStatus::Pending)->count(),
            ],
        ]);
    }

    // Modal State
    public $isEditMode = false;
    public $logId = null;
    public $date = '';
    public $importantNotes = '';
    public $activities = []; // Array of ['start_time' => '', 'end_time' => '', 'activity_description' => '']

    // View Modal State
    public $viewLogData = null;

    protected $rules = [
        'date' => 'required|date',
        'importantNotes' => 'nullable|string',
        'activities' => 'required|array|min:1',
        'activities.*.start_time' => 'required|date_format:H:i',
        'activities.*.end_time' => 'required|date_format:H:i|after:activities.*.start_time',
        'activities.*.activity_description' => 'required|string',
    ];

    public function mount()
    {
        $this->addActivity(); // Start with one empty activity
    }

    public function addActivity()
    {
        $this->activities[] = [
            'start_time' => '',
            'end_time' => '',
            'activity_description' => ''
        ];
    }

    public function removeActivity($index)
    {
        if (count($this->activities) > 1) {
            unset($this->activities[$index]);
            $this->activities = array_values($this->activities);
        }
    }

    public function resetForm()
    {
        $this->isEditMode = false;
        $this->logId = null;
        $this->date = '';
        $this->importantNotes = '';
        $this->activities = [];
        $this->addActivity();
        $this->resetValidation();
    }

    public function editLog($id)
    {
        $this->resetForm();
        
        $log = DailyLog::with('activities')->where('student_id', Auth::id())->findOrFail($id);
        
        if ($log->status === LogStatus::Approved) {
            // Can't edit approved log
            return;
        }

        $this->isEditMode = true;
        $this->logId = $log->id;
        $this->date = $log->date->format('Y-m-d');
        $this->importantNotes = $log->important_notes;
        
        $this->activities = [];
        foreach ($log->activities as $activity) {
            $this->activities[] = [
                'start_time' => \Carbon\Carbon::parse($activity->start_time)->format('H:i'),
                'end_time' => \Carbon\Carbon::parse($activity->end_time)->format('H:i'),
                'activity_description' => $activity->activity_description,
            ];
        }

        if (empty($this->activities)) {
            $this->addActivity();
        }

        $this->dispatch('open-modal', 'log-form-modal');
    }

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
        $this->dispatch('open-modal', 'log-view-modal');
    }

    public function saveLog()
    {
        $this->validate();

        $user = Auth::user();
        $group = $user->group()->with(['period'])->first();
        $period = $group?->period;

        if ($period) {
            $logDate = \Carbon\Carbon::parse($this->date);
            if ($logDate->lt($period->start_date) || $logDate->gt($period->end_date)) {
                $this->addError('date', 'Tanggal harus berada dalam periode KKN (' . $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y') . ').');
                return;
            }
        }

        if ($this->isEditMode) {
            $log = DailyLog::where('student_id', Auth::id())->findOrFail($this->logId);
            if ($log->status === LogStatus::Approved) {
                return;
            }
            $log->update([
                'date' => $this->date,
                'important_notes' => $this->importantNotes,
                // Status remains pending
            ]);
            
            // Recreate activities
            $log->activities()->delete();
        } else {
            // Check if log already exists for this date
            $existingLog = DailyLog::where('student_id', Auth::id())
                ->where('date', $this->date)
                ->first();
                
            if ($existingLog) {
                $this->addError('date', 'Anda sudah membuat logbook untuk tanggal ini.');
                return;
            }

            $log = DailyLog::create([
                'student_id' => Auth::id(),
                'date' => $this->date,
                'important_notes' => $this->importantNotes,
                'status' => LogStatus::Pending,
            ]);
        }

        // Add activities
        foreach ($this->activities as $activity) {
            $log->activities()->create([
                'start_time' => $activity['start_time'],
                'end_time' => $activity['end_time'],
                'activity_description' => $activity['activity_description'],
            ]);
        }

        $this->dispatch('close-modal', 'log-form-modal');
        $this->resetForm();
    }
}
