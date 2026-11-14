<?php

namespace App\Livewire\Admin;

use App\Concerns\IssuesTemporaryCredentials;
use App\Enums\UserRole;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class StudyPrograms extends Component
{
    use IssuesTemporaryCredentials, WithPagination;

    public $faculties;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $faculty_id = '';

    public string $search = '';

    public string $facultyFilter = '';

    public $deletingId = null;

    // Account management state
    public ?int $selectedProdiId = null;

    public string $selectedProdiName = '';

    public ?int $accountUserId = null;

    public string $accountName = '';

    public string $accountEmail = '';

    public string $accountPassword = '';

    public array $duplicateAccounts = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFacultyFilter(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->faculties = Faculty::orderBy('name')->get();
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'faculty_id', 'isEditing', 'editingId']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        Flux::modal('study-program-modal')->show();
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $prodi = StudyProgram::findOrFail($id);
        $this->name = $prodi->name;
        $this->code = $prodi->code;
        $this->faculty_id = $prodi->faculty_id;

        Flux::modal('study-program-modal')->show();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('study_programs', 'code')->ignore($this->editingId),
            ],
        ]);

        if ($this->isEditing) {
            $prodi = StudyProgram::findOrFail($this->editingId);
            $prodi->update([
                'name' => $this->name,
                'code' => $this->code,
                'faculty_id' => $this->faculty_id,
            ]);
            $message = 'Program studi berhasil diperbarui.';
        } else {
            StudyProgram::create([
                'name' => $this->name,
                'code' => $this->code,
                'faculty_id' => $this->faculty_id,
            ]);
            $message = 'Program studi berhasil ditambahkan.';
        }

        Flux::modal('study-program-modal')->close();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        Flux::modal('delete-study-program-modal')->show();
    }

    public function delete()
    {
        $prodi = StudyProgram::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (User::where('study_program_id', $prodi->id)->exists()) {
            Flux::modal('delete-study-program-modal')->close();
            $this->dispatch('toast', message: 'Tidak dapat menghapus program studi yang sudah memiliki pengguna terkait.', type: 'error');

            return;
        }

        $prodi->delete();

        Flux::modal('delete-study-program-modal')->close();
        $this->dispatch('toast', message: 'Program studi berhasil dihapus.', type: 'success');
    }

    public function manageAccount(int $prodiId): void
    {
        $this->resetValidation();
        $this->selectedProdiId = $prodiId;

        $prodi = StudyProgram::with(['faculty', 'observerUser', 'observerUsers'])->findOrFail($prodiId);
        $this->selectedProdiName = $prodi->name.' ('.($prodi->faculty->short_name ?: $prodi->faculty->code).')';

        // Check for duplicates
        if ($prodi->observerUsers->count() > 1) {
            $this->duplicateAccounts = $prodi->observerUsers->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->toArray();
        } else {
            $this->duplicateAccounts = [];
        }

        $observer = $prodi->observerUser;
        if ($observer) {
            $this->accountUserId = $observer->id;
            $this->accountName = $observer->name;
            $this->accountEmail = $observer->email;
            $this->accountPassword = '';
        } else {
            $this->accountUserId = null;
            $this->accountName = $prodi->defaultObserverAccountName();
            $this->accountEmail = '';
            $this->accountPassword = '';
        }

        Flux::modal('prodi-account-modal')->show();
    }

    public function saveAccount(): void
    {
        $this->validate([
            'accountName' => ['required', 'string', 'max:255'],
            'accountEmail' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->accountUserId),
            ],
            'accountPassword' => $this->optionalDefaultPasswordRules(),
        ], [
            'accountPassword.min' => 'Password minimal 6 karakter.',
        ]);

        $prodi = StudyProgram::findOrFail($this->selectedProdiId);

        $data = [
            'name' => $this->accountName,
            'email' => $this->accountEmail,
            'role' => UserRole::Prodi,
            'study_program_id' => $this->selectedProdiId,
            'faculty_id' => $prodi->faculty_id,
        ];

        $isUpdating = (bool) $this->accountUserId;
        $passwordData = $this->resolveProvisionedPassword($this->accountPassword, $isUpdating);

        if ($passwordData['hash'] !== null) {
            $data['password'] = $passwordData['hash'];
            $data['must_change_password'] = $passwordData['must_change_password'];
        }

        if ($isUpdating) {
            $user = User::findOrFail($this->accountUserId);
            $user->update($data);
            $message = 'Akun program studi berhasil diperbarui.';
        } else {
            $user = User::create($data);
            $this->accountUserId = $user->id;
            $message = 'Akun program studi berhasil dibuat.';
        }

        Flux::modal('prodi-account-modal')->close();
        $this->dispatch('toast', message: $message, type: 'success');

        if (! $isUpdating && $passwordData['temporary_password'] !== null) {
            $this->presentIssuedCredential($user, $passwordData['temporary_password'], __('Akun Program Studi Dibuat'));
        }
    }

    public function resetAccountPassword(): void
    {
        if (! $this->accountUserId) {
            return;
        }

        $user = User::findOrFail($this->accountUserId);
        Flux::modal('prodi-account-modal')->close();
        $this->issueResetTemporaryPassword($user, __('Password Sementara Program Studi Direset'));
    }

    public function confirmDeleteAccount(): void
    {
        Flux::modal('delete-prodi-account-modal')->show();
    }

    public function deleteAccount(): void
    {
        if (! $this->accountUserId) {
            return;
        }

        $user = User::findOrFail($this->accountUserId);

        if ($user->id === auth()->id()) {
            Flux::modal('delete-prodi-account-modal')->close();
            $this->dispatch('toast', message: 'Anda tidak dapat menghapus akun Anda sendiri.', type: 'error');

            return;
        }

        $user->delete();
        $this->accountUserId = null;

        Flux::modal('delete-prodi-account-modal')->close();
        Flux::modal('prodi-account-modal')->close();
        $this->dispatch('toast', message: 'Akun program studi berhasil dihapus.', type: 'success');
    }

    public function resolveDuplicateAccount(int $keepUserId): void
    {
        // Delete duplicates except the chosen one
        User::where('role', UserRole::Prodi)
            ->where('study_program_id', $this->selectedProdiId)
            ->where('id', '!=', $keepUserId)
            ->delete();

        $this->manageAccount($this->selectedProdiId);
        $this->dispatch('toast', message: 'Duplikasi akun berhasil dibersihkan.', type: 'success');
    }

    public function render()
    {
        $studyPrograms = StudyProgram::with(['faculty', 'observerUser'])
            ->withCount(['observerUsers'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->facultyFilter, fn ($query) => $query->where('faculty_id', $this->facultyFilter))
            ->orderBy('faculty_id')
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.admin.study-programs', [
            'studyPrograms' => $studyPrograms,
            'stats' => [
                'total_prodi' => StudyProgram::count(),
                'total_faculties' => Faculty::has('studyPrograms')->count(),
                'total_students' => User::where('role', UserRole::Mahasiswa)->whereNotNull('study_program_id')->count(),
            ],
        ]);
    }
}
