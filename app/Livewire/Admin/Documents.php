<?php

namespace App\Livewire\Admin;

use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Models\Period;
use App\Models\Program;
use Livewire\Component;

class Documents extends Component
{
    public ?int $periodId = null;

    public string $search = '';

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id;
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

        $groups = $query->latest()->get();

        $groupData = $groups->map(function ($group) {
            $total = $group->programs->count();
            $approved = $group->programs->where('status', ProgramStatus::Approved)->count();

            return (object) [
                'group' => $group,
                'totalPrograms' => $total,
                'approvedCount' => $approved,
                'allApproved' => $total > 0 && $approved === $total,
            ];
        });

        // Global stats based on filtered groups' programs
        $programIds = $groups->flatMap->programs->pluck('id');
        $allPrograms = Program::whereIn('id', $programIds)->get();

        return view('livewire.admin.documents', [
            'groupData' => $groupData,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'draft' => $allPrograms->where('status', ProgramStatus::Draft)->count(),
                'submitted' => $allPrograms->where('status', ProgramStatus::Submitted)->count(),
                'approved' => $allPrograms->where('status', ProgramStatus::Approved)->count(),
                'ready_pdf' => $groupData->where('allApproved', true)->where('totalPrograms', '>', 0)->count(),
            ],
        ]);
    }
}
