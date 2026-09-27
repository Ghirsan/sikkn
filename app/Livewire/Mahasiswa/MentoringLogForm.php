<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\LogStatus;
use App\Models\MentoringLog;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;

class MentoringLogForm extends Component
{
    #[Url]
    public ?int $logId = null;

    public $date = '';
    public $topic = '';
    public $discussion_summary = '';
    public $program_id = null;
    public $target_group = '';
    public $student_count = null;
    public $output = '';

    protected function rules()
    {
        $group = Auth::user()?->group;
        $maxStudentCount = $group?->students()->count();

        return [
            'date' => 'required|date',
            'topic' => 'required|string|max:255',
            'discussion_summary' => 'required|string',
            'program_id' => 'nullable|exists:programs,id',
            'target_group' => 'nullable|string|max:255',
            'student_count' => array_filter([
                'nullable',
                'integer',
                'min:1',
                $maxStudentCount ? 'max:' . $maxStudentCount : null,
            ]),
            'output' => 'nullable|string|max:255',
        ];
    }

    protected function messages()
    {
        return [
            'student_count.max' => 'Jumlah mahasiswa terlibat tidak boleh melebihi jumlah anggota tim (:max).',
        ];
    }

    public function mount()
    {
        if ($this->logId) {
            $log = MentoringLog::where('student_id', Auth::id())->findOrFail($this->logId);
            
            if ($log->status === LogStatus::Approved) {
                return redirect()->route('mentoring-logs.index');
            }

            $this->date = $log->date->format('Y-m-d');
            $this->topic = $log->topic;
            $this->discussion_summary = $log->discussion_summary;
            $this->program_id = $log->program_id;
            $this->target_group = $log->target_group;
            $this->student_count = $log->student_count;
            $this->output = $log->output;
        }
    }

    public function saveLog()
    {
        $this->validate();

        $user = Auth::user();
        $group = $user->group()->first();

        if ($group && $group->start_date && $group->end_date) {
            $logDate = \Carbon\Carbon::parse($this->date);
            if ($logDate->lt($group->start_date) || $logDate->gt($group->end_date)) {
                $this->addError('date', 'Tanggal harus berada dalam periode kelompok KKN (' . $group->start_date->format('d/m/Y') . ' - ' . $group->end_date->format('d/m/Y') . ').');
                return;
            }
        }

        // Check if a mentoring log already exists for this date
        $existingLogQuery = MentoringLog::where('student_id', Auth::id())
            ->whereDate('date', $this->date);

        if ($this->logId) {
            $existingLogQuery->where('id', '!=', $this->logId);
        }

        if ($existingLogQuery->exists()) {
            $this->addError('date', 'Anda sudah memiliki catatan pembimbingan untuk tanggal ini.');
            return;
        }

        if ($this->logId) {
            $log = MentoringLog::where('student_id', Auth::id())->findOrFail($this->logId);
            if ($log->status === LogStatus::Approved) {
                return redirect()->route('mentoring-logs.index');
            }
            $log->update([
                'date' => $this->date,
                'topic' => $this->topic,
                'discussion_summary' => $this->discussion_summary,
                'program_id' => $this->program_id,
                'target_group' => $this->target_group,
                'student_count' => $this->student_count,
                'output' => $this->output,
                'status' => LogStatus::Draft,
            ]);
        } else {
            MentoringLog::create([
                'group_id' => $group->id,
                'student_id' => Auth::id(),
                'date' => $this->date,
                'topic' => $this->topic,
                'discussion_summary' => $this->discussion_summary,
                'program_id' => $this->program_id,
                'target_group' => $this->target_group,
                'student_count' => $this->student_count,
                'output' => $this->output,
                'status' => LogStatus::Draft,
            ]);
        }

        session()->flash('success', 'Catatan pembimbingan berhasil disimpan sebagai draf.');
        return $this->redirect(route('mentoring-logs.index'), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $programs = Program::where('group_id', $user->group_id)
            ->where(function ($query) use ($user) {
                $query->where('student_id', $user->id)
                      ->orWhereHas('participants', function ($q) use ($user) {
                          $q->where('student_id', $user->id);
                      });
            })
            ->orderBy('type')
            ->orderBy('sequence')
            ->get();
        $group = $user->group()->first();
        $studentCount = $group?->students()->count();

        return view('livewire.mahasiswa.mentoring-log-form', [
            'programs' => $programs,
            'minDate' => $group && $group->start_date ? $group->start_date->format('Y-m-d') : null,
            'maxDate' => $group && $group->end_date ? $group->end_date->format('Y-m-d') : null,
            'maxStudentCount' => $studentCount,
        ]);
    }
}
