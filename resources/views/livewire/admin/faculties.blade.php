<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Fakultas') }}</flux:heading>
            <flux:subheading>{{ __('Kelola data fakultas di lingkungan universitas.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Tambah Fakultas') }}</flux:button>
    </div>

    <flux:card>
        @if($faculties->isEmpty())
            <x-empty-state icon="building-office-2" :heading="__('Belum ada data fakultas')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode') }}</flux:table.column>
                    <flux:table.column>{{ __('Singkatan') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama Fakultas') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($faculties as $faculty)
                        <flux:table.row :key="$faculty->id">
                            <flux:table.cell>
                                <flux:badge>{{ $faculty->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $faculty->short_name ?: '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $faculty->name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $faculty->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" wire:click="confirmDelete({{ $faculty->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <!-- Create/Edit Modal -->
    <flux:modal name="faculty-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Fakultas') : __('Tambah Fakultas') }}</flux:heading>
            
            <div class="space-y-4 my-4">
                <flux:input wire:model="code" label="{{ __('Kode') }}" placeholder="Ex: FSM" />
                <flux:input wire:model="short_name" label="{{ __('Singkatan') }}" placeholder="Ex: FSM (Opsional)" />
                <flux:input wire:model="name" label="{{ __('Nama Fakultas') }}" placeholder="Ex: Fakultas Sains dan Matematika" />
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
    <flux:modal name="delete-faculty-modal" class="md:w-96">
        <flux:heading size="lg" class="text-red-600">{{ __('Hapus Fakultas') }}</flux:heading>
        
        <div class="my-4">
            <flux:text>{{ __('Apakah Anda yakin ingin menghapus fakultas ini? Data yang sudah memiliki program studi atau pengguna terkait tidak dapat dihapus.') }}</flux:text>
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

