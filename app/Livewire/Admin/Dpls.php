<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Group;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\GroupAllocationExcelService;
use Flux\Flux;
use Illuminate\Support\Facades\Hash;
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

    public bool $showCreateModal = false;

    public string $nip = '';

    public string $name = '';

    public string $email = '';

    public ?int $study_program_id = null;

    public string $phone = '';

    public string $password = '';

    public ?int $group_id = null;

    public bool $is_lead = false;

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

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['nip', 'name', 'email', 'study_program_id', 'phone', 'password', 'group_id', 'is_lead']);
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
        $this->reset(['nip', 'name', 'email', 'study_program_id', 'phone', 'password', 'group_id', 'is_lead']);
    }

    public function createDpl(): void
    {
        $this->validate([
            'nip' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'is_lead' => ['boolean'],
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'name.required' => 'Nama dosen wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $nip = trim($this->nip);
        $email = trim($this->email) ?: strtolower($nip).'@lecturer.sikkn.ac.id';
        $existingUser = User::where('nip', $nip)->first();

        $emailOwner = User::where('email', $email)->first();
        if ($emailOwner && (! $existingUser || $emailOwner->id !== $existingUser->id)) {
            $this->addError('email', 'Email sudah digunakan oleh pengguna lain.');

            return;
        }

        $studyProgram = $this->study_program_id ? StudyProgram::find($this->study_program_id) : null;
        $facultyId = $studyProgram?->faculty_id;

        if ($existingUser) {
            if ($existingUser->role !== UserRole::Dpl) {
                $this->addError('nip', 'NIP ini sudah terdaftar sebagai pengguna non-dosen.');

                return;
            }

            $updateData = [
                'name' => $this->name,
                'email' => $email,
                'phone' => $this->phone ?: $existingUser->phone,
                'study_program_id' => $studyProgram?->id ?? $existingUser->study_program_id,
                'faculty_id' => $facultyId ?? $existingUser->faculty_id,
            ];

            if ($this->password) {
                $updateData['password'] = Hash::make($this->password);
            }

            $existingUser->update($updateData);
            $dpl = $existingUser;
            Flux::toast('Data Dosen KKN berhasil diperbarui.', variant: 'success');
        } else {
            $dpl = User::create([
                'name' => $this->name,
                'nip' => $nip,
                'email' => $email,
                'password' => Hash::make($this->password ?: 'password'),
                'role' => UserRole::Dpl,
                'phone' => $this->phone ?: null,
                'study_program_id' => $studyProgram?->id,
                'faculty_id' => $facultyId,
            ]);

            Flux::toast('Dosen KKN berhasil ditambahkan.', variant: 'success');
        }

        if ($this->group_id) {
            $group = Group::find($this->group_id);
            if ($group) {
                $group->dpls()->syncWithoutDetaching([$dpl->id]);
                if ($this->is_lead) {
                    $group->update(['lead_dpl_id' => $dpl->id]);
                }
            }
        }

        $this->closeCreateModal();
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
            'studyPrograms' => StudyProgram::orderBy('name')->get(),
            'availableGroups' => Group::when($this->periodId, fn ($q) => $q->where('period_id', $this->periodId))->orderBy('name')->get(),
            'periods' => Period::orderByDesc('start_date')->get(),
            'stats' => [
                'total' => $totalDpls,
                'assigned' => $assignedCount,
                'unassigned' => max(0, $totalDpls - $assignedCount),
            ],
        ]);
    }
}
