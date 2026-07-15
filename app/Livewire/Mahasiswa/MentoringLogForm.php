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
        return [
            'date' => 'required|date',
            'topic' => 'required|string|max:255',
            'discussion_summary' => 'required|string',
            'program_id' => 'nullable|exists:programs,id',
            'target_group' => 'nullable|string|max:255',
            'student_count' => 'nullable|integer|min:1',
            'output' => 'nullable|string|max:255',
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
        $group = $user->group()->with(['period'])->first();
        $period = $group?->period;

        if ($period) {
            $logDate = \Carbon\Carbon::parse($this->date);
            if ($logDate->lt($period->start_date) || $logDate->gt($period->end_date)) {
                $this->addError('date', 'Tanggal harus berada dalam periode KKN (' . $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y') . ').');
                return;
            }
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
                'status' => LogStatus::Pending,
            ]);
        }

        session()->flash('success', 'Catatan pembimbingan berhasil disimpan.');
        return $this->redirect(route('mentoring-logs.index'), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();
        $programs = Program::where('student_id', $user->id)
            ->orWhere('group_id', $user->group_id)
            ->orderBy('sequence')
            ->get();

        return view('livewire.mahasiswa.mentoring-log-form', [
            'programs' => $programs,
        ]);
    }
}
