<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">{{ __('Pengguna Sistem') }}</flux:heading>
            <flux:subheading>{{ __('Kelola semua pengguna dalam sistem SIKKN.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="create">{{ __('Tambah Pengguna') }}</flux:button>
    </div>

    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 items-center gap-4">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, email, NIM, NIP...') }}" class="max-w-md" />
            <flux:select wire:model.live="filterRole" placeholder="{{ __('Semua Role') }}" class="max-w-xs">
                <flux:select.option value="">{{ __('Semua Role') }}</flux:select.option>
                @foreach($roles as $r)
                    <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <flux:card>
        @if($users->isEmpty())
            <x-empty-state icon="users" :heading="__('Belum ada pengguna ditemukan')" />
        @else
            <flux:table :paginate="$users">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama & Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Role') }}</flux:table.column>
                    <flux:table.column>{{ __('Identitas') }}</flux:table.column>
                    <flux:table.column>{{ __('Afiliasi') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</div>
                                        <div class="text-sm text-zinc-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$user->role->color()" size="sm">{{ $user->role->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($user->nim)
                                    <div class="text-sm"><span class="text-zinc-500">NIM:</span> {{ $user->nim }}</div>
                                @endif
                                @if($user->nip)
                                    <div class="text-sm"><span class="text-zinc-500">NIP:</span> {{ $user->nip }}</div>
                                @endif
                                @if(!$user->nim && !$user->nip)
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($user->studyProgram)
                                    <div class="text-sm truncate max-w-[200px]" title="{{ $user->studyProgram->name }}">{{ $user->studyProgram->name }}</div>
                                @elseif($user->faculty)
                                    <div class="text-sm truncate max-w-[200px]" title="{{ $user->faculty->name }}">{{ $user->faculty->name }}</div>
                                @else
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $user->id }})" />
                                    <flux:button size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" wire:click="confirmDelete({{ $user->id }})" />
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    <!-- Create/Edit Modal -->
    <flux:modal name="user-modal" class="md:w-[32rem]">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Pengguna') : __('Tambah Pengguna') }}</flux:heading>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
                <div class="space-y-4 md:col-span-2">
                    <flux:input wire:model="name" label="{{ __('Nama Lengkap') }}" placeholder="Ex: Budi Santoso" />
                    <flux:input type="email" wire:model="email" label="{{ __('Email') }}" placeholder="Ex: budi@example.com" />
                </div>
                
                <flux:select wire:model="role" label="{{ __('Role') }}" placeholder="{{ __('Pilih Role...') }}">
                    @foreach($roles as $r)
                        <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                
                <flux:input type="password" wire:model="password" label="{{ $isEditing ? __('Password Baru (Kosongkan jika tidak diubah)') : __('Password') }}" />
                
                <flux:input wire:model="nim" label="{{ __('NIM (Untuk Mahasiswa)') }}" />
                <flux:input wire:model="nip" label="{{ __('NIP (Untuk DPL/Prodi/Fakultas)') }}" />
                
                <div class="space-y-4 md:col-span-2">
                    <flux:input wire:model="phone" label="{{ __('No Telepon / WA') }}" />
                </div>
                
                <flux:select wire:model="faculty_id" label="{{ __('Fakultas (Opsional)') }}" placeholder="{{ __('Pilih Fakultas...') }}">
                    <flux:select.option value="">{{ __('Tidak ada') }}</flux:select.option>
                    @foreach($faculties as $faculty)
                        <flux:select.option value="{{ $faculty->id }}">{{ $faculty->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                
                <flux:select wire:model="study_program_id" label="{{ __('Program Studi (Opsional)') }}" placeholder="{{ __('Pilih Prodi...') }}">
                    <flux:select.option value="">{{ __('Tidak ada') }}</flux:select.option>
                    @foreach($studyPrograms as $prodi)
                        <flux:select.option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->faculty->short_name }})</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-user-modal" class="md:w-96">
        <flux:heading size="lg" class="text-red-600">{{ __('Hapus Pengguna') }}</flux:heading>
        
        <div class="my-4">
            <flux:text>{{ __('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.') }}</flux:text>
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

