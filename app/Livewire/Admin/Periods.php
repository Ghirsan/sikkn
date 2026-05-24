<?php

namespace App\Livewire\Admin;

use App\Enums\PeriodStatus;
use App\Models\Period;
use Livewire\Component;

class Periods extends Component
{
    public string $name = '';

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public bool $isCreating = false;

    public function startCreating()
    {
        if (Period::where('status', PeriodStatus::Active)->exists()) {
            $this->addError('creation_failed', 'Tidak dapat menambahkan periode baru. Masih ada periode yang berstatus aktif. Selesaikan periode aktif terlebih dahulu.');
            return;
        }

        $this->isCreating = true;
    }

    public function createPeriod()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'year' => 'required|digits:4',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Period::create([
            'name' => $this->name,
            'year' => $this->year,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => PeriodStatus::Inactive,
        ]);

        $this->reset(['name', 'year', 'start_date', 'end_date', 'isCreating']);
    }

    public function toggleStatus(int $id, string $status)
    {
        $period = Period::findOrFail($id);

        // If activating, deactivate any currently active period
        if ($status === PeriodStatus::Active->value) {
            Period::where('status', PeriodStatus::Active)->update(['status' => PeriodStatus::Completed]);
        }

        $period->update(['status' => PeriodStatus::from($status)]);
    }

    public function render()
    {
        $periods = Period::withCount('groups')->latest()->get();
        $activePeriod = Period::where('status', PeriodStatus::Active)->first();

        return view('livewire.admin.periods', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }
}
