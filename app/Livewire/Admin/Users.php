<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Component;
use Livewire\WithPagination;

class Users extends Component
{
    use WithPagination;

    public $faculties;

    public $studyPrograms;

    public $roles;

    public $isEditing = false;

    public $editingId = null;

    // Form fields
    public $name = '';

    public $email = '';

    public $password = '';

    public $role = '';

    public $nim = '';

    public $nip = '';

    public $phone = '';

    public $faculty_id = '';

    public $study_program_id = '';

    public $deletingId = null;

    public $search = '';

    public $filterRole = '';

    public function mount()
    {
        $this->faculties = Faculty::orderBy('name')->get();
        $this->studyPrograms = StudyProgram::orderBy('name')->get();
        $this->roles = UserRole::cases();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterRole()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'email', 'password', 'role', 'nim', 'nip', 'phone',
            'faculty_id', 'study_program_id', 'isEditing', 'editingId',
        ]);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'user-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $user = User::findOrFail($id);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->nim = $user->nim;
        $this->nip = $user->nip;
        $this->phone = $user->phone;
        $this->faculty_id = $user->faculty_id;
        $this->study_program_id = $user->study_program_id;

        $this->dispatch('modal-show', name: 'user-modal');
    }

    public function save()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', new Enum(UserRole::class)],
            'nim' => ['nullable', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:50'],
            'faculty_id' => ['nullable', 'exists:faculties,id'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
        ];

        if (! $this->isEditing || ! empty($this->password)) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'nim' => $this->nim ?: null,
            'nip' => $this->nip ?: null,
            'phone' => $this->phone ?: null,
            'faculty_id' => $this->faculty_id ?: null,
            'study_program_id' => $this->study_program_id ?: null,
        ];

        if (! empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEditing) {
            $user = User::findOrFail($this->editingId);
            $user->update($data);
            $message = 'Pengguna berhasil diperbarui.';
        } else {
            User::create($data);
            $message = 'Pengguna berhasil ditambahkan.';
        }

        $this->dispatch('modal-close', name: 'user-modal');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->dispatch('modal-show', name: 'delete-user-modal');
    }

    public function delete()
    {
        $user = User::findOrFail($this->deletingId);

        // Prevent deleting oneself
        if ($user->id === auth()->id()) {
            $this->dispatch('modal-close', name: 'delete-user-modal');
            $this->dispatch('toast', message: 'Anda tidak dapat menghapus akun Anda sendiri.', type: 'error');

            return;
        }

        $user->delete();

        $this->dispatch('modal-close', name: 'delete-user-modal');
        $this->dispatch('toast', message: 'Pengguna berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        $query = User::with(['faculty', 'studyProgram'])->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('nim', 'like', '%'.$this->search.'%')
                    ->orWhere('nip', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->filterRole) {
            $query->where('role', $this->filterRole);
        }

        return view('livewire.admin.users', [
            'users' => $query->paginate(15),
        ]);
    }
}
