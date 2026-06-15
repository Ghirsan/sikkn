<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Programs extends Component
{
    public ?int $participantToDelete = null;

    public ?int $selectedProgramId = null;
    public ?int $selectedParticipantId = null;

    public function viewProgram(int $programId, ?int $participantId = null)
    {
        $this->selectedProgramId = $programId;
        $this->selectedParticipantId = $participantId;
        $this->js('$flux.modal("view-program").show()');
    }

    #[\Livewire\Attributes\Computed]
    public function selectedProgram()
    {
        if (!$this->selectedProgramId) return null;
        return Program::with('participants')->find($this->selectedProgramId);
    }

    #[\Livewire\Attributes\Computed]
    public function selectedParticipant()
    {
        if (!$this->selectedParticipantId) return null;
        return \App\Models\ProgramParticipant::find($this->selectedParticipantId);
    }

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
        \Flux\Flux::toast(variant: 'success', heading: 'Dihapus', text: 'Data program berhasil dihapus.');
    }

    // ─── GENERAL ACTIONS ──────────────────────────────────────────

    public ?int $participantToSubmit = null;

    public function confirmSubmitLrk(int $participantId)
    {
        $this->participantToSubmit = $participantId;
        $this->js('$flux.modal("submit-lrk").show()');
    }

    public function submitLrk()
    {
        if (!$this->participantToSubmit) return;

        $participant = \App\Models\ProgramParticipant::where('student_id', Auth::id())->findOrFail($this->participantToSubmit);
        if ($participant->status === ProgramStatus::Draft) {
            $participant->update(['status' => ProgramStatus::Submitted]);
            \Flux\Flux::toast(variant: 'success', heading: 'LRK Diajukan', text: 'Rencana program berhasil diajukan ke DPL.');
        }

        $this->participantToSubmit = null;
        $this->js('$flux.modal("submit-lrk").close()');
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
