<?php

namespace App\Livewire\Admin;

use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Faculties extends Component
{
    public $faculties;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $short_name = '';

    public $deletingId = null;

    public function mount()
    {
        $this->loadFaculties();
    }

    public function loadFaculties()
    {
        $this->faculties = Faculty::orderBy('code')->get();
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'short_name', 'isEditing', 'editingId']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'faculty-modal');
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

        $this->dispatch('modal-show', name: 'faculty-modal');
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

        $this->loadFaculties();
        $this->dispatch('modal-close', name: 'faculty-modal');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->dispatch('modal-show', name: 'delete-faculty-modal');
    }

    public function delete()
    {
        $faculty = Faculty::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (StudyProgram::where('faculty_id', $faculty->id)->exists() || User::where('faculty_id', $faculty->id)->exists()) {
            $this->dispatch('modal-close', name: 'delete-faculty-modal');
            $this->dispatch('toast', message: 'Tidak dapat menghapus fakultas yang sudah memiliki prodi atau pengguna terkait.', type: 'error');

            return;
        }

        $faculty->delete();

        $this->loadFaculties();
        $this->dispatch('modal-close', name: 'delete-faculty-modal');
        $this->dispatch('toast', message: 'Fakultas berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.faculties');
    }
}
