<?php

namespace App\Livewire\Admin;

use App\Models\ProgramParticipant;
use App\Models\SdgCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SdgCategories extends Component
{
    public $sdgCategories;

    public $isEditing = false;

    public $editingId = null;

    public $name = '';

    public $code = '';

    public $deletingId = null;

    public function mount()
    {
        $this->loadSdgCategories();
    }

    public function loadSdgCategories()
    {
        $this->sdgCategories = SdgCategory::orderBy('code')->get();
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'isEditing', 'editingId']);
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('modal-show', name: 'sdg-category-modal');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingId = $id;

        $type = SdgCategory::findOrFail($id);
        $this->name = $type->name;
        $this->code = $type->code;

        $this->dispatch('modal-show', name: 'sdg-category-modal');
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'integer',
                Rule::unique('sdg_categories', 'code')->ignore($this->editingId),
            ],
        ]);

        if ($this->isEditing) {
            $type = SdgCategory::findOrFail($this->editingId);
            $type->update([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            $message = 'Kategori SDG berhasil diperbarui.';
        } else {
            SdgCategory::create([
                'name' => $this->name,
                'code' => $this->code,
            ]);
            $message = 'Kategori SDG berhasil ditambahkan.';
        }

        $this->loadSdgCategories();
        $this->dispatch('modal-close', name: 'sdg-category-modal');
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function confirmDelete($id)
    {
        $this->deletingId = $id;
        $this->dispatch('modal-show', name: 'delete-sdg-category-modal');
    }

    public function delete()
    {
        $type = SdgCategory::findOrFail($this->deletingId);

        // Safeguard: Check if it's used
        if (ProgramParticipant::where('sdg_category_id', $type->id)->exists()) {
            $this->dispatch('modal-close', name: 'delete-sdg-category-modal');
            $this->dispatch('toast', message: 'Tidak dapat menghapus kategori SDG yang sudah digunakan pada partisipan program.', type: 'error');

            return;
        }

        $type->delete();

        $this->loadSdgCategories();
        $this->dispatch('modal-close', name: 'delete-sdg-category-modal');
        $this->dispatch('toast', message: 'Kategori SDG berhasil dihapus.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.sdg-categories');
    }
}
