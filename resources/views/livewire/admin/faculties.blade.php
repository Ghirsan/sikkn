<div>
    {{-- Heading --}}
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Fakultas') }}</flux:heading>
        <flux:subheading>{{ __('Kelola data fakultas dan akun pengelola fakultas di lingkungan universitas.') }}</flux:subheading>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="building-office-2" color="blue" :label="__('Total Fakultas')" :value="$stats['total_faculties']" />
        <x-stat-card icon="building-library" color="purple" :label="__('Total Program Studi')" :value="$stats['total_prodi']" />
        <x-stat-card icon="academic-cap" color="green" :label="__('Mahasiswa Terdaftar')" :value="$stats['total_students']" />
    </div>

    {{-- Search bar, filters, buttons --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, kode, singkatan...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            <flux:button variant="primary" size="sm" icon="plus" wire:click="create" class="w-full sm:w-auto">{{ __('Tambah Fakultas') }}</flux:button>
        </div>
    </div>

    <flux:card>
        @if($faculties->isEmpty())
            <x-empty-state icon="building-office-2" :heading="__('Belum ada data fakultas')" />
        @else
            <flux:table :paginate="$faculties">
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode') }}</flux:table.column>
                    <flux:table.column>{{ __('Singkatan') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama Fakultas') }}</flux:table.column>
                    <flux:table.column>{{ __('Akun Pengelola') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($faculties as $faculty)
                        <flux:table.row :key="$faculty->id">
                            <flux:table.cell>
                                <flux:badge>{{ $faculty->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $faculty->short_name ?: '-' }}</flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $faculty->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($faculty->observerUser)
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $faculty->observerUser->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $faculty->observerUser->email }}</div>
                                        </div>
                                        @if($faculty->observer_users_count > 1)
                                            <flux:badge color="amber" size="sm" title="{{ $faculty->observer_users_count }} akun terdaftar">+{{ $faculty->observer_users_count - 1 }}</flux:badge>
                                        @endif
                                    </div>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Belum Ada Akun') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="subtle" icon="user" wire:click="manageAccount({{ $faculty->id }})">
                                        {{ $faculty->observerUser ? __('Kelola Akun') : __('Buat Akun') }}
                                    </flux:button>
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

    <!-- Create/Edit Faculty Modal -->
    <flux:modal name="faculty-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Fakultas') : __('Tambah Fakultas') }}</flux:heading>
            
            <div class="space-y-4 my-4">
                <flux:input wire:model="code" label="{{ __('Kode') }}" placeholder="Ex: FSM" />
                <flux:input wire:model="short_name" label="{{ __('Singkatan') }}" placeholder="Ex: FSM (Opsional)" />
                <flux:input wire:model="name" label="{{ __('Nama Fakultas') }}" placeholder="Ex: Fakultas Sains dan Matematika" />
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Manage Faculty Account Modal -->
    <flux:modal name="faculty-account-modal" class="md:w-[30rem]">
        <form wire:submit.prevent="saveAccount">
            <flux:heading size="lg">{{ $accountUserId ? __('Kelola Akun Fakultas') : __('Buat Akun Fakultas') }}</flux:heading>
            <flux:subheading>{{ $selectedFacultyName }}</flux:subheading>

            {{-- Duplicate Accounts Warning & Resolver --}}
            @if(!empty($duplicateAccounts))
                <flux:callout color="amber" icon="exclamation-triangle" class="my-4">
                    <flux:callout.heading>{{ __('Terdeteksi lebih dari satu akun untuk fakultas ini:') }}</flux:callout.heading>
                    <flux:callout.text>
                        <div class="mt-2 space-y-2">
                            @foreach($duplicateAccounts as $dup)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $dup['name'] }} <span class="font-normal text-zinc-500">({{ $dup['email'] }})</span></span>
                                    <flux:button size="xs" variant="primary" wire:click="resolveDuplicateAccount({{ $dup['id'] }})">
                                        {{ __('Jadikan Akun Resmi') }}
                                    </flux:button>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">
                            {{ __('Memilih akun resmi akan membersihkan duplikasi dan menyisakan satu akun resmi.') }}
                        </div>
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="space-y-4 my-4">
                <flux:input wire:model="accountName" label="{{ __('Nama Akun / PIC') }}" placeholder="Ex: FAKULTAS TEKNIK" />
                <flux:input type="email" wire:model="accountEmail" label="{{ __('Email Akun') }}" placeholder="Ex: fakultas.ft@univ.ac.id" />
                <flux:input
                    type="password"
                    wire:model="accountPassword"
                    label="{{ __('Password') }}"
                    placeholder="{{ $accountUserId ? __('Kosongkan jika tidak ingin mengubah') : __('Default: password') }}"
                    viewable
                />
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <div class="w-full sm:w-auto">
                    @if($accountUserId)
                        <flux:button type="button" variant="danger" class="w-full sm:w-auto" wire:click="confirmDeleteAccount">
                            {{ __('Hapus Akun') }}
                        </flux:button>
                    @endif
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                    <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                        <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Simpan Akun') }}</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <!-- Delete Faculty Account Confirmation Modal -->
    <flux:modal name="delete-faculty-account-modal" class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Akun Fakultas?') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menghapus akun pengelola fakultas') }} <strong class="text-zinc-900 dark:text-zinc-100">{{ $selectedFacultyName }}</strong>? {{ __('Akun ini tidak akan dapat digunakan untuk masuk ke sistem lagi.') }}
                </flux:text>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="deleteAccount" class="w-full sm:w-auto">{{ __('Hapus Akun') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Delete Faculty Confirmation Modal -->
    <flux:modal name="delete-faculty-modal" class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Fakultas?') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menghapus fakultas ini? Data yang sudah memiliki program studi atau pengguna terkait tidak dapat dihapus.') }}
                </flux:text>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" class="w-full sm:w-auto">{{ __('Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
