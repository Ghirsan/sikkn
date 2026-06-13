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
    public bool $showFormModal = false;

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
