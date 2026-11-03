<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\Period;
use App\Models\User;
use App\Services\GroupAllocationExcelService;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Groups extends Component
{
    use WithFileUploads, WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public $importFile = null;

    public bool $showImportModal = false;

    public array $importErrors = [];

    public function mount()
    {
        $this->periodId = Period::active()->first()?->id ?? Period::latest('start_date')->first()?->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
    }

    public bool $showAssignModal = false;

    public ?Group $assigningGroup = null;

    public array $selectedDpls = [];

    public ?int $leadDplId = null;

    public function openAssignModal(Group $group)
    {
        $this->assigningGroup = $group;
        $this->selectedDpls = $group->dpls()->pluck('users.id')->map(fn ($id) => (string) $id)->toArray();
        $this->leadDplId = $group->lead_dpl_id;
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assigningGroup = null;
        $this->selectedDpls = [];
        $this->leadDplId = null;
    }

    public function saveAssignments()
    {
        if (! $this->assigningGroup) {
            return;
        }

        $this->validate([
            'selectedDpls' => 'array',
            'leadDplId' => 'nullable|integer',
        ]);

        // Sync pivot table
        $this->assigningGroup->dpls()->sync($this->selectedDpls);

        // Ensure lead DPL is one of the assigned DPLs
        if ($this->leadDplId && ! in_array((string) $this->leadDplId, $this->selectedDpls)) {
            $this->leadDplId = null; // Unset if they are not in the assigned list
        }

        // Update group's lead_dpl_id
        $this->assigningGroup->update(['lead_dpl_id' => $this->leadDplId]);

        $this->closeAssignModal();
        Flux::toast('DPL berhasil ditugaskan ke kelompok.');
    }

    public bool $showDatesModal = false;

    public ?Group $editingGroup = null;

    public $startDate = null;

    public $endDate = null;

    public function openDatesModal(Group $group)
    {
        $this->editingGroup = $group;
        $this->startDate = $group->start_date?->format('Y-m-d');
        $this->endDate = $group->end_date?->format('Y-m-d');
        $this->showDatesModal = true;
    }

    public function closeDatesModal()
    {
        $this->showDatesModal = false;
        $this->editingGroup = null;
        $this->startDate = null;
        $this->endDate = null;
    }

    public function saveDates()
    {
        if (! $this->editingGroup) {
            return;
        }

        $period = $this->editingGroup->period;

        $this->validate([
            'startDate' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->lt($period->start_date)) {
                        $fail('Tanggal mulai tidak boleh sebelum tanggal mulai periode ('.$period->start_date->format('d/m/Y').').');
                    }
                },
            ],
            'endDate' => [
                'nullable',
                'date',
                'after_or_equal:startDate',
                function ($attribute, $value, $fail) use ($period) {
                    if ($value && $period && Carbon::parse($value)->gt($period->end_date)) {
                        $fail('Tanggal selesai tidak boleh setelah tanggal selesai periode ('.$period->end_date->format('d/m/Y').').');
                    }
                },
            ],
        ]);

        $this->editingGroup->update([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);

        $this->closeDatesModal();
        Flux::toast('Waktu KKN berhasil disimpan.');
    }

    public function exportExcel(GroupAllocationExcelService $excelService)
    {
        $period = $this->periodId ? Period::find($this->periodId) : (Period::active()->first() ?? Period::current());

        if (! $period) {
            Flux::toast('Pilih periode KKN terlebih dahulu untuk mengunduh format Excel.', variant: 'danger');

            return null;
        }

        return $excelService->exportGroups($period);
    }

    public function openImportModal(): void
    {
        $this->reset(['importFile', 'importErrors']);
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset(['importFile', 'importErrors']);
    }

    public function importExcel(GroupAllocationExcelService $excelService)
    {
        $period = $this->periodId ? Period::find($this->periodId) : (Period::active()->first() ?? Period::current());

        if (! $period) {
            $this->importErrors = ['Pilih periode KKN terlebih dahulu sebelum mengimpor data kelompok.'];

            return;
        }

        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'importFile.required' => 'Silakan pilih file Excel (.xlsx) untuk diimpor.',
            'importFile.mimes' => 'Format file harus berupa dokumen Excel (.xlsx).',
            'importFile.max' => 'Ukuran file Excel tidak boleh melebihi 10MB.',
        ]);

        $result = $excelService->importGroups($this->importFile, $period);

        try {
            $this->importFile?->delete();
        } catch (\Throwable) {
            // ignore
        }

        if ($result['success']) {
            $this->closeImportModal();
            $msg = "Berhasil memproses kelompok: {$result['imported_count']} baru dibuat, {$result['updated_count']} diperbarui.";
            Flux::toast($msg);
        } else {
            $this->importErrors = $result['errors'];
            Flux::toast('Gagal mengimpor file. Periksa daftar kesalahan.', variant: 'danger');
        }
    }

    public function render()
    {
        $query = Group::query();

        if ($this->periodId) {
            $query->where('period_id', $this->periodId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('village', 'like', '%'.$this->search.'%');
            });
        }

        $groups = (clone $query)
            ->with(['period', 'dpls', 'groupType', 'leadDpl'])
            ->withCount('students')
            ->latest()
            ->paginate(10);

        $withDpl = (clone $query)->has('dpls')->count();
        $availableDpls = User::where('role', UserRole::Dpl)->get();

        return view('livewire.admin.groups', [
            'groups' => $groups,
            'availableDpls' => $availableDpls,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $groups->total(),
                'with_dpl' => $withDpl,
                'without_dpl' => max(0, $groups->total() - $withDpl),
            ],
        ]);
    }
}
