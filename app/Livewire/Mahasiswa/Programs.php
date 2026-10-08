<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Models\Program;
use App\Models\ProgramParticipant;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
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

    #[Computed]
    public function selectedProgram()
    {
        if (! $this->selectedProgramId) {
            return null;
        }

        return Program::with('participants')->find($this->selectedProgramId);
    }

    #[Computed]
    public function selectedParticipant()
    {
        if (! $this->selectedParticipantId) {
            return null;
        }

        return ProgramParticipant::with('outputs')->find($this->selectedParticipantId);
    }

    public function confirmDelete(int $participantId)
    {
        $this->participantToDelete = $participantId;
        $this->js('$flux.modal("delete-participant").show()');
    }

    public function deleteParticipant()
    {
        if (! $this->participantToDelete) {
            return;
        }

        $participant = ProgramParticipant::with('program')->where('student_id', Auth::id())->findOrFail($this->participantToDelete);
        if ($participant->status === ProgramStatus::Draft) {
            $program = $participant->program;
            $participant->delete();

            // If it's an individual program, delete the program entirely
            if ($program->student_id === Auth::id()) {
                $program->delete();
            }

            Flux::toast(variant: 'success', heading: 'Dihapus', text: 'Data program berhasil dihapus.');
        }

        $this->participantToDelete = null;
        $this->js('$flux.modal("delete-participant").close()');
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
        if (! $this->participantToSubmit) {
            return;
        }

        $participant = ProgramParticipant::where('student_id', Auth::id())->findOrFail($this->participantToSubmit);
        if ($participant->status === ProgramStatus::Draft || $participant->status === ProgramStatus::NeedsRevision) {
            $participant->update(['status' => ProgramStatus::Submitted]);
            Flux::toast(variant: 'success', heading: 'LRK Diajukan', text: 'Rencana program berhasil diajukan ke Dosen KKN.');
        }

        $this->participantToSubmit = null;
        $this->js('$flux.modal("submit-lrk").close()');
    }

    public function confirmSubmitLpk(int $participantId)
    {
        $this->participantToSubmit = $participantId;
        $this->js('$flux.modal("submit-lpk").show()');
    }

    public function submitLpk()
    {
        if (! $this->participantToSubmit) {
            return;
        }

        $participant = ProgramParticipant::where('student_id', Auth::id())->findOrFail($this->participantToSubmit);
        if ($participant->lpk_status === ProgramStatus::Draft || $participant->lpk_status === ProgramStatus::NeedsRevision) {
            $participant->update([
                'lpk_status' => ProgramStatus::Submitted,
            ]);
            Flux::toast(variant: 'success', heading: 'LPK Diajukan', text: 'Laporan program berhasil diajukan ke Dosen KKN.');
        }

        $this->participantToSubmit = null;
        $this->js('$flux.modal("submit-lpk").close()');
    }

    public function render()
    {
        $user = Auth::user();

        // All Group Programs
        $allPrograms = Program::where('group_id', $user->group_id)
            ->with(['programType', 'participants' => function ($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->get();

        $multidisiplinPrograms = $allPrograms->where('programType.code', 'multidisiplin')->filter(function ($prog) {
            return $prog->participants->isNotEmpty();
        });

        $sosmasPrograms = $allPrograms->where('programType.code', 'sosial_kemasyarakatan')->where('student_id', $user->id);
        $lainnyaPrograms = $allPrograms->where('programType.code', 'lainnya')->where('student_id', $user->id);

        $isMultidisiplinFilled = $multidisiplinPrograms->count() >= 2;
        if ($isMultidisiplinFilled) {
            foreach ($multidisiplinPrograms as $prog) {
                $part = $prog->participants->first();
                if (! $part || empty($part->role_in_program) || empty($part->responsibility) || empty($part->execution_date)) {
                    $isMultidisiplinFilled = false;
                    break;
                }
            }
        }

        $hasSosmas = $sosmasPrograms->count() > 0;

        $joinedIds = ProgramParticipant::where('student_id', $user->id)->pluck('program_id');
        $hasAvailableMultidisiplin = Program::where('group_id', $user->group_id)
            ->whereType('multidisiplin')
            ->whereNotIn('id', $joinedIds)
            ->exists();

        return view('livewire.mahasiswa.programs', [
            'multidisiplinPrograms' => $multidisiplinPrograms,
            'sosmasPrograms' => $sosmasPrograms,
            'lainnyaPrograms' => $lainnyaPrograms,
            'isMultidisiplinFilled' => $isMultidisiplinFilled,
            'hasSosmas' => $hasSosmas,
            'hasAvailableMultidisiplin' => $hasAvailableMultidisiplin,
        ]);
    }
}
