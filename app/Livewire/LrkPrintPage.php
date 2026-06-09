<?php

namespace App\Livewire;

use App\Models\Group;
use Livewire\Component;

class LrkPrintPage extends Component
{
    public Group $group;

    public function mount(Group $group): void
    {
        $this->group = $group->load([
            'period',
            'dpls',
            'students',
            'programs.student',
            'scheduleEvents',
            'surveyDocuments',
        ]);
    }

    public function render()
    {
        $programs = $this->group->programs->where('status', \App\Enums\ProgramStatus::Approved);

        return view('livewire.lrk-print-page', [
            'group' => $this->group,
            'period' => $this->group->period,
            'dpls' => $this->group->dpls,
            'students' => $this->group->students,
            'multidisiplin' => $programs->where('type', \App\Enums\ProgramType::Multidisiplin),
            'sosialKemasyarakatan' => $programs->where('type', \App\Enums\ProgramType::SosialKemasyarakatan),
            'lainnya' => $programs->where('type', \App\Enums\ProgramType::Lainnya),
            'scheduleEvents' => $this->group->scheduleEvents->sortBy('date'),
            'surveyDocuments' => $this->group->surveyDocuments->sortBy('sort_order'),
        ])->layout('components.layouts.print');
    }
}
