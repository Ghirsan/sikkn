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

class Faculties extends Component
{
    use IssuesTemporaryCredentials, WithPagination;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $short_name = '';

    public string $search = '';

    public $deletingId = null;

    // Account management state
    public ?int $selectedFacultyId = null;

    public string $selectedFacultyName = '';

    public ?int $accountUserId = null;

    public string $accountName = '';

    public string $accountEmail = '';

    public string $accountPassword = '';

    public array $duplicateAccounts = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'short_name', 'isEditing', 'editingId']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        Flux::modal('faculty-modal')->show();
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $faculty = Faculty::findOrFail($id);
        $this->name = $faculty->name;
        $this->code = $faculty->code;
        $this->short_name = $faculty->short_name;

        Flux::modal('faculty-modal')->show();
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('faculties', 'code')->ignore($this->editingId),
            ],
        ]);

        if ($this->isEditing) {
            $faculty = Faculty::findOrFail($this->editingId);
            $faculty->update([
                'name' => $this->name,
                'code' => $this->code,
                'short_name' => $this->short_name,
            ]);
            $message = 'Fakultas berhasil diperbarui.';
        } else {
            Faculty::create([
                'name' => $this->name,
                'code' => $this->code,
                'short_name' => $this->short_name,
            ]);
            $message = 'Fakultas berhasil ditambahkan.';
        }

        Flux::modal('faculty-modal')->close();
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        Flux::modal('delete-faculty-modal')->show();
    }

    public function delete()
    {
        $faculty = Faculty::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (StudyProgram::where('faculty_id', $faculty->id)->exists() || User::where('faculty_id', $faculty->id)->exists()) {
            Flux::modal('delete-faculty-modal')->close();
            $this->dispatch('toast', message: 'Tidak dapat menghapus fakultas yang sudah memiliki prodi atau pengguna terkait.', type: 'error');

            return;
        }

        $faculty->delete();

        Flux::modal('delete-faculty-modal')->close();
        $this->dispatch('toast', message: 'Fakultas berhasil dihapus.', type: 'success');
    }

    public function manageAccount(int $facultyId): void
    {
        $this->resetValidation();
        $this->selectedFacultyId = $facultyId;

        $faculty = Faculty::with(['observerUser', 'observerUsers'])->findOrFail($facultyId);
        $this->selectedFacultyName = $faculty->name;

        // Check for duplicates
        if ($faculty->observerUsers->count() > 1) {
            $this->duplicateAccounts = $faculty->observerUsers->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ])->toArray();
        } else {
            $this->duplicateAccounts = [];
        }

        $observer = $faculty->observerUser;
        if ($observer) {
            $this->accountUserId = $observer->id;
            $this->accountName = $observer->name;
            $this->accountEmail = $observer->email;
            $this->accountPassword = '';
        } else {
            $this->accountUserId = null;
            $this->accountName = $faculty->defaultObserverAccountName();
            $this->accountEmail = '';
            $this->accountPassword = '';
        }

        Flux::modal('faculty-account-modal')->show();
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

        $data = [
            'name' => $this->accountName,
            'email' => $this->accountEmail,
            'role' => UserRole::Fakultas,
            'faculty_id' => $this->selectedFacultyId,
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
            $message = 'Akun fakultas berhasil diperbarui.';
        } else {
            $user = User::create($data);
            $this->accountUserId = $user->id;
            $message = 'Akun fakultas berhasil dibuat.';
        }

        Flux::modal('faculty-account-modal')->close();
        $this->dispatch('toast', message: $message, type: 'success');

        if (! $isUpdating && $passwordData['temporary_password'] !== null) {
            $this->presentIssuedCredential($user, $passwordData['temporary_password'], __('Akun Fakultas Dibuat'));
        }
    }

    public function resetAccountPassword(): void
    {
        if (! $this->accountUserId) {
            return;
        }

        $user = User::findOrFail($this->accountUserId);
        Flux::modal('faculty-account-modal')->close();
        $this->issueResetTemporaryPassword($user, __('Password Sementara Fakultas Direset'));
    }

    public function confirmDeleteAccount(): void
    {
        Flux::modal('delete-faculty-account-modal')->show();
    }

    public function deleteAccount(): void
    {
        if (! $this->accountUserId) {
            return;
        }

        $user = User::findOrFail($this->accountUserId);

        if ($user->id === auth()->id()) {
            Flux::modal('delete-faculty-account-modal')->close();
            $this->dispatch('toast', message: 'Anda tidak dapat menghapus akun Anda sendiri.', type: 'error');

            return;
        }

        $user->delete();
        $this->accountUserId = null;

        Flux::modal('delete-faculty-account-modal')->close();
        Flux::modal('faculty-account-modal')->close();
        $this->dispatch('toast', message: 'Akun fakultas berhasil dihapus.', type: 'success');
    }

    public function resolveDuplicateAccount(int $keepUserId): void
    {
        // Delete duplicates except the chosen one
        User::where('role', UserRole::Fakultas)
            ->where('faculty_id', $this->selectedFacultyId)
            ->where('id', '!=', $keepUserId)
            ->delete();

        $this->manageAccount($this->selectedFacultyId);
        $this->dispatch('toast', message: 'Duplikasi akun berhasil dibersihkan.', type: 'success');
    }

    public function render()
    {
        $faculties = Faculty::query()
            ->with(['observerUser'])
            ->withCount(['observerUsers'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%')
                        ->orWhere('short_name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.admin.faculties', [
            'faculties' => $faculties,
            'stats' => [
                'total_faculties' => Faculty::count(),
                'total_prodi' => StudyProgram::count(),
                'total_students' => User::where('role', UserRole::Mahasiswa)->count(),
            ],
        ]);
    }
}
