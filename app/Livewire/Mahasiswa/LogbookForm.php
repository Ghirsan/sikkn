<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class LogbookForm extends Component
{
    use WithFileUploads;

    #[Url]
    public ?int $logId = null;

    public $date = '';
    public $importantNotes = '';
    public $activities = [];
    public $notesImage = null;
    public $existingImagePath = null;

    protected $rules = [
        'date' => 'required|date',
        'importantNotes' => 'nullable|string',
        'notesImage' => 'nullable|image|max:2048',
        'activities' => 'required|array|min:1',
        'activities.*.start_time' => 'required|date_format:H:i',
        'activities.*.end_time' => 'required|date_format:H:i|after:activities.*.start_time',
        'activities.*.activity_description' => 'required|string',
    ];

    public function mount()
    {
        if ($this->logId) {
            $log = DailyLog::with('activities')->where('student_id', Auth::id())->findOrFail($this->logId);
            
            if ($log->status === LogStatus::Approved) {
                return redirect()->route('logbook.index');
            }

            $this->date = $log->date->format('Y-m-d');
            $this->importantNotes = $log->important_notes;
            $this->existingImagePath = $log->image_path;
            
            foreach ($log->activities as $activity) {
                $this->activities[] = [
                    'start_time' => \Carbon\Carbon::parse($activity->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($activity->end_time)->format('H:i'),
                    'activity_description' => $activity->activity_description,
                ];
            }
        }

        if (empty($this->activities)) {
            $this->addActivity();
        }
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

        // Handle image upload
        $imagePath = $this->existingImagePath;
        if ($this->notesImage) {
            // Delete old image if replacing
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->notesImage->store('logbook-images', 'public');
        }

        if ($this->logId) {
            $log = DailyLog::where('student_id', Auth::id())->findOrFail($this->logId);
            if ($log->status === LogStatus::Approved) {
                return redirect()->route('logbook.index');
            }
            $log->update([
                'date' => $this->date,
                'important_notes' => $this->importantNotes,
                'image_path' => $imagePath,
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
                'image_path' => $imagePath,
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

        session()->flash('success', 'Catatan harian berhasil disimpan.');
        return $this->redirect(route('logbook.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.mahasiswa.logbook-form');
    }
}
