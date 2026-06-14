<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Programs extends Component
{
    // Form Type State
    public string $formMode = 'edit_program'; // 'edit_program', 'edit_peran', 'lpk'
    
    // Core IDs
    public ?int $programId = null; // ID in programs table
    public ?int $participantId = null; // ID in program_participants table
    
    // Program Fields (Programs Table)
    public string $title = '';
    public string $type = 'lainnya';
    public string $problem_potential = '';
    public string $location = '';
    public string $target_audience = '';
    public string $output_target = '';
    public string $method = '';

    // Participant Fields (Participants Table - LRK Phase)
    public string $role_in_program = '';
    public string $responsibility = '';

    // Participant Fields (Participants Table - LPK Phase)
    public string $achievement = '';
    public string $obstacle = '';
    public string $solution = '';

    protected function resetForms()
    {
        $this->reset([
            'programId', 'participantId', 
            'title', 'problem_potential', 'location', 'target_audience', 'output_target', 'method',
            'role_in_program', 'responsibility',
            'achievement', 'obstacle', 'solution'
        ]);
        $this->type = ProgramType::Lainnya->value;
        $this->resetValidation();
    }

    private function isVideoProfile(?string $title, ?string $theme): bool
    {
        if (!$title && !$theme) return false;
        return str_contains(strtolower($title ?? ''), 'video profile') || 
               str_contains(strtolower($theme ?? ''), 'video profile') ||
               str_contains(strtolower($title ?? ''), 'video profil') ||
               str_contains(strtolower($theme ?? ''), 'video profil') ||
               str_contains(strtolower($title ?? ''), 'video dokumenter');
    }

    // ─── 1. SOSMAS & LAINNYA (Program Individu & Tambahan) ────────

    public function createLainnya()
    {
        $this->formMode = 'create_individual';
        $this->resetForms();
        $this->type = ProgramType::Lainnya->value;
    }

    public function createSosmas()
    {
        $this->formMode = 'create_individual';
        $this->resetForms();
        $this->type = ProgramType::SosialKemasyarakatan->value;
    }

    // ─── 2. EDIT FORM (Dynamic based on Program Type) ─────────────

    public function openForm(int $programId, ?int $participantId = null)
    {
        $this->resetForms();
        $program = Program::where('group_id', Auth::user()->group_id)->findOrFail($programId);
        
        $this->programId = $program->id;
        $this->participantId = $participantId;
        $this->type = $program->type->value;
        
        $isVideoProfile = $this->isVideoProfile($program->title, $program->theme);

        // Determine Form Mode based on Type
        if ($program->type === ProgramType::SosialKemasyarakatan || $program->type === ProgramType::Lainnya) {
            $this->formMode = 'create_individual'; // Individual
            $this->title = $program->title;
        } elseif ($isVideoProfile) {
            $this->formMode = 'edit_peran'; // Video Profile is shared, just fill role
            $this->title = $program->title; // Read-only
        } else {
            $this->formMode = 'edit_program'; // Multidisiplin
            $this->title = $program->title;
            $this->problem_potential = $program->problem_potential ?? '';
            $this->location = $program->location ?? '';
            $this->method = $program->method ?? '';
            $this->target_audience = $program->target_audience ?? '';
            $this->output_target = $program->output_target ?? '';
        }

        // Load existing participant data if joining/editing
        if ($participantId) {
            $participant = $program->participants()->where('student_id', Auth::id())->findOrFail($participantId);
            $this->role_in_program = $participant->role_in_program ?? '';
            $this->responsibility = $participant->responsibility ?? '';
        } else {
            // Check if already joined
            $participant = $program->participants()->where('student_id', Auth::id())->first();
            if ($participant) {
                $this->participantId = $participant->id;
                $this->role_in_program = $participant->role_in_program ?? '';
                $this->responsibility = $participant->responsibility ?? '';
            }
        }
    }

    public function saveForm()
    {
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
            ]);
        } else {
            $this->validate([
                'title' => 'required|string|max:255',
                'problem_potential' => 'required|string',
                'location' => 'required|string',
                'method' => 'required|string',
                'target_audience' => 'required|string',
                'output_target' => 'required|string',
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
                ]);
            } elseif ($this->formMode === 'create_individual') {
                $program->update([
                    'title' => $this->title,
                ]);
            }
        } else {
            if ($this->formMode === 'create_individual') {
                // If it's Sosmas, check max 1
                if ($this->type === ProgramType::SosialKemasyarakatan->value) {
                    $existingSosmas = Program::where('student_id', $user->id)
                        ->where('type', ProgramType::SosialKemasyarakatan)
                        ->exists();

                    if ($existingSosmas) {
                        $this->addError('title', 'Maksimal 1 program sosial kemasyarakatan yang diizinkan.');
                        return;
                    }
                }

                // Creating new Individual program (Sosmas or Lainnya)
                $program = Program::create([
                    'student_id' => $user->id,
                    'group_id' => $user->group_id,
                    'title' => $this->title,
                    'type' => $this->type, // Will be either Sosmas or Lainnya based on state
                ]);
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

        $this->js('$flux.modal("program-modal").close()');
        \Flux::toast('Data program berhasil disimpan.', variant: 'success');
    }

    public ?int $participantToDelete = null;

    public function confirmDelete(int $participantId)
    {
        $this->participantToDelete = $participantId;
        $this->js('$flux.modal("delete-participant").show()');
    }

    public function deleteParticipant()
    {
        if (!$this->participantToDelete) return;

        $participant = \App\Models\ProgramParticipant::with('program')->where('student_id', Auth::id())->findOrFail($this->participantToDelete);
        if ($participant->status === ProgramStatus::Draft) {
            $program = $participant->program;
            $participant->delete();
            
            // If it's an individual program, delete the program entirely
            if ($program->student_id === Auth::id()) {
                $program->delete();
            }
        }

        $this->participantToDelete = null;
        $this->js('$flux.modal("delete-participant").close()');
        \Flux::toast('Data program berhasil dihapus.', variant: 'success');
    }

    // ─── 3. PELAKSANAAN (LPK Phase) ───────────────────────────────

    public function isiLpk(int $participantId)
    {
        $this->formMode = 'lpk';
        $this->resetForms();

        $participant = \App\Models\ProgramParticipant::with('program')->where('student_id', Auth::id())->findOrFail($participantId);
        
        if ($participant->status !== ProgramStatus::Approved) return;

        $this->participantId = $participant->id;
        $this->title = $participant->program->title; // Read-only

        $this->achievement = $participant->achievement ?? '';
        $this->obstacle = $participant->obstacle ?? '';
        $this->solution = $participant->solution ?? '';
    }

    public function saveLpk()
    {
        $this->validate([
            'achievement' => 'required|string',
            'obstacle' => 'required|string',
            'solution' => 'required|string',
        ]);

        $participant = \App\Models\ProgramParticipant::where('student_id', Auth::id())->findOrFail($this->participantId);
        
        $participant->update([
            'achievement' => $this->achievement,
            'obstacle' => $this->obstacle,
            'solution' => $this->solution,
            'lpk_status' => ProgramStatus::Submitted,
            'lpk_revision_note' => null,
        ]);

        $this->js('$flux.modal("program-modal").close()');
        \Flux::toast('LPK berhasil diajukan.', variant: 'success');
    }

    // ─── GENERAL ACTIONS ──────────────────────────────────────────

    public function submitLrk(int $participantId)
    {
        $participant = \App\Models\ProgramParticipant::where('student_id', Auth::id())->findOrFail($participantId);
        if ($participant->status === ProgramStatus::Draft) {
            $participant->update(['status' => ProgramStatus::Submitted]);
            \Flux::toast('Program (LRK) berhasil diajukan ke DPL.', variant: 'success');
        }
    }

    public function render()
    {
        $user = Auth::user();

        // All Group Programs
        $allPrograms = Program::where('group_id', $user->group_id)
            ->with(['participants' => function($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->get();

        $multidisiplinPrograms = $allPrograms->where('type', ProgramType::Multidisiplin);
        $sosmasPrograms = $allPrograms->where('type', ProgramType::SosialKemasyarakatan)->where('student_id', $user->id);
        $lainnyaPrograms = $allPrograms->where('type', ProgramType::Lainnya)->where('student_id', $user->id);

        return view('livewire.mahasiswa.programs', [
            'multidisiplinPrograms' => $multidisiplinPrograms,
            'sosmasPrograms' => $sosmasPrograms,
            'lainnyaPrograms' => $lainnyaPrograms,
        ]);
    }
}
