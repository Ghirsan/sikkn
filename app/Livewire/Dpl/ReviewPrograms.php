<?php

namespace App\Livewire\Dpl;

use App\Enums\ProgramStatus;
use App\Models\Program;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ReviewPrograms extends Component
{
    public string $filterStatus = '';

    public function approve(int $programId): void
    {
        $program = $this->getAuthorizedProgram($programId);
        $program->update(['status' => ProgramStatus::Approved, 'revision_note' => null]);
    }

    public string $revisionNote = '';

    public int $revisingProgramId = 0;

    public function startRevision(int $programId): void
    {
        $this->revisingProgramId = $programId;
        $this->revisionNote = '';
    }

    public function submitRevision(): void
    {
        $this->validate(['revisionNote' => 'required|min:10']);

        $program = $this->getAuthorizedProgram($this->revisingProgramId);
        $program->update([
            'status' => ProgramStatus::NeedsRevision,
            'revision_note' => $this->revisionNote,
        ]);

        $this->revisingProgramId = 0;
        $this->revisionNote = '';
    }

    public function render()
    {
        $groupId = Auth::user()->group_id;

        $query = Program::where('group_id', $groupId)->with(['student', 'group']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.dpl.review-programs', [
            'programs' => $query->latest()->get(),
            'stats' => [
                'pending' => Program::where('group_id', $groupId)->where('status', ProgramStatus::Submitted)->count(),
                'approved' => Program::where('group_id', $groupId)->where('status', ProgramStatus::Approved)->count(),
                'revision' => Program::where('group_id', $groupId)->where('status', ProgramStatus::NeedsRevision)->count(),
                'total' => Program::where('group_id', $groupId)->count(),
            ],
        ]);
    }

    private function getAuthorizedProgram(int $programId): Program
    {
        $groupId = Auth::user()->group_id;

        return Program::where('group_id', $groupId)->findOrFail($programId);
    }
}
