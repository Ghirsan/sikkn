<?php

namespace App\Livewire\Admin;

use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class StudyPrograms extends Component
{
    use WithPagination;

    public $faculties;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $faculty_id = '';

    public string $search = '';

    public string $facultyFilter = '';

    public $deletingId = null;

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

        $this->dispatch('modal-close', name: 'delete-study-program-modal');
        $this->dispatch('toast', message: 'Program studi berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        $studyPrograms = StudyProgram::with('faculty')
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
        ]);
    }
}
