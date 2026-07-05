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
    public string $participant_title = '';
    public string $role_in_program = '';
    public string $responsibility = '';

    // Participant Fields (Participants Table - LPK Phase)
    public string $achievement = '';
    public string $obstacle = '';
    public string $solution = '';
    public string $execution_description = '';
    
    public bool $isLpkMultidisiplin = false;
    public bool $isLpkVideoProfile = false;

    public function mount()
    {
        $user = Auth::user();

        if ($this->action === 'create') {
            $this->formMode = 'create_individual';
            $this->type = $this->type ?? ProgramType::Lainnya->value;
        } elseif ($this->action === 'edit' && $this->programId) {
            $program = Program::where('group_id', $user->group_id)->findOrFail($this->programId);
            $this->type = $program->type->value;
            
            $isVideoProfile = $this->isVideoProfile($program);

            if ($program->type === ProgramType::SosialKemasyarakatan || $program->type === ProgramType::Lainnya) {
                $this->formMode = 'create_individual';
                $this->title = $program->title;
            } elseif ($isVideoProfile) {
                $this->formMode = 'edit_peran';
                $this->title = $program->title;
            } else {
                $this->formMode = 'edit_program';
                $this->title = $program->title;
            }

            if ($this->participantId) {
                $participant = $program->participants()->where('student_id', $user->id)->findOrFail($this->participantId);
                $this->participant_title = $participant->participant_title ?? '';
                $this->role_in_program = $participant->role_in_program ?? '';
                $this->responsibility = $participant->responsibility ?? '';
                if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual' || $this->formMode === 'edit_peran') {
                    $this->execution_date = $participant->execution_date?->format('Y-m-d');
                }
                if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual') {
                    $this->problem_potential = $participant->problem_potential ?? '';
                    $this->location = $participant->location ?? '';
                    $this->method = $participant->method ?? '';
                    $this->target_audience = $participant->target_audience ?? '';
                    $this->output_target = $participant->output_target ?? '';
                    $this->execution_date = $participant->execution_date?->format('Y-m-d');
                }
            } else {
                $participant = $program->participants()->where('student_id', $user->id)->first();
                if ($participant) {
                    $this->participantId = $participant->id;
                    $this->participant_title = $participant->participant_title ?? '';
                    $this->role_in_program = $participant->role_in_program ?? '';
                    $this->responsibility = $participant->responsibility ?? '';
                    if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual' || $this->formMode === 'edit_peran') {
                        $this->execution_date = $participant->execution_date?->format('Y-m-d');
                    }
                    if ($this->formMode === 'edit_program' || $this->formMode === 'create_individual') {
                        $this->problem_potential = $participant->problem_potential ?? '';
                        $this->location = $participant->location ?? '';
                        $this->method = $participant->method ?? '';
                        $this->target_audience = $participant->target_audience ?? '';
                        $this->output_target = $participant->output_target ?? '';
                        $this->execution_date = $participant->execution_date?->format('Y-m-d');
                    }
                }
            }
        } elseif ($this->action === 'lpk' && $this->participantId) {
            $this->formMode = 'lpk';
            $participant = ProgramParticipant::with('program')->where('student_id', $user->id)->findOrFail($this->participantId);
            
            if ($participant->status !== ProgramStatus::Approved) {
                return redirect()->route('lpk.index');
            }

            $this->title = $participant->program->title;
            $this->isLpkVideoProfile = $this->isVideoProfile($participant->program);
            $this->isLpkMultidisiplin = $participant->program->type === ProgramType::Multidisiplin && !$this->isLpkVideoProfile;
            
            $this->achievement = $participant->achievement ?? '';
            $this->obstacle = $participant->obstacle ?? '';
            $this->solution = $participant->solution ?? '';
            $this->execution_description = $participant->execution_description ?? '';
        }
    }

    private function isVideoProfile(?Program $program): bool
    {
        if (!$program) return false;
        
        // In this system, Multidisiplin 3 is specifically the Video Profile
        if ($program->type === ProgramType::Multidisiplin && $program->sequence == 3) {
            return true;
        }

        $title = $program->title;
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
                'execution_date' => 'required|date',
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
                'participant_title' => 'required|string|max:255',
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
            if ($this->formMode === 'create_individual') {
                $program->update([
                    'title' => $this->title,
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
                    'sequence' => $nextSequence,
                ]);
                $this->programId = $program->id;
            }
        }

        // 2. Handle Participant Creation/Update
        $participantData = [
            'status' => ProgramStatus::Draft,
        ];

        // Ensure we preserve existing role/responsibility for edit_program
        // or update them if provided in create_individual/edit_peran
        $participantData['role_in_program'] = $this->role_in_program ?: null;
        $participantData['responsibility'] = $this->responsibility ?: null;
        
        if ($this->formMode === 'edit_peran' || $this->formMode === 'create_individual') {
            $participantData['execution_date'] = $this->execution_date ?: null;
            $participantData['participant_title'] = null;
            $participantData['problem_potential'] = null;
            $participantData['location'] = null;
            $participantData['method'] = null;
            $participantData['target_audience'] = null;
            $participantData['output_target'] = null;
        } elseif ($this->formMode === 'edit_program') {
            $participantData['participant_title'] = $this->participant_title ?: null;
            $participantData['problem_potential'] = $this->problem_potential ?: null;
            $participantData['location'] = $this->location ?: null;
            $participantData['method'] = $this->method ?: null;
            $participantData['target_audience'] = $this->target_audience ?: null;
            $participantData['output_target'] = $this->output_target ?: null;
            $participantData['execution_date'] = $this->execution_date ?: null;
        }

        if ($this->participantId) {
            $participant = $program->participants()->where('student_id', $user->id)->findOrFail($this->participantId);
            $participantData['revision_note'] = null;
            $participant->update($participantData);
        } else {
            $participantData['student_id'] = $user->id;
            $program->participants()->create($participantData);
        }

        session()->flash('success', 'Data program berhasil disimpan.');
        return $this->redirect(route('programs.index'), navigate: true);
    }

    private function saveLpk()
    {
        $participant = ProgramParticipant::with('program')->where('student_id', Auth::id())->findOrFail($this->participantId);
        $isVideo = $this->isVideoProfile($participant->program);
        $isMultidisiplin = $participant->program->type === ProgramType::Multidisiplin && !$isVideo;

        if ($isMultidisiplin) {
            $this->validate([
                'execution_description' => 'required|string',
                'achievement' => 'required|string',
                'obstacle' => 'required|string',
                'solution' => 'required|string',
            ]);
        } else {
            // For Sosmas/Lainnya/Video Profile: they need to fill 'achievement' as the "Hasil"
            $this->validate([
                'achievement' => 'required|string', // Digunakan untuk menampung "Hasil"
            ]);
        }
        
        $participant->update([
            'execution_description' => $this->execution_description,
            'achievement' => $this->achievement,
            'obstacle' => $this->obstacle,
            'solution' => $this->solution,
        ]);

        session()->flash('success', 'Laporan LPK Anda berhasil disimpan.');
        return $this->redirect(route('programs.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.mahasiswa.program-form');
    }
}
