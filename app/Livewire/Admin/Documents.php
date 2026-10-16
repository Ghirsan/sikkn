<?php

namespace App\Livewire\Admin;

use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use Livewire\Component;
use Livewire\WithPagination;

class Documents extends Component
{
    use WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Group::with(['programs', 'period']);

        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        }

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $paginatedGroups = $query->latest()->paginate(10);

        $groupData = $paginatedGroups->through(function ($group) {
            $total = $group->programs->count();
            $approved = $group->programs->where('status', ProgramStatus::Approved)->count();

            return (object) [
                'group' => $group,
                'totalPrograms' => $total,
                'approvedCount' => $approved,
                'allApproved' => $total > 0 && $approved === $total,
            ];
        });

        // Global stats based on filtered groups
        $statsProgramQuery = Program::whereHas('group', function ($q) {
            if ($this->periodId) {
                $q->where('period_id', $this->periodId);
            }
            if ($this->search) {
                $q->where('name', 'like', '%'.$this->search.'%');
            }
        });

        $readyPdfCount = Group::whereHas('programs')
            ->when($this->periodId, fn ($q) => $q->where('period_id', $this->periodId))
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->withCount([
                'programs',
                'programs as approved_programs_count' => fn ($q) => $q->where('status', ProgramStatus::Approved),
            ])
            ->havingRaw('programs_count = approved_programs_count')
            ->count();

        return view('livewire.admin.documents', [
            'groupData' => $groupData,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'draft' => (clone $statsProgramQuery)->where('status', ProgramStatus::Draft)->count(),
                'submitted' => (clone $statsProgramQuery)->where('status', ProgramStatus::Submitted)->count(),
                'approved' => (clone $statsProgramQuery)->where('status', ProgramStatus::Approved)->count(),
                'ready_pdf' => $readyPdfCount,
            ],
        ]);
    }
}
