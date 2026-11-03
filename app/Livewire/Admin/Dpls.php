<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\User;
use App\Services\GroupAllocationExcelService;
use Flux\Flux;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dpls extends Component
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

    public function exportExcel(GroupAllocationExcelService $excelService)
    {
        $period = $this->periodId ? Period::find($this->periodId) : (Period::active()->first() ?? Period::current());

        if (! $period) {
            Flux::toast('Pilih periode KKN terlebih dahulu untuk mengunduh format Excel.', variant: 'danger');

            return null;
        }

        return $excelService->exportDpls($period);
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
            $this->importErrors = ['Pilih periode KKN terlebih dahulu sebelum mengimpor data DPL.'];

            return;
        }

        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'importFile.required' => 'Silakan pilih file Excel (.xlsx) untuk diimpor.',
            'importFile.mimes' => 'Format file harus berupa dokumen Excel (.xlsx).',
            'importFile.max' => 'Ukuran file Excel tidak boleh melebihi 10MB.',
        ]);

        $result = $excelService->importDpls($this->importFile, $period);

        try {
            $this->importFile?->delete();
        } catch (\Throwable) {
            // ignore
        }

        if ($result['success']) {
            $this->closeImportModal();
            $msg = "Berhasil memproses DPL: {$result['imported_count']} baru dibuat, {$result['updated_count']} diperbarui.";
            Flux::toast($msg);
        } else {
            $this->importErrors = $result['errors'];
            Flux::toast('Gagal mengimpor file. Periksa daftar kesalahan.', variant: 'danger');
        }
    }

    public function render()
    {
        $query = User::where('role', UserRole::Dpl)->with(['studyProgram', 'dplGroups.period']);

        if ($this->periodId) {
            $query->whereHas('dplGroups', function ($q) {
                $q->where('period_id', $this->periodId);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nip', 'like', '%'.$this->search.'%');
            });
        }

        $dpls = $query->latest()->paginate(10);
        $totalDpls = User::where('role', UserRole::Dpl)->count();

        $assignedQuery = User::where('role', UserRole::Dpl)->whereHas('dplGroups', function ($q) {
            if ($this->periodId) {
                $q->where('period_id', $this->periodId);
            }
        });
        $assignedCount = $assignedQuery->count();

        return view('livewire.admin.dpls', [
            'dpls' => $dpls,
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $totalDpls,
                'assigned' => $assignedCount,
                'unassigned' => max(0, $totalDpls - $assignedCount),
            ],
        ]);
    }
}
