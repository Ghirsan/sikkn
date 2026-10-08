<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Jenis Kelompok') }}</flux:heading>
            <flux:subheading>{{ __('Kelola referensi jenis kelompok KKN.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Tambah Jenis Kelompok') }}</flux:button>
    </div>

    <flux:card>
        @if($groupTypes->isEmpty())
            <x-empty-state icon="tag" :heading="__('Belum ada data jenis kelompok')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($groupTypes as $type)
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
    <flux:modal name="group-type-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Jenis Kelompok') : __('Tambah Jenis Kelompok') }}</flux:heading>
            
            <div class="space-y-4 my-4">
                <flux:input wire:model="code" label="{{ __('Kode') }}" placeholder="Ex: Reguler, Internasional" />
                <flux:input wire:model="name" label="{{ __('Nama') }}" placeholder="Ex: KKN Reguler" />
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
    <flux:modal name="delete-group-type-modal" class="md:w-96">
        <flux:heading size="lg" class="text-red-600">{{ __('Hapus Jenis Kelompok') }}</flux:heading>
        
        <div class="my-4">
            <flux:text>{{ __('Apakah Anda yakin ingin menghapus jenis kelompok ini? Data yang sudah digunakan pada kelompok tidak dapat dihapus.') }}</flux:text>
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
