<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Program Studi') }}</flux:heading>
            <flux:subheading>{{ __('Kelola data program studi di lingkungan universitas.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Tambah Program Studi') }}</flux:button>
    </div>

    <flux:card>
        @if($studyPrograms->isEmpty())
            <x-empty-state icon="building-library" :heading="__('Belum ada data program studi')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Fakultas') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($studyPrograms as $prodi)
                        <flux:table.row :key="$prodi->id">
                            <flux:table.cell>
                                <flux:badge>{{ $prodi->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $prodi->name }}</flux:table.cell>
                            <flux:table.cell>{{ $prodi->faculty->name }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $prodi->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" wire:click="confirmDelete({{ $prodi->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <!-- Create/Edit Modal -->
    <flux:modal name="study-program-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Program Studi') : __('Tambah Program Studi') }}</flux:heading>
            
            <div class="space-y-4 my-4">
                <flux:input wire:model="code" label="{{ __('Kode') }}" placeholder="Ex: 51" />
                <flux:input wire:model="name" label="{{ __('Nama Program Studi') }}" placeholder="Ex: Teknik Informatika" />
                
                <flux:select wire:model="faculty_id" label="{{ __('Fakultas') }}" placeholder="{{ __('Pilih Fakultas...') }}">
                    @foreach($faculties as $faculty)
                        <flux:select.option value="{{ $faculty->id }}">{{ $faculty->name }}</flux:select.option>
                    @endforeach
                </flux:select>
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
    <flux:modal name="delete-study-program-modal" class="md:w-96">
        <flux:heading size="lg" class="text-red-600">{{ __('Hapus Program Studi') }}</flux:heading>
        
        <div class="my-4">
            <flux:text>{{ __('Apakah Anda yakin ingin menghapus program studi ini? Data yang sudah memiliki pengguna terkait tidak dapat dihapus.') }}</flux:text>
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

