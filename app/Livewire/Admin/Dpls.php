<?php

namespace App\Livewire\Admin;

use App\Concerns\IssuesTemporaryCredentials;
use App\Enums\UserRole;
use App\Models\Group;
use App\Models\Period;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\GroupAllocationExcelService;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dpls extends Component
{
    use IssuesTemporaryCredentials, WithFileUploads, WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public $importFile = null;

    public bool $showImportModal = false;

    public array $importErrors = [];

    public int $generatedCredentialsCount = 0;

    public string $importSummaryMessage = '';

    public bool $showCreateModal = false;

    public bool $isEditing = false;

    public ?int $editingDplId = null;

    public string $nip = '';

    public string $name = '';

    public string $email = '';

    public ?int $study_program_id = null;

    public string $phone = '';

    public string $password = '';

    public ?int $group_id = null;

    public ?int $initialGroupId = null;

    public ?int $deletingDplId = null;

    public string $deletingDplName = '';

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
        session()->forget('admin_import_credentials.dpls');
        $this->reset(['importFile', 'importErrors', 'generatedCredentialsCount', 'importSummaryMessage']);
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        session()->forget('admin_import_credentials.dpls');
        $this->showImportModal = false;
        $this->reset(['importFile', 'importErrors', 'generatedCredentialsCount', 'importSummaryMessage']);
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
            $this->importErrors = [];
            $credentials = $result['generated_credentials'] ?? [];
            $msg = "Berhasil memproses DPL: {$result['imported_count']} baru dibuat, {$result['updated_count']} diperbarui.";

            if (count($credentials) > 0) {
                session()->put('admin_import_credentials.dpls', $credentials);
                $this->generatedCredentialsCount = count($credentials);
                $this->importSummaryMessage = $msg;
                $this->importFile = null;
                $this->showImportModal = true;
            } else {
                $this->closeImportModal();
            }

            Flux::toast($msg);
        } else {
            $this->importErrors = $result['errors'];
            Flux::toast('Gagal mengimpor file. Periksa daftar kesalahan.', variant: 'danger');
        }
    }

    public function downloadGeneratedCredentials(GroupAllocationExcelService $excelService)
    {
        $credentials = session()->pull('admin_import_credentials.dpls', []);

        if (empty($credentials)) {
            Flux::toast('Kredensial sementara sudah diunduh atau kedaluwarsa.', variant: 'warning');

            return null;
        }

        $this->closeImportModal();

        return $excelService->exportGeneratedCredentials($credentials, 'kredensial_dpl_baru', 'NIP');
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['nip', 'name', 'email', 'study_program_id', 'phone', 'password', 'group_id', 'initialGroupId', 'isEditing', 'editingDplId']);
        $this->showCreateModal = true;
    }

    public function openEditModal(User $dpl): void
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->editingDplId = $dpl->id;
        $this->nip = $dpl->nip ?? '';
        $this->name = $dpl->name ?? '';
        $this->email = $dpl->email ?? '';
        $this->study_program_id = $dpl->study_program_id;
        $this->phone = $dpl->phone ?? '';
        $this->password = '';

        $group = $this->periodId
            ? $dpl->dplGroups()->where('period_id', $this->periodId)->first()
            : $dpl->dplGroups()->first();
        $this->group_id = $group?->id;
        $this->initialGroupId = $group?->id;

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
        $this->reset(['nip', 'name', 'email', 'study_program_id', 'phone', 'password', 'group_id', 'initialGroupId', 'isEditing', 'editingDplId']);
    }

    public function saveDpl(): void
    {
        $rules = [
            'nip' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'group_id' => ['nullable', 'exists:groups,id'],
        ];

        if (! $this->isEditing) {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        }

        $this->validate($rules, [
            'nip.required' => 'NIP wajib diisi.',
            'name.required' => 'Nama dosen wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $nip = trim($this->nip);
        $email = trim($this->email) ?: strtolower($nip).'@lecturer.sikkn.ac.id';

        if ($this->isEditing && $this->editingDplId) {
            $dpl = User::where('id', $this->editingDplId)->where('role', UserRole::Dpl)->firstOrFail();

            $duplicateNip = User::where('nip', $nip)->where('id', '!=', $dpl->id)->exists();
            if ($duplicateNip) {
                $this->addError('nip', 'NIP ini sudah terdaftar untuk pengguna lain.');

                return;
            }

            $duplicateEmail = User::where('email', $email)->where('id', '!=', $dpl->id)->exists();
            if ($duplicateEmail) {
                $this->addError('email', 'Email sudah digunakan oleh pengguna lain.');

                return;
            }

            $studyProgram = $this->study_program_id ? StudyProgram::find($this->study_program_id) : null;
            $facultyId = $studyProgram?->faculty_id;

            $dpl->update([
                'nip' => $nip,
                'name' => $this->name,
                'email' => $email,
                'phone' => $this->phone ?: null,
                'study_program_id' => $studyProgram?->id,
                'faculty_id' => $facultyId,
            ]);

            $this->syncDplGroupAssignment($dpl, $this->group_id ?: null, $this->initialGroupId);

            $this->closeCreateModal();
            Flux::toast('Data Dosen KKN berhasil diperbarui.', variant: 'success');

            return;
        }

        $existingUser = User::where('nip', $nip)->first();

        $emailOwner = User::where('email', $email)->first();
        if ($emailOwner && (! $existingUser || $emailOwner->id !== $existingUser->id)) {
            $this->addError('email', 'Email sudah digunakan oleh pengguna lain.');

            return;
        }

        $studyProgram = $this->study_program_id ? StudyProgram::find($this->study_program_id) : null;
        $facultyId = $studyProgram?->faculty_id;

        $temporaryPassword = null;

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
                $updateData['must_change_password'] = true;
            }

            $existingUser->update($updateData);
            $dpl = $existingUser;
            Flux::toast('Data Dosen KKN berhasil diperbarui.', variant: 'success');
        } else {
            $passwordData = $this->resolveProvisionedPassword($this->password, false);
            $temporaryPassword = $passwordData['temporary_password'];

            $dpl = User::create([
                'name' => $this->name,
                'nip' => $nip,
                'email' => $email,
                'password' => $passwordData['hash'],
                'must_change_password' => $passwordData['must_change_password'],
                'role' => UserRole::Dpl,
                'phone' => $this->phone ?: null,
                'study_program_id' => $studyProgram?->id,
                'faculty_id' => $facultyId,
            ]);

            Flux::toast('Dosen KKN berhasil ditambahkan.', variant: 'success');
        }

        $this->syncDplGroupAssignment($dpl, $this->group_id ?: null, null);

        $this->closeCreateModal();

        if ($temporaryPassword !== null) {
            $this->presentIssuedCredential($dpl, $temporaryPassword, __('Akun Dosen KKN Dibuat'));
        }
    }

    public function resetPassword(?int $id = null): void
    {
        $targetId = $id ?? $this->editingDplId;

        if (! $targetId) {
            return;
        }

        $dpl = User::where('role', UserRole::Dpl)->findOrFail($targetId);

        if ($this->showCreateModal) {
            $this->closeCreateModal();
        }

        $this->issueResetTemporaryPassword($dpl, __('Password Sementara Dosen KKN Direset'));
    }

    public function createDpl(): void
    {
        $this->saveDpl();
    }

    public function confirmDelete(int $id): void
    {
        $dpl = User::where('role', UserRole::Dpl)->findOrFail($id);

        if ($dpl->hasDplActivityRecords()) {
            Flux::toast(
                variant: 'warning',
                heading: __('Dosen KKN Tidak Dapat Dihapus'),
                text: __('Dosen KKN tidak dapat dihapus karena sudah memiliki riwayat penilaian atau aktivitas.'),
            );

            return;
        }

        $this->deletingDplId = $dpl->id;
        $this->deletingDplName = $dpl->name;

        Flux::modal('delete-dpl-modal')->show();
    }

    public function cancelDelete(): void
    {
        $this->reset(['deletingDplId', 'deletingDplName']);
    }

    public function delete(): void
    {
        if (! $this->deletingDplId) {
            return;
        }

        $dpl = User::where('role', UserRole::Dpl)->findOrFail($this->deletingDplId);

        if ($dpl->hasDplActivityRecords()) {
            Flux::modal('delete-dpl-modal')->close();
            $this->cancelDelete();
            Flux::toast(
                variant: 'warning',
                heading: __('Dosen KKN Tidak Dapat Dihapus'),
                text: __('Dosen KKN tidak dapat dihapus karena sudah memiliki riwayat penilaian atau aktivitas.'),
            );

            return;
        }

        DB::transaction(function () use ($dpl) {
            Group::where('lead_dpl_id', $dpl->id)->update(['lead_dpl_id' => null]);
            $dpl->dplGroups()->detach();
            $dpl->delete();
        });

        Flux::modal('delete-dpl-modal')->close();
        $this->cancelDelete();
        Flux::toast(
            variant: 'success',
            heading: __('Dosen KKN Dihapus'),
            text: __('Data Dosen KKN berhasil dihapus.'),
        );
    }

    /**
     * Update the edited/assigned group pivot without disturbing other multi-group assignments.
     */
    private function syncDplGroupAssignment(User $dpl, ?int $targetGroupId, ?int $initialGroupId): void
    {
        if ($initialGroupId && $initialGroupId !== $targetGroupId) {
            $dpl->dplGroups()->detach($initialGroupId);
            Group::where('id', $initialGroupId)
                ->where('lead_dpl_id', $dpl->id)
                ->update(['lead_dpl_id' => null]);
        }

        if ($targetGroupId) {
            $group = Group::find($targetGroupId);
            if ($group) {
                $group->dpls()->syncWithoutDetaching([$dpl->id]);
            }
        }
    }

    public function render()
    {
        $query = User::where('role', UserRole::Dpl)
            ->with(['studyProgram', 'dplGroups.period'])
            ->withCount(['dplGrades', 'programs', 'dailyLogs', 'mentoringLogs', 'programParticipants', 'scheduleEvents']);

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
