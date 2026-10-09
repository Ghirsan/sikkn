<?php

namespace App\Livewire\Admin;

use App\Enums\Semester;
use App\Models\Period;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Periods extends Component
{
    public string $name = '';

    public Semester $semester = Semester::Ganjil;

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public function mount()
    {
        $this->year = date('Y');
    }

    public function startCreating()
    {
        $this->reset(['name', 'year', 'start_date', 'end_date']);
        $this->semester = Semester::Ganjil;
        $this->year = date('Y');
        $this->dispatch('modal-show', name: 'period-modal');
    }

    public function createPeriod()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'semester' => ['required', Rule::enum(Semester::class)],
            'year' => 'required|digits:4',
            'start_date' => [
                'required',
                'date',
                'after_or_equal:' . $this->year . '-01-01',
                'before_or_equal:' . $this->year . '-12-31',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                'before_or_equal:' . $this->year . '-12-31',
            ],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai harus berada pada tahun ' . $this->year . '.',
            'start_date.before_or_equal' => 'Tanggal mulai harus berada pada tahun ' . $this->year . '.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'end_date.before_or_equal' => 'Tanggal selesai harus berada pada tahun ' . $this->year . '.',
        ]);

        Period::create([
            'name' => $this->name,
            'semester' => $this->semester,
            'year' => $this->year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $this->dispatch('modal-close', name: 'period-modal');
        \Flux\Flux::toast('Periode berhasil ditambahkan.', variant: 'success');
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
