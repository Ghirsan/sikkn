<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\DailyLog;
use App\Services\ExternalImagePreviewUrl;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class LogbookForm extends Component
{
    #[Url]
    public ?int $logId = null;

    #[Url]
    public ?string $date = null;

    public $importantNotes = '';

    public $activities = [];

    public $imageUrl = null;

    public $imagePreviewUrl = null;

    public $imageError = null;

    public $imageVerified = false;

    protected $rules = [
        'date' => 'required|date',
        'importantNotes' => 'nullable|string',
        'imageUrl' => 'nullable|url',
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
            $this->imageUrl = $log->image_path;

            if ($this->imageUrl && str_starts_with($this->imageUrl, 'http')) {
                $this->verifyImageUrl();
            }

            foreach ($log->activities as $activity) {
                $this->activities[] = [
                    'start_time' => Carbon::parse($activity->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($activity->end_time)->format('H:i'),
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
            'activity_description' => '',
        ];
    }

    public function removeActivity($index)
    {
        if (count($this->activities) > 1) {
            unset($this->activities[$index]);
            $this->activities = array_values($this->activities);
        }
    }

    public function verifyImageUrl(): void
    {
        if (empty($this->imageUrl)) {
            $this->imagePreviewUrl = null;
            $this->imageVerified = false;
            $this->imageError = null;

            return;
        }

        $this->validateOnly('imageUrl');
        $this->imageVerified = true;

        try {
            $this->imagePreviewUrl = app(ExternalImagePreviewUrl::class)->resolve($this->imageUrl);
            $this->imageError = null;
            $this->resetValidation('imageUrl');
        } catch (\InvalidArgumentException $exception) {
            $this->imagePreviewUrl = null;
            $this->imageError = $exception->getMessage();
            $this->addError('imageUrl', $exception->getMessage());
        }
    }

    public function updatedImageUrl(): void
    {
        $this->imagePreviewUrl = null;
        $this->imageVerified = false;
        $this->imageError = null;
        $this->resetValidation('imageUrl');
    }

    public function saveDraft()
    {
        $this->processSave(LogStatus::Draft);
    }

    public function saveAndSubmit()
    {
        $this->processSave(LogStatus::Pending);
    }

    private function processSave(LogStatus $status)
    {
        $this->validate();

        if ($this->imageUrl) {
            $this->verifyImageUrl();
            if ($this->imageError) {
                return;
            }
        }

        $user = Auth::user();
        $group = $user->group()->first();

        if ($group && $group->start_date && $group->end_date) {
            $logDate = Carbon::parse($this->date);
            if ($logDate->lt($group->start_date) || $logDate->gt($group->end_date)) {
                $this->addError('date', 'Tanggal harus berada dalam periode kelompok KKN ('.$group->start_date->format('d/m/Y').' - '.$group->end_date->format('d/m/Y').').');

                return;
            }
            if ($logDate->gt(Carbon::today())) {
                $this->addError('date', 'Tanggal kegiatan tidak boleh melebihi hari ini.');

                return;
            }
        }

        if ($this->logId) {
            $log = DailyLog::where('student_id', Auth::id())->findOrFail($this->logId);
            if ($log->status === LogStatus::Approved) {
                return redirect()->route('logbook.index');
            }

            $log->update([
                'date' => $this->date,
                'important_notes' => $this->importantNotes,
                'image_path' => $this->imageUrl ?: null,
                'status' => $status,
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
                'image_path' => $this->imageUrl ?: null,
                'status' => $status,
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

        session()->flash('success', $status === LogStatus::Pending ? 'Catatan harian berhasil diajukan.' : 'Catatan harian berhasil disimpan sebagai draf.');

        return $this->redirect(route('logbook.index'), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $group = $user->group()->first();

        return view('livewire.mahasiswa.logbook-form', [
            'minDate' => $group && $group->start_date ? $group->start_date->format('Y-m-d') : null,
            'maxDate' => $group && $group->end_date ? $group->end_date->format('Y-m-d') : null,
        ]);
    }
}
