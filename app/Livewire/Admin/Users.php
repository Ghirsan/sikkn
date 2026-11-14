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

class Users extends Component
{
    use IssuesTemporaryCredentials, WithPagination;

    public $roles;

    public $isEditing = false;

    public $editingId = null;

    // Form fields
    public $name = '';

    public $email = '';

    public $password = '';

    public $role = '';

    public $faculty_id = '';

    public $study_program_id = '';

    public $deletingId = null;

    public $search = '';

    public $filterRole = '';

    public function mount()
    {
        $this->roles = [UserRole::P2kkn, UserRole::Fakultas, UserRole::Prodi];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRole()
    {
        $this->resetPage();
    }

    public function updatedRole($value)
    {
        if ($value === UserRole::P2kkn->value) {
            $this->faculty_id = '';
            $this->study_program_id = '';
        } elseif ($value === UserRole::Fakultas->value) {
            $this->study_program_id = '';
        } elseif ($value === UserRole::Prodi->value) {
            $this->faculty_id = '';
        }
    }

    public function updatedFacultyId($value): void
    {
        if ($value && ! $this->isEditing) {
            $faculty = Faculty::find($value);
            if ($faculty) {
                $this->name = $faculty->defaultObserverAccountName();
            }
        }
    }

    public function updatedStudyProgramId($value): void
    {
        if ($value && ! $this->isEditing) {
            $prodi = StudyProgram::find($value);
            if ($prodi) {
                $this->name = $prodi->defaultObserverAccountName();
            }
        }
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'email', 'password', 'role',
            'faculty_id', 'study_program_id', 'isEditing', 'editingId',
        ]);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        Flux::modal('user-modal')->show();
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $user = User::whereIn('role', [UserRole::P2kkn, UserRole::Fakultas, UserRole::Prodi])
            ->findOrFail($id);

        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->faculty_id = $user->faculty_id ? (string) $user->faculty_id : '';
        $this->study_program_id = $user->study_program_id ? (string) $user->study_program_id : '';

        Flux::modal('user-modal')->show();
    }

    public function save()
    {
        $allowedRoles = [UserRole::P2kkn->value, UserRole::Fakultas->value, UserRole::Prodi->value];

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', Rule::in($allowedRoles)],
            'faculty_id' => [
                Rule::requiredIf($this->role === UserRole::Fakultas->value),
                'nullable',
                'exists:faculties,id',
            ],
            'study_program_id' => [
                Rule::requiredIf($this->role === UserRole::Prodi->value),
                'nullable',
                'exists:study_programs,id',
            ],
            'password' => $this->optionalDefaultPasswordRules(),
        ];

        $this->validate($rules, [
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // 1-to-1 account enforcement: verify no other user has this faculty / study program
        if ($this->role === UserRole::Fakultas->value) {
            $duplicate = User::where('role', UserRole::Fakultas)
                ->where('faculty_id', $this->faculty_id)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($duplicate) {
                $this->addError('faculty_id', 'Fakultas ini sudah memiliki akun pengelola/observer.');

                return;
            }
        }

        if ($this->role === UserRole::Prodi->value) {
            $duplicate = User::where('role', UserRole::Prodi)
                ->where('study_program_id', $this->study_program_id)
                ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($duplicate) {
                $this->addError('study_program_id', 'Program studi ini sudah memiliki akun pengelola/observer.');

                return;
            }
        }

        $facultyId = null;
        if ($this->role === UserRole::Fakultas->value) {
            $facultyId = $this->faculty_id;
        } elseif ($this->role === UserRole::Prodi->value) {
            $facultyId = StudyProgram::find($this->study_program_id)?->faculty_id;
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'faculty_id' => $facultyId,
            'study_program_id' => $this->role === UserRole::Prodi->value ? $this->study_program_id : null,
        ];

        $passwordData = $this->resolveProvisionedPassword($this->password, (bool) $this->isEditing);

        if ($passwordData['hash'] !== null) {
            $data['password'] = $passwordData['hash'];
            $data['must_change_password'] = $passwordData['must_change_password'];
        }

        $createdUser = null;

        if ($this->isEditing) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            $message = 'Pengguna berhasil diperbarui.';
        } else {
            $createdUser = User::create($data);
            $message = 'Pengguna berhasil ditambahkan.';
        }

        Flux::modal('user-modal')->close();
        $this->dispatch('toast', message: $message, type: 'success');

        if ($createdUser && $passwordData['temporary_password'] !== null) {
            $this->presentIssuedCredential($createdUser, $passwordData['temporary_password'], __('Akun Pengguna Dibuat'));
        }
    }

    public function resetPassword(int $id): void
    {
        $user = User::whereIn('role', [UserRole::P2kkn, UserRole::Fakultas, UserRole::Prodi])
            ->findOrFail($id);

        $this->issueResetTemporaryPassword($user, __('Password Sementara Direset'));
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        Flux::modal('delete-user-modal')->show();
    }

    public function delete()
    {
        $user = User::findOrFail($this->deletingId);

        // Prevent deleting oneself
        if ($user->id === auth()->id()) {
            Flux::modal('delete-user-modal')->close();
            $this->dispatch('toast', message: 'Anda tidak dapat menghapus akun Anda sendiri.', type: 'error');

            return;
        }

        $user->delete();

        Flux::modal('delete-user-modal')->close();
        $this->dispatch('toast', message: 'Pengguna berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        $allowedRoles = [UserRole::P2kkn, UserRole::Fakultas, UserRole::Prodi];

        $query = User::with(['faculty', 'studyProgram'])
            ->whereIn('role', $allowedRoles)
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterRole && in_array(UserRole::tryFrom($this->filterRole), $allowedRoles, true)) {
            $query->where('role', $this->filterRole);
        }

        $stats = [
            'total' => User::whereIn('role', $allowedRoles)->count(),
            'p2kkn' => User::where('role', UserRole::P2kkn)->count(),
            'fakultas' => User::where('role', UserRole::Fakultas)->count(),
            'prodi' => User::where('role', UserRole::Prodi)->count(),
        ];

        // Available faculties for dropdown (unassigned or currently assigned to editing user)
        $availableFaculties = Faculty::whereDoesntHave('observerUsers', function ($q) {
            $q->when($this->editingId, fn ($subQ) => $subQ->where('id', '!=', $this->editingId));
        })->orderBy('name')->get();

        // Available study programs for dropdown (unassigned or currently assigned to editing user)
        $availableStudyPrograms = StudyProgram::with('faculty')
            ->whereDoesntHave('observerUsers', function ($q) {
                $q->when($this->editingId, fn ($subQ) => $subQ->where('id', '!=', $this->editingId));
            })->orderBy('name')->get();

        return view('livewire.admin.users', [
            'users' => $query->paginate(10),
            'stats' => $stats,
            'availableFaculties' => $availableFaculties,
            'availableStudyPrograms' => $availableStudyPrograms,
        ]);
    }
}
