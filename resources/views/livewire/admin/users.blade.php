<div>
    {{-- Heading --}}
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Pengguna Sistem') }}</flux:heading>
        <flux:subheading>{{ __('Kelola akun pengelola dan observer sistem (Admin P2KKN, Fakultas, Program Studi).') }}</flux:subheading>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid auto-rows-min gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card icon="users" color="neutral" :label="__('Total Pengelola')" :value="$stats['total']" />
        <x-stat-card icon="shield-check" color="amber" :label="__('Admin P2KKN')" :value="$stats['p2kkn']" />
        <x-stat-card icon="building-office-2" color="blue" :label="__('Akun Fakultas')" :value="$stats['fakultas']" />
        <x-stat-card icon="building-library" color="purple" :label="__('Akun Program Studi')" :value="$stats['prodi']" />
    </div>

    {{-- Search bar, filters, buttons --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, email...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            <flux:select wire:model.live="filterRole" size="sm" class="w-full sm:w-48">
                <flux:select.option value="">{{ __('Semua Role') }}</flux:select.option>
                @foreach($roles as $r)
                    <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button variant="primary" size="sm" icon="plus" wire:click="create" class="w-full sm:w-auto">{{ __('Tambah Pengguna') }}</flux:button>
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
                                @if($user->studyProgram)
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate max-w-[220px]" title="{{ $user->studyProgram->name }}">{{ $user->studyProgram->name }}</div>
                                    @if($user->studyProgram->faculty)
                                        <div class="text-xs text-zinc-500">{{ $user->studyProgram->faculty->short_name ?: $user->studyProgram->faculty->name }}</div>
                                    @endif
                                @elseif($user->faculty)
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate max-w-[220px]" title="{{ $user->faculty->name }}">{{ $user->faculty->name }}</div>
                                    <div class="text-xs text-zinc-500">{{ $user->faculty->code }}</div>
                                @else
                                    <span class="text-xs text-zinc-400">{{ __('Pusat / P2KKN') }}</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="ghost" icon="key" wire:click="resetPassword({{ $user->id }})" tooltip="{{ __('Buat Password Sementara Baru') }}" />
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
                    <flux:input wire:model="name" label="{{ __('Nama Lengkap / Akun') }}" placeholder="Ex: Admin P2KKN / FAKULTAS TEKNIK / Prodi Hukum" />
                    <flux:input type="email" wire:model="email" label="{{ __('Email') }}" placeholder="Ex: staff@univ.ac.id" />
                </div>
                
                <div class="md:col-span-2">
                    <flux:select wire:model.live="role" label="{{ __('Role') }}">
                        <flux:select.option value="">{{ __('Pilih Role...') }}</flux:select.option>
                        @foreach($roles as $r)
                            <flux:select.option value="{{ $r->value }}">{{ $r->label() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                @if($role === \App\Enums\UserRole::Fakultas->value)
                    <div class="md:col-span-2">
                        <flux:select wire:model.live="faculty_id" label="{{ __('Fakultas') }}">
                            <flux:select.option value="">{{ __('Pilih Fakultas...') }}</flux:select.option>
                            @foreach($availableFaculties as $faculty)
                                <flux:select.option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @elseif($role === \App\Enums\UserRole::Prodi->value)
                    <div class="md:col-span-2">
                        <flux:select wire:model.live="study_program_id" label="{{ __('Program Studi') }}">
                            <flux:select.option value="">{{ __('Pilih Program Studi...') }}</flux:select.option>
                            @foreach($availableStudyPrograms as $prodi)
                                <flux:select.option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->faculty->short_name ?: $prodi->faculty->code }})</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                @endif

                <div class="md:col-span-2">
                    <flux:input
                        type="password"
                        wire:model="password"
                        label="{{ __('Password') }}"
                        placeholder="{{ $isEditing ? __('Kosongkan jika tidak ingin mengubah') : __('Kosongkan untuk password sementara acak') }}"
                        viewable
                    />
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-user-modal" class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Pengguna?') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.') }}
                </flux:text>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" class="w-full sm:w-auto">{{ __('Hapus Pengguna') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <x-issued-credential-modal :credential="$issuedCredential" />
</div>
