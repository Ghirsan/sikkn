<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\GroupType;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class GroupTypes extends Component
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
        $this->dispatch('modal-show', name: 'group-type-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $type = GroupType::findOrFail($id);
        $this->name = $type->name;
        $this->code = $type->code;

        $this->dispatch('modal-show', name: 'group-type-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('group_types', 'code')->ignore($this->editingId),
            ],
        ]);

        if ($this->isEditing) {
            $type = GroupType::findOrFail($this->editingId);
            $type->update([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            $message = 'Jenis kelompok berhasil diperbarui.';
        } else {
            GroupType::create([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            $message = 'Jenis kelompok berhasil ditambahkan.';
        }

        $this->dispatch('modal-close', name: 'group-type-modal');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->dispatch('modal-show', name: 'delete-group-type-modal');
    }

    public function delete()
    {
        $type = GroupType::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (Group::where('group_type_id', $type->id)->exists()) {
            $this->dispatch('modal-close', name: 'delete-group-type-modal');
            $this->dispatch('toast', message: 'Tidak dapat menghapus jenis kelompok yang sudah digunakan.', type: 'error');

            return;
        }

        $type->delete();

        $this->dispatch('modal-close', name: 'delete-group-type-modal');
        $this->dispatch('toast', message: 'Jenis kelompok berhasil dihapus.', type: 'success');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $groupTypes = GroupType::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('id')
            ->paginate(10);

        return view('livewire.admin.group-types', [
            'groupTypes' => $groupTypes,
        ]);
    }
}
