<?php

namespace App\Livewire\Admin;

use App\Enums\Semester;
use App\Models\Period;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Periods extends Component
{
    public Semester $semester = Semester::Ganjil;

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public bool $isCreating = false;

    public function startCreating()
    {
        $this->isCreating = true;
    }

    public function createPeriod()
    {
        $this->validate([
            'semester' => ['required', Rule::enum(Semester::class)],
            'year' => 'required|digits:4',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Period::create([
            'semester' => $this->semester,
            'year' => $this->year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $this->reset(['semester', 'year', 'start_date', 'end_date', 'isCreating']);
    }

    public function render()
    {
        $periods = Period::withCount('groups')->latest()->get();
        $activePeriod = Period::active()->first();

        return view('livewire.admin.periods', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }
}
