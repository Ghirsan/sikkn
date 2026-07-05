<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LpkDocuments extends Component
{
    use WithPagination;

    public ?string $filterType = '';
    public ?string $filterStatus = '';
    public string $search = '';
    public string $sortBy = 'execution_date';
    public string $sortDirection = 'asc';
    public ?int $selectedParticipantId = null;

    public function viewProgram(?int $participantId = null)
    {
        $this->selectedParticipantId = $participantId;
        $this->js('$flux.modal("view-program").show()');
    }

    public function updatedFilterType() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[\Livewire\Attributes\Computed]
    public function selectedParticipant()
    {
        if (!$this->selectedParticipantId) return null;
        return ProgramParticipant::with('program')->find($this->selectedParticipantId);
    }

    public function render()
    {
        $user = Auth::user();
        $group = $user->group;

        // All participants in this group's programs
        $participants = $group ? ProgramParticipant::with(['program', 'student'])
            ->whereHas('program', function($q) use ($group) {
                $q->where('group_id', $group->id);
            })->get() : collect();

        $totalParticipants = $participants->count();
        $lpkApprovedCount = $participants->where('lpk_status', ProgramStatus::Approved)->count();
        
        $progressPercent = $totalParticipants > 0 ? round(($lpkApprovedCount / $totalParticipants) * 100) : 0;

        // Note: LPK can only be printed if ALL LRK are approved AND all LPK are approved.
        $lrkApprovedCount = $participants->where('status', ProgramStatus::Approved)->count();
        $allLrkApproved = $totalParticipants > 0 && $lrkApprovedCount === $totalParticipants;
        $allLpkApproved = $totalParticipants > 0 && $lpkApprovedCount === $totalParticipants;

        // Per-student summary for peer monitoring
        $students = $group ? $group->students()->get() : collect();

        $memberSummary = $students->map(function ($student) use ($participants) {
            $studentParticipants = $participants->where('student_id', $student->id);
            $total = $studentParticipants->count();
            $approved = $studentParticipants->where('lpk_status', ProgramStatus::Approved)->count();
            $submitted = $studentParticipants->where('lpk_status', ProgramStatus::Submitted)->count();
            $revision = $studentParticipants->where('lpk_status', ProgramStatus::NeedsRevision)->count();
            $draft = $studentParticipants->where('lpk_status', ProgramStatus::Draft)->count();

            if ($total === 0) {
                $overallStatus = 'empty';
            } elseif ($approved === $total) {
                $overallStatus = 'approved';
            } elseif ($revision > 0) {
                $overallStatus = 'revision';
            } elseif ($submitted > 0) {
                $overallStatus = 'submitted';
            } else {
                $overallStatus = 'draft';
            }

            return (object)[
                'student' => $student,
                'total' => $total,
                'approved' => $approved,
                'submitted' => $submitted,
                'revision' => $revision,
                'draft' => $draft,
                'overallStatus' => $overallStatus,
            ];
        });

        // Query for the paginated detail table
        $paginatedParticipants = collect();
        if ($group) {
            $query = ProgramParticipant::with(['program', 'student'])
                ->join('programs', 'program_participants.program_id', '=', 'programs.id')
                ->where('programs.group_id', $group->id)
                ->select('program_participants.*');

            if ($this->filterType) {
                $query->where('programs.type', $this->filterType);
            }
            if ($this->filterStatus) {
                $query->where('program_participants.lpk_status', $this->filterStatus);
            }
            if ($this->search) {
                $query->where('programs.title', 'like', '%' . $this->search . '%')
                      ->orWhere('program_participants.participant_code', 'like', '%' . $this->search . '%');
            }

            if ($this->sortBy === 'execution_date') {
                $query->orderBy('program_participants.execution_date', $this->sortDirection);
            }

            $query->orderBy('programs.type')
                  ->orderBy('programs.sequence')
                  ->orderBy('program_participants.student_id');

            $paginatedParticipants = $query->paginate(10);
        }

        $isLeader = $group && $group->student_leader_id === $user->id;

        return view('livewire.mahasiswa.lpk-documents', [
            'group' => $group,
            'totalParticipants' => $totalParticipants,
            'lpkApprovedCount' => $lpkApprovedCount,
            'allLrkApproved' => $allLrkApproved,
            'allLpkApproved' => $allLpkApproved,
            'progressPercent' => $progressPercent,
            'memberSummary' => $memberSummary,
            'paginatedParticipants' => $paginatedParticipants,
            'isLeader' => $isLeader,
        ]);
    }
}
