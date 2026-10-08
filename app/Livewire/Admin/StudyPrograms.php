<?php

namespace App\Livewire\Admin;

use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StudyPrograms extends Component
{
    public $studyPrograms;

    public $faculties;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $faculty_id = '';

    public $deletingId = null;

    public function mount()
    {
        $this->faculties = Faculty::orderBy('name')->get();
        $this->loadStudyPrograms();
    }

    public function loadStudyPrograms()
    {
        $this->studyPrograms = StudyProgram::with('faculty')->orderBy('faculty_id')->orderBy('code')->get();
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'faculty_id', 'isEditing', 'editingId']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'study-program-modal');
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

        $this->dispatch('modal-show', name: 'study-program-modal');
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

        $this->loadStudyPrograms();
        $this->dispatch('modal-close', name: 'study-program-modal');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->dispatch('modal-show', name: 'delete-study-program-modal');
    }

    public function delete()
    {
        $prodi = StudyProgram::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (User::where('study_program_id', $prodi->id)->exists()) {
            $this->dispatch('modal-close', name: 'delete-study-program-modal');
            $this->dispatch('toast', message: 'Tidak dapat menghapus program studi yang sudah memiliki pengguna terkait.', type: 'error');

            return;
        }

        $prodi->delete();

        $this->loadStudyPrograms();
        $this->dispatch('modal-close', name: 'delete-study-program-modal');
        $this->dispatch('toast', message: 'Program studi berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.study-programs');
    }
}
