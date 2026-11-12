<?php

namespace App\Livewire\Admin;

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

class Students extends Component
{
    use WithFileUploads, WithPagination;

    public ?int $periodId = null;

    public string $search = '';

    public $importFile = null;

    public bool $showImportModal = false;

    public array $importErrors = [];

    public bool $showCreateModal = false;

    public bool $isEditing = false;

    public ?int $editingStudentId = null;

    public string $nim = '';

    public string $name = '';

    public string $email = '';

    public ?int $study_program_id = null;

    public ?int $group_id = null;

    public string $phone = '';

    public string $password = '';

    public ?int $deletingStudentId = null;

    public string $deletingStudentName = '';

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

        return $excelService->exportStudents($period);
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
            $this->importErrors = ['Pilih periode KKN terlebih dahulu sebelum mengimpor data mahasiswa.'];

            return;
        }

        $this->validate([
            'importFile' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'importFile.required' => 'Silakan pilih file Excel (.xlsx) untuk diimpor.',
            'importFile.mimes' => 'Format file harus berupa dokumen Excel (.xlsx).',
            'importFile.max' => 'Ukuran file Excel tidak boleh melebihi 10MB.',
        ]);

        $result = $excelService->importStudents($this->importFile, $period);

        try {
            $this->importFile?->delete();
        } catch (\Throwable) {
            // ignore
        }

        if ($result['success']) {
            $this->closeImportModal();
            $msg = "Berhasil memproses mahasiswa: {$result['imported_count']} baru dibuat, {$result['updated_count']} diperbarui.";
            Flux::toast($msg);
        } else {
            $this->importErrors = $result['errors'];
            Flux::toast('Gagal mengimpor file. Periksa daftar kesalahan.', variant: 'danger');
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['nim', 'name', 'email', 'study_program_id', 'group_id', 'phone', 'password', 'isEditing', 'editingStudentId']);
        $this->showCreateModal = true;
    }

    public function openEditModal(User $student): void
    {
        $this->resetValidation();
        $this->isEditing = true;
        $this->editingStudentId = $student->id;
        $this->nim = $student->nim ?? '';
        $this->name = $student->name ?? '';
        $this->email = $student->email ?? '';
        $this->study_program_id = $student->study_program_id;
        $this->group_id = $student->group_id;
        $this->phone = $student->phone ?? '';
        $this->password = '';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
        $this->reset(['nim', 'name', 'email', 'study_program_id', 'group_id', 'phone', 'password', 'isEditing', 'editingStudentId']);
    }

    public function saveStudent(): void
    {
        $this->validate([
            'nim' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ], [
            'nim.required' => 'NIM wajib diisi.',
            'name.required' => 'Nama mahasiswa wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $nim = trim($this->nim);
        $email = trim($this->email) ?: strtolower($nim).'@student.sikkn.ac.id';

        if ($this->isEditing && $this->editingStudentId) {
            $student = User::where('id', $this->editingStudentId)->where('role', UserRole::Mahasiswa)->firstOrFail();

            $duplicateNim = User::where('nim', $nim)->where('id', '!=', $student->id)->exists();
            if ($duplicateNim) {
                $this->addError('nim', 'NIM ini sudah terdaftar untuk pengguna lain.');

                return;
            }

            $duplicateEmail = User::where('email', $email)->where('id', '!=', $student->id)->exists();
            if ($duplicateEmail) {
                $this->addError('email', 'Email sudah digunakan oleh pengguna lain.');

                return;
            }

            $studyProgram = $this->study_program_id ? StudyProgram::find($this->study_program_id) : null;
            $facultyId = $studyProgram?->faculty_id;
            $previousGroupId = $student->group_id;
            $newGroupId = $this->group_id ?: null;

            $updateData = [
                'nim' => $nim,
                'name' => $this->name,
                'email' => $email,
                'phone' => $this->phone ?: null,
                'study_program_id' => $studyProgram?->id,
                'faculty_id' => $facultyId,
                'group_id' => $newGroupId,
            ];

            if (! empty($this->password)) {
                $updateData['password'] = Hash::make($this->password);
            }

            $student->update($updateData);
            $this->clearGroupLeadershipIfMoved($student, $previousGroupId, $newGroupId);

            $this->closeCreateModal();
            Flux::toast('Data mahasiswa berhasil diperbarui.', variant: 'success');

            return;
        }

        $existingUser = User::where('nim', $nim)->first();

        $emailOwner = User::where('email', $email)->first();
        if ($emailOwner && (! $existingUser || $emailOwner->id !== $existingUser->id)) {
            $this->addError('email', 'Email sudah digunakan oleh pengguna lain.');

            return;
        }

        $studyProgram = $this->study_program_id ? StudyProgram::find($this->study_program_id) : null;
        $facultyId = $studyProgram?->faculty_id;

        if ($existingUser) {
            if ($existingUser->role !== UserRole::Mahasiswa) {
                $this->addError('nim', 'NIM ini sudah terdaftar sebagai pengguna non-mahasiswa.');

                return;
            }

            $previousGroupId = $existingUser->group_id;
            $newGroupId = $this->group_id ?: $existingUser->group_id;

            $updateData = [
                'name' => $this->name,
                'email' => $email,
                'phone' => $this->phone ?: $existingUser->phone,
                'study_program_id' => $studyProgram?->id ?? $existingUser->study_program_id,
                'faculty_id' => $facultyId ?? $existingUser->faculty_id,
                'group_id' => $newGroupId,
            ];

            if ($this->password) {
                $updateData['password'] = Hash::make($this->password);
            }

            $existingUser->update($updateData);
            $this->clearGroupLeadershipIfMoved($existingUser, $previousGroupId, $newGroupId);

            $this->closeCreateModal();
            Flux::toast('Data mahasiswa berhasil diperbarui dan dialokasikan.', variant: 'success');
        } else {
            User::create([
                'name' => $this->name,
                'nim' => $nim,
                'email' => $email,
                'password' => Hash::make($this->password ?: 'password'),
                'role' => UserRole::Mahasiswa,
                'phone' => $this->phone ?: null,
                'study_program_id' => $studyProgram?->id,
                'faculty_id' => $facultyId,
                'group_id' => $this->group_id ?: null,
            ]);

            $this->closeCreateModal();
            Flux::toast('Peserta KKN berhasil ditambahkan.', variant: 'success');
        }
    }

    public function createStudent(): void
    {
        $this->saveStudent();
    }

    public function confirmDelete(int $id): void
    {
        $student = User::where('role', UserRole::Mahasiswa)->findOrFail($id);

        if ($student->hasStudentActivityRecords()) {
            Flux::toast(
                variant: 'warning',
                heading: __('Mahasiswa Tidak Dapat Dihapus'),
                text: __('Mahasiswa tidak dapat dihapus karena sudah memiliki riwayat logbook, program, bimbingan, jadwal, atau nilai.'),
            );

            return;
        }

        $this->deletingStudentId = $student->id;
        $this->deletingStudentName = $student->name;

        Flux::modal('delete-student-modal')->show();
    }

    public function cancelDelete(): void
    {
        $this->reset(['deletingStudentId', 'deletingStudentName']);
    }

    public function delete(): void
    {
        if (! $this->deletingStudentId) {
            return;
        }

        $student = User::where('role', UserRole::Mahasiswa)->findOrFail($this->deletingStudentId);

        if ($student->hasStudentActivityRecords()) {
            Flux::modal('delete-student-modal')->close();
            $this->cancelDelete();
            Flux::toast(
                variant: 'warning',
                heading: __('Mahasiswa Tidak Dapat Dihapus'),
                text: __('Mahasiswa tidak dapat dihapus karena sudah memiliki riwayat logbook, program, bimbingan, jadwal, atau nilai.'),
            );

            return;
        }

        DB::transaction(function () use ($student) {
            Group::where('student_leader_id', $student->id)->update(['student_leader_id' => null]);
            $student->delete();
        });

        Flux::modal('delete-student-modal')->close();
        $this->cancelDelete();
        Flux::toast(
            variant: 'success',
            heading: __('Mahasiswa Dihapus'),
            text: __('Data mahasiswa berhasil dihapus.'),
        );
    }

    /**
     * Reset student leadership on the previous group when a student is unassigned or moved.
     */
    private function clearGroupLeadershipIfMoved(User $student, ?int $previousGroupId, ?int $newGroupId): void
    {
        if ($previousGroupId && $previousGroupId !== $newGroupId) {
            Group::where('id', $previousGroupId)
                ->where('student_leader_id', $student->id)
                ->update(['student_leader_id' => null]);
        }
    }

    public function render()
    {
        $query = User::where('role', UserRole::Mahasiswa)
            ->with(['group.period', 'studyProgram', 'faculty'])
            ->withCount(['dailyLogs', 'programParticipants', 'programs', 'mentoringLogs', 'scheduleEvents', 'grades']);

        if ($this->periodId) {
            $query->whereHas('group', function ($q) {
                $q->where('period_id', $this->periodId);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%')
                    ->orWhereHas('studyProgram', function ($query) {
                        $query->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $students = $query->latest()->paginate(10);
        $totalStudents = User::where('role', UserRole::Mahasiswa)->count();
        $assignedCount = User::where('role', UserRole::Mahasiswa)->whereHas('group', function ($q) {
            if ($this->periodId) {
                $q->where('period_id', $this->periodId);
            }
        })->count();

        return view('livewire.admin.students', [
            'students' => $students,
            'studyPrograms' => StudyProgram::orderBy('name')->get(),
            'availableGroups' => Group::when($this->periodId, fn ($q) => $q->where('period_id', $this->periodId))->orderBy('name')->get(),
            'periods' => Period::orderByDesc('start_date')->get(),
            'totalStudents' => $students->total(),
            'stats' => [
                'total' => $totalStudents,
                'assigned' => $assignedCount,
                'unassigned' => max(0, $totalStudents - $assignedCount),
            ],
        ]);
    }
}
