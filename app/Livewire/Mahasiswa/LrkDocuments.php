<?php

namespace App\Livewire\Mahasiswa;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\ProgramParticipant;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class LrkDocuments extends Component
{
    use WithPagination;

    public ?string $filterType = '';
    public ?string $filterStatus = '';
    public string $search = '';
    public string $sortBy = 'execution_date';
    public string $sortDirection = 'asc';
    public ?int $selectedProgramId = null;
    public ?int $selectedParticipantId = null;

    public function viewProgram(int $programId, ?int $participantId = null)
    {
        $this->selectedProgramId = $programId;
        $this->selectedParticipantId = $participantId;
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
    public function selectedProgram()
    {
        if (!$this->selectedProgramId) return null;
        return \App\Models\Program::find($this->selectedProgramId);
    }

    #[\Livewire\Attributes\Computed]
    public function selectedParticipant()
    {
        if (!$this->selectedParticipantId) return null;
        return ProgramParticipant::find($this->selectedParticipantId);
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
        $approvedCount = $participants->where('status', ProgramStatus::Approved)->count();
        $allApproved = false; // Will be calculated after mapping students

        // Progress percentage
        $progressPercent = $totalParticipants > 0 ? round(($approvedCount / $totalParticipants) * 100) : 0;

        // Per-student summary for peer monitoring
        $students = $group ? $group->students()->get() : collect();
        $multidisiplinProgramsCount = $group ? $group->programs()->where('type', ProgramType::Multidisiplin)->count() : 0;
        $allStudentsMeetMinimumRequirements = true;

        $memberSummary = $students->map(function ($student) use ($participants, $multidisiplinProgramsCount, &$allStudentsMeetMinimumRequirements) {
            $studentParticipants = $participants->where('student_id', $student->id);
            $total = $studentParticipants->count();
            $approved = $studentParticipants->where('status', ProgramStatus::Approved)->count();
            $submitted = $studentParticipants->where('status', ProgramStatus::Submitted)->count();
            $revision = $studentParticipants->where('status', ProgramStatus::NeedsRevision)->count();
            $draft = $studentParticipants->where('status', ProgramStatus::Draft)->count();

            $hasSosmas = $studentParticipants->contains(fn($p) => $p->program->type === ProgramType::SosialKemasyarakatan);
            $hasLainnya = $studentParticipants->contains(fn($p) => $p->program->type === ProgramType::Lainnya);
            $multidisiplinCount = $studentParticipants->filter(fn($p) => $p->program->type === ProgramType::Multidisiplin)->count();

            $meetsMinimumRequirements = $hasSosmas && $hasLainnya && ($multidisiplinCount > 0 && $multidisiplinCount === $multidisiplinProgramsCount);

            if (!$meetsMinimumRequirements) {
                $allStudentsMeetMinimumRequirements = false;
            }

            // Determine overall student status
            if ($total === 0) {
                $overallStatus = 'empty';
            } elseif ($approved === $total && $meetsMinimumRequirements) {
                $overallStatus = 'approved';
            } elseif ($approved === $total && !$meetsMinimumRequirements) {
                $overallStatus = 'incomplete'; // Needs to add missing mandatory programs
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
                'meetsMinimumRequirements' => $meetsMinimumRequirements,
            ];
        });

        // Ensure LRK is only ready if all participants are approved AND all students meet the minimum program requirements
        $allApproved = $totalParticipants > 0 && $approvedCount === $totalParticipants && $allStudentsMeetMinimumRequirements;

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
                $query->where('program_participants.status', $this->filterStatus);
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
        // Check if current user is group leader
        $isLeader = $group && $group->student_leader_id === $user->id;

        // Check LRK report completeness
        $reportFields = [
            'background', 'program_multidisiplin_text', 'program_sosmas_text',
            'program_lainnya_text', 'survey_documentation_text', 'location_map_text',
        ];
        $filledReportFields = $group ? collect($reportFields)->filter(fn($f) => !empty($group->$f))->count() : 0;
        $totalReportFields = count($reportFields);

        return view('livewire.mahasiswa.lrk-documents', [
            'group' => $group,
            'totalParticipants' => $totalParticipants,
            'approvedCount' => $approvedCount,
            'allApproved' => $allApproved,
            'progressPercent' => $progressPercent,
            'memberSummary' => $memberSummary,
            'paginatedParticipants' => $paginatedParticipants,
            'isLeader' => $isLeader,
            'filledReportFields' => $filledReportFields,
            'totalReportFields' => $totalReportFields,
        ]);
    }
}
