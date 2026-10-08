<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Kategori SDG') }}</flux:heading>
            <flux:subheading>{{ __('Kelola referensi Sustainable Development Goals (SDG).') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Tambah Kategori SDG') }}</flux:button>
    </div>

    <flux:card>
        @if($sdgCategories->isEmpty())
            <x-empty-state icon="globe-americas" :heading="__('Belum ada data kategori SDG')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode / No') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($sdgCategories as $type)
                        <flux:table.row :key="$type->id">
                            <flux:table.cell>
                                <flux:badge>{{ $type->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $type->name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $type->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" wire:click="confirmDelete({{ $type->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <!-- Create/Edit Modal -->
    <flux:modal name="sdg-category-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Kategori SDG') : __('Tambah Kategori SDG') }}</flux:heading>
            
            <div class="space-y-4 my-4">
                <flux:input type="number" wire:model="code" label="{{ __('Kode / Nomor') }}" placeholder="Ex: 1" />
                <flux:input wire:model="name" label="{{ __('Nama') }}" placeholder="Ex: 1. No Poverty" />
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-sdg-category-modal" class="md:w-96">
        <flux:heading size="lg" class="text-red-600">{{ __('Hapus Kategori SDG') }}</flux:heading>
        
        <div class="my-4">
            <flux:text>{{ __('Apakah Anda yakin ingin menghapus kategori SDG ini? Data yang sudah digunakan tidak dapat dihapus.') }}</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" wire:click="delete">{{ __('Hapus') }}</flux:button>
        </div>
    </flux:modal>
</div>

