<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Programs extends Component
{
    public string $filterType = '';
    
    // Form State
    public ?int $programId = null;
    public string $title = '';
    public string $type = 'sosial_kemasyarakatan';
    public string $theme = '';
    public string $problem_potential = '';
    public string $target = '';

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'theme' => 'nullable|string|max:255',
            'problem_potential' => 'nullable|string',
            'target' => 'nullable|string',
        ];
    }

    public function create()
    {
        $this->reset(['programId', 'title', 'theme', 'problem_potential', 'target']);
        $this->type = ProgramType::SosialKemasyarakatan->value; // default
        $this->resetValidation();
    }

    public function edit(int $id)
    {
        $program = Program::where('student_id', Auth::id())->findOrFail($id);
        
        // Ensure only editable programs can be edited
        if (!$program->isEditable()) {
            return;
        }

        $this->programId = $program->id;
        $this->title = $program->title;
        $this->type = $program->type->value;
        $this->theme = $program->theme ?? '';
        $this->problem_potential = $program->problem_potential ?? '';
        $this->target = $program->target ?? '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        
        // If student is not in a group yet, they shouldn't create a program. Let's assume group_id exists for now.
        $studentGroup = \App\Models\GroupStudent::where('student_id', $user->id)->first();
        if (!$studentGroup) {
            session()->flash('error', __('Anda harus masuk ke dalam kelompok terlebih dahulu sebelum mengajukan program.'));
            return;
        }

        if ($this->programId) {
            $program = Program::where('student_id', $user->id)->findOrFail($this->programId);
            
            if (!$program->isEditable()) {
                return;
            }

            $program->update([
                'title' => $this->title,
                'type' => $this->type,
                'theme' => $this->theme,
                'problem_potential' => $this->problem_potential,
                'target' => $this->target,
                // If it was needs_revision, maybe change it back to Draft or Submitted? Usually editing a NeedsRevision draft changes it.
                // We leave status as is, or set to Draft.
            ]);
            
            // If they fixed a revision, maybe we clear the note? 
            if ($program->status === ProgramStatus::NeedsRevision) {
                $program->update(['status' => ProgramStatus::Draft, 'revision_note' => null]);
            }
        } else {
            Program::create([
                'student_id' => $user->id,
                'group_id' => $studentGroup->group_id,
                'title' => $this->title,
                'type' => $this->type,
                'theme' => $this->theme,
                'problem_potential' => $this->problem_potential,
                'target' => $this->target,
                'status' => ProgramStatus::Draft,
            ]);
        }

        $this->js('$flux.modal("program-modal").close()');
    }

    public function delete(int $id)
    {
        $program = Program::where('student_id', Auth::id())->findOrFail($id);
        
        if ($program->status === ProgramStatus::Draft) {
            $program->delete();
        }
    }

    public function submitProgram(int $id)
    {
        $program = Program::where('student_id', Auth::id())->findOrFail($id);
        
        if ($program->status === ProgramStatus::Draft) {
            $program->update(['status' => ProgramStatus::Submitted]);
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = Program::where('student_id', $user->id);

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        $programs = $query->latest()->get();

        return view('livewire.mahasiswa.programs', [
            'programs' => $programs,
            'stats' => [
                'draft' => Program::where('student_id', $user->id)->where('status', ProgramStatus::Draft)->count(),
                'submitted' => Program::where('student_id', $user->id)->where('status', ProgramStatus::Submitted)->count(),
                'needs_revision' => Program::where('student_id', $user->id)->where('status', ProgramStatus::NeedsRevision)->count(),
                'approved' => Program::where('student_id', $user->id)->where('status', ProgramStatus::Approved)->count(),
            ],
            'programTypes' => ProgramType::cases(),
        ]);
    }
}
