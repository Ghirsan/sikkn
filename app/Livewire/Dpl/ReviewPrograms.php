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
        $user = Auth::user();
        $groupIds = $user->supervisedGroups()->pluck('id');

        $query = Program::whereIn('group_id', $groupIds)->with(['student', 'group']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.dpl.review-programs', [
            'programs' => $query->latest()->get(),
            'stats' => [
                'pending' => Program::whereIn('group_id', $groupIds)->where('status', ProgramStatus::Submitted)->count(),
                'approved' => Program::whereIn('group_id', $groupIds)->where('status', ProgramStatus::Approved)->count(),
                'revision' => Program::whereIn('group_id', $groupIds)->where('status', ProgramStatus::NeedsRevision)->count(),
                'total' => Program::whereIn('group_id', $groupIds)->count(),
            ],
        ]);
    }

    private function getAuthorizedProgram(int $programId): Program
    {
        $groupIds = Auth::user()->supervisedGroups()->pluck('id');

        return Program::whereIn('group_id', $groupIds)->findOrFail($programId);
    }
}
