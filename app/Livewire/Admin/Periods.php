<?php

namespace App\Livewire\Admin;

use App\Enums\Semester;
use App\Models\Group;
use App\Models\Period;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Periods extends Component
{
    use WithPagination;

    public string $name = '';

    public string $search = '';

    public Semester $semester = Semester::Ganjil;

    public string $year = '';

    public string $start_date = '';

    public string $end_date = '';

    public bool $isEditing = false;

    public ?int $editingId = null;

    public ?int $deletingId = null;

    public string $deletingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->year = date('Y');
    }

    public function startCreating()
    {
        $this->reset(['name', 'year', 'start_date', 'end_date', 'isEditing', 'editingId']);
        $this->semester = Semester::Ganjil;
        $this->year = date('Y');
        $this->resetValidation();
        $this->dispatch('modal-show', name: 'period-modal');
    }

    public function editPeriod(int $id)
    {
        $this->resetValidation();
        $period = Period::findOrFail($id);
        $this->editingId = $period->id;
        $this->isEditing = true;
        $this->name = $period->name ?? '';
        $this->semester = $period->semester;
        $this->year = (string) $period->year;
        $this->start_date = $period->start_date->format('Y-m-d');
        $this->end_date = $period->end_date->format('Y-m-d');
        $this->dispatch('modal-show', name: 'period-modal');
    }

    public function createPeriod()
    {
        $this->savePeriod();
    }

    public function savePeriod()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'semester' => ['required', Rule::enum(Semester::class)],
            'year' => 'required|digits:4',
            'start_date' => [
                'required',
                'date',
                'after_or_equal:'.$this->year.'-01-01',
                'before_or_equal:'.$this->year.'-12-31',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                'before_or_equal:'.$this->year.'-12-31',
            ],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai harus berada pada tahun '.$this->year.'.',
            'start_date.before_or_equal' => 'Tanggal mulai harus berada pada tahun '.$this->year.'.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
            'end_date.before_or_equal' => 'Tanggal selesai harus berada pada tahun '.$this->year.'.',
        ]);

        if ($this->isEditing && $this->editingId) {
            $period = Period::findOrFail($this->editingId);
            $period->update([
                'name' => $this->name,
                'semester' => $this->semester,
                'year' => $this->year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]);
            $message = 'Periode berhasil diperbarui.';
        } else {
            Period::create([
                'name' => $this->name,
                'semester' => $this->semester,
                'year' => $this->year,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
            ]);
            $message = 'Periode berhasil ditambahkan.';
        }

        $this->dispatch('modal-close', name: 'period-modal');
        $this->reset(['name', 'year', 'start_date', 'end_date', 'isEditing', 'editingId']);
        Flux::toast($message, variant: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $period = Period::findOrFail($id);

        if (! $period->canBeDeleted()) {
            Flux::toast(
                variant: 'warning',
                heading: __('Periode Tidak Dapat Dihapus'),
                text: __('Periode tidak dapat dihapus karena masih memiliki kelompok KKN.'),
            );

            return;
        }

        $this->deletingId = $period->id;
        $this->deletingName = $period->display_name;

        Flux::modal('delete-period-modal')->show();
    }

    public function cancelDelete(): void
    {
        $this->reset(['deletingId', 'deletingName']);
    }

    public function delete(): void
    {
        if (! $this->deletingId) {
            return;
        }

        $period = Period::findOrFail($this->deletingId);

        if (! $period->canBeDeleted()) {
            Flux::modal('delete-period-modal')->close();
            $this->cancelDelete();
            Flux::toast(
                variant: 'warning',
                heading: __('Periode Tidak Dapat Dihapus'),
                text: __('Periode tidak dapat dihapus karena masih memiliki kelompok KKN.'),
            );

            return;
        }

        $period->delete();

        Flux::modal('delete-period-modal')->close();
        $this->cancelDelete();
        Flux::toast(
            variant: 'success',
            heading: __('Periode Dihapus'),
            text: __('Periode berhasil dihapus.'),
        );
    }

    public function render()
    {
        $periods = Period::withCount('groups')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('year', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);
        $activePeriod = Period::active()->first();
        $totalPeriods = Period::count();
        $totalGroups = Group::count();

        return view('livewire.admin.periods', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
            'stats' => [
                'total' => $totalPeriods,
                'active' => $activePeriod ? $activePeriod->name : __('Tidak Ada'),
                'groups' => $totalGroups,
            ],
        ]);
    }
}
