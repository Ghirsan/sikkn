<?php

namespace App\Livewire\Admin;

use App\Models\Program;
use App\Models\ProgramType;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramTypes extends Component
{
    use WithPagination;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public string $search = '';

    public $deletingId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'isEditing', 'editingId']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'program-type-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $type = ProgramType::findOrFail($id);
        $this->name = $type->name;
        $this->code = $type->code;

        $this->dispatch('modal-show', name: 'program-type-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('program_types', 'code')->ignore($this->editingId),
            ],
        ]);

        if ($this->isEditing) {
            $type = ProgramType::findOrFail($this->editingId);
            $type->update([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            $message = 'Sifat program berhasil diperbarui.';
        } else {
            ProgramType::create([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            $message = 'Sifat program berhasil ditambahkan.';
        }

        $this->dispatch('modal-close', name: 'program-type-modal');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->dispatch('modal-show', name: 'delete-program-type-modal');
    }

    public function delete()
    {
        $type = ProgramType::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (Program::where('program_type_id', $type->id)->exists()) {
            $this->dispatch('modal-close', name: 'delete-program-type-modal');
            $this->dispatch('toast', message: 'Tidak dapat menghapus sifat program yang sudah digunakan pada program kerja.', type: 'error');

            return;
        }

        $type->delete();

        $this->dispatch('modal-close', name: 'delete-program-type-modal');
        $this->dispatch('toast', message: 'Sifat program berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        $programTypes = ProgramType::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.admin.program-types', [
            'programTypes' => $programTypes,
        ]);
    }
}
