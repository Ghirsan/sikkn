<?php

namespace App\Livewire\Admin;

use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Models\Program;
use Livewire\Component;

class Documents extends Component
{
    public string $search = '';

    public function render()
    {
        $query = Group::with(['programs', 'period']);

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

        // Global stats
        $allPrograms = Program::all();

        return view('livewire.admin.documents', [
            'groupData' => $groupData,
            'stats' => [
                'draft' => $allPrograms->where('status', ProgramStatus::Draft)->count(),
                'submitted' => $allPrograms->where('status', ProgramStatus::Submitted)->count(),
                'approved' => $allPrograms->where('status', ProgramStatus::Approved)->count(),
                'ready_pdf' => $groupData->where('allApproved', true)->count(),
            ],
        ]);
    }
}
