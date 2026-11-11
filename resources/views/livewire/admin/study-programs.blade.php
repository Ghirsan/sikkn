<div>
    {{-- Heading --}}
    <div class="mb-6">
        <flux:heading size="xl">{{ __('Program Studi') }}</flux:heading>
        <flux:subheading>{{ __('Kelola data program studi dan akun pengelola prodi di lingkungan universitas.') }}</flux:subheading>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="building-library" color="blue" :label="__('Total Program Studi')" :value="$stats['total_prodi']" />
        <x-stat-card icon="building-office-2" color="purple" :label="__('Fakultas Terafiliasi')" :value="$stats['total_faculties']" />
        <x-stat-card icon="academic-cap" color="green" :label="__('Mahasiswa Terdaftar')" :value="$stats['total_students']" />
    </div>

    {{-- Search bar, filters, buttons --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama atau kode prodi...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            <flux:select wire:model.live="facultyFilter" size="sm" class="w-full sm:w-48">
                <flux:select.option value="">{{ __('Semua Fakultas') }}</flux:select.option>
                @foreach($faculties as $faculty)
                    <flux:select.option value="{{ $faculty->id }}">{{ $faculty->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button variant="primary" size="sm" icon="plus" wire:click="create" class="w-full sm:w-auto">{{ __('Tambah Program Studi') }}</flux:button>
        </div>
    </div>

    <flux:card>
        @if($studyPrograms->isEmpty())
            <x-empty-state icon="building-library" :heading="__('Belum ada data program studi')" />
        @else
            <flux:table :paginate="$studyPrograms">
                <flux:table.columns>
                    <flux:table.column>{{ __('Kode') }}</flux:table.column>
                    <flux:table.column>{{ __('Nama Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Fakultas') }}</flux:table.column>
                    <flux:table.column>{{ __('Akun Pengelola') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                
                <flux:table.rows>
                    @foreach($studyPrograms as $prodi)
                        <flux:table.row :key="$prodi->id">
                            <flux:table.cell>
                                <flux:badge>{{ $prodi->code }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $prodi->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $prodi->faculty->name }}</flux:table.cell>
                            <flux:table.cell>
                                @if($prodi->observerUser)
                                    <div class="flex items-center gap-2">
                                        <div>
                                            <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $prodi->observerUser->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $prodi->observerUser->email }}</div>
                                        </div>
                                        @if($prodi->observer_users_count > 1)
                                            <flux:badge color="amber" size="sm" title="{{ $prodi->observer_users_count }} akun terdaftar">+{{ $prodi->observer_users_count - 1 }}</flux:badge>
                                        @endif
                                    </div>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Belum Ada Akun') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:button size="sm" variant="subtle" icon="user" wire:click="manageAccount({{ $prodi->id }})">
                                        {{ $prodi->observerUser ? __('Kelola Akun') : __('Buat Akun') }}
                                    </flux:button>
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

    <!-- Create/Edit Study Program Modal -->
    <flux:modal name="study-program-modal" class="md:w-96">
        <form wire:submit.prevent="save">
            <flux:heading size="lg">{{ $isEditing ? __('Edit Program Studi') : __('Tambah Program Studi') }}</flux:heading>
            
            <div class="space-y-4 my-4">
                <flux:input wire:model="code" label="{{ __('Kode') }}" placeholder="Ex: 51" />
                <flux:input wire:model="name" label="{{ __('Nama Program Studi') }}" placeholder="Ex: Teknik Informatika" />
                
                <flux:select wire:model="faculty_id" label="{{ __('Fakultas') }}">
                    <flux:select.option value="">{{ __('Pilih Fakultas...') }}</flux:select.option>
                    @foreach($faculties as $faculty)
                        <flux:select.option value="{{ $faculty->id }}">{{ $faculty->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <!-- Manage Study Program Account Modal -->
    <flux:modal name="prodi-account-modal" class="md:w-[30rem]">
        <form wire:submit.prevent="saveAccount">
            <flux:heading size="lg">{{ $accountUserId ? __('Kelola Akun Program Studi') : __('Buat Akun Program Studi') }}</flux:heading>
            <flux:subheading>{{ $selectedProdiName }}</flux:subheading>

            {{-- Duplicate Accounts Warning & Resolver --}}
            @if(!empty($duplicateAccounts))
                <flux:callout color="amber" icon="exclamation-triangle" class="my-4">
                    <flux:callout.heading>{{ __('Terdeteksi lebih dari satu akun untuk program studi ini:') }}</flux:callout.heading>
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
                <flux:input wire:model="accountName" label="{{ __('Nama Akun / PIC') }}" placeholder="Ex: Prodi Hukum / Prodi Informatika" />
                <flux:input type="email" wire:model="accountEmail" label="{{ __('Email Akun') }}" placeholder="Ex: prodi.if@univ.ac.id" />
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

    <!-- Delete Study Program Account Confirmation Modal -->
    <flux:modal name="delete-prodi-account-modal" class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Akun Program Studi?') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menghapus akun pengelola program studi') }} <strong class="text-zinc-900 dark:text-zinc-100">{{ $selectedProdiName }}</strong>? {{ __('Akun ini tidak akan dapat digunakan untuk masuk ke sistem lagi.') }}
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

    <!-- Delete Confirmation Modal -->
    <flux:modal name="delete-study-program-modal" class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Program Studi?') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menghapus program studi ini? Data yang sudah memiliki pengguna terkait tidak dapat dihapus.') }}
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
