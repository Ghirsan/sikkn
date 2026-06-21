<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Url;

class ProgramForm extends Component
{
    #[Url]
    public string $action = 'create'; // 'create', 'edit', 'lpk'
    
    #[Url]
    public ?string $type = null;

    #[Url]
    public ?int $programId = null;

    #[Url]
    public ?int $participantId = null;

    public string $formMode = 'edit_program';

    // Program Fields (Programs Table)
    public string $title = '';
    public string $problem_potential = '';
    public string $location = '';
    public string $target_audience = '';
    public string $output_target = '';
    public string $method = '';
    public ?string $execution_date = null;

    // Participant Fields (Participants Table - LRK Phase)
    public string $role_in_program = '';
    public string $responsibility = '';

    // Participant Fields (Participants Table - LPK Phase)
    public string $achievement = '';
    public string $obstacle = '';
    public string $solution = '';

    public function mount()
    {
        $user = Auth::user();

        if ($this->action === 'create') {
            $this->formMode = 'create_individual';
            $this->type = $this->type ?? ProgramType::Lainnya->value;
        } elseif ($this->action === 'edit' && $this->programId) {
            $program = Program::where('group_id', $user->group_id)->findOrFail($this->programId);
            $this->type = $program->type->value;
            
            $isVideoProfile = $this->isVideoProfile($program->title);

            if ($program->type === ProgramType::SosialKemasyarakatan || $program->type === ProgramType::Lainnya) {
                $this->formMode = 'create_individual';
                $this->title = $program->title;
            } elseif ($isVideoProfile) {
                $this->formMode = 'edit_peran';
                $this->title = $program->title;
            } else {
                $this->formMode = 'edit_program';
                $this->title = $program->title;
                $this->problem_potential = $program->problem_potential ?? '';
                $this->location = $program->location ?? '';
                $this->method = $program->method ?? '';
                $this->target_audience = $program->target_audience ?? '';
                $this->output_target = $program->output_target ?? '';
                $this->execution_date = $program->execution_date?->format('Y-m-d');
            }

            if ($this->participantId) {
                $participant = $program->participants()->where('student_id', $user->id)->findOrFail($this->participantId);
                $this->role_in_program = $participant->role_in_program ?? '';
                $this->responsibility = $participant->responsibility ?? '';
            } else {
                $participant = $program->participants()->where('student_id', $user->id)->first();
                if ($participant) {
                    $this->participantId = $participant->id;
                    $this->role_in_program = $participant->role_in_program ?? '';
                    $this->responsibility = $participant->responsibility ?? '';
                }
            }
        } elseif ($this->action === 'lpk' && $this->participantId) {
            $this->formMode = 'lpk';
            $participant = ProgramParticipant::with('program')->where('student_id', $user->id)->findOrFail($this->participantId);
            
            if ($participant->status !== ProgramStatus::Approved) {
                return redirect()->route('programs.index');
            }

            $this->title = $participant->program->title;
            $this->achievement = $participant->achievement ?? '';
            $this->obstacle = $participant->obstacle ?? '';
            $this->solution = $participant->solution ?? '';
        }
    }

    private function isVideoProfile(?string $title): bool
    {
        if (!$title) return false;
        return str_contains(strtolower($title), 'video profile') || 
               str_contains(strtolower($title), 'video profil') ||
               str_contains(strtolower($title), 'video dokumenter');
    }

    public function save()
    {
        if ($this->action === 'lpk') {
            return $this->saveLpk();
        }

        $user = Auth::user();
        if (!$user->group_id) return;

        // Validation based on mode
        if ($this->formMode === 'edit_peran') {
            $this->validate([
                'role_in_program' => 'required|string',
                'responsibility' => 'required|string',
            ]);
        } elseif ($this->formMode === 'create_individual') {
            $this->validate([
                'title' => 'required|string|max:255',
                'role_in_program' => 'required|string',
                'responsibility' => 'required|string',
                'execution_date' => 'required|date',
            ]);
        } else {
            $this->validate([
                'title' => 'required|string|max:255',
                'problem_potential' => 'required|string',
                'location' => 'required|string',
                'method' => 'required|string',
                'target_audience' => 'required|string',
                'output_target' => 'required|string',
                'execution_date' => 'required|date',
            ]);
        }

        // 1. Handle Program Creation/Update
        if ($this->programId) {
            $program = Program::where('group_id', $user->group_id)->findOrFail($this->programId);
            if ($this->formMode === 'edit_program') {
                $program->update([
                    'title' => $this->title,
                    'problem_potential' => $this->problem_potential,
                    'location' => $this->location,
                    'method' => $this->method,
                    'target_audience' => $this->target_audience,
                    'output_target' => $this->output_target,
                    'execution_date' => $this->execution_date,
                ]);
            } elseif ($this->formMode === 'create_individual') {
                $program->update([
                    'title' => $this->title,
                    'execution_date' => $this->execution_date,
                ]);
            }
        } else {
            if ($this->formMode === 'create_individual') {
                if ($this->type === ProgramType::SosialKemasyarakatan->value) {
                    $existingSosmas = Program::where('student_id', $user->id)
                        ->where('type', ProgramType::SosialKemasyarakatan)
                        ->exists();

                    if ($existingSosmas) {
                        $this->addError('title', 'Maksimal 1 program sosial kemasyarakatan yang diizinkan.');
                        return;
                    }
                }

                $nextSequence = Program::where('student_id', $user->id)
                    ->where('type', $this->type)
                    ->max('sequence') + 1;

                $program = Program::create([
                    'student_id' => $user->id,
                    'group_id' => $user->group_id,
                    'title' => $this->title,
                    'type' => $this->type,
                    'execution_date' => $this->execution_date,
                    'sequence' => $nextSequence,
                ]);
                $this->programId = $program->id;
            }
        }

        // 2. Handle Participant Creation/Update
        if ($this->participantId) {
            $participant = $program->participants()->where('student_id', $user->id)->findOrFail($this->participantId);
            $participant->update([
                'role_in_program' => $this->role_in_program,
                'responsibility' => $this->responsibility,
                'status' => ProgramStatus::Draft,
                'revision_note' => null,
            ]);
        } else {
            $program->participants()->create([
                'student_id' => $user->id,
                'role_in_program' => $this->role_in_program,
                'responsibility' => $this->responsibility,
                'status' => ProgramStatus::Draft,
            ]);
        }

        session()->flash('success', 'Data program berhasil disimpan.');
        return $this->redirect(route('programs.index'), navigate: true);
    }

    private function saveLpk()
    {
        $this->validate([
            'achievement' => 'required|string',
            'obstacle' => 'required|string',
            'solution' => 'required|string',
        ]);

        $participant = ProgramParticipant::where('student_id', Auth::id())->findOrFail($this->participantId);
        
        $participant->update([
            'achievement' => $this->achievement,
            'obstacle' => $this->obstacle,
            'solution' => $this->solution,
            'lpk_status' => ProgramStatus::Submitted,
            'lpk_revision_note' => null,
        ]);

        session()->flash('success', 'Laporan LPK Anda berhasil disimpan.');
        return $this->redirect(route('programs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.mahasiswa.program-form');
    }
}
