<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Heading --}}
    <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>

    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ditempatkan')" :value="$stats['assigned']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Ditempatkan')" :value="$stats['unassigned']" />
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau Prodi...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            <flux:select wire:model.live="periodId" size="sm" class="w-full sm:w-48" placeholder="{{ __('Semua Periode') }}">
                <flux:select.option value="">{{ __('Semua Periode') }}</flux:select.option>
                @foreach($periods as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->display_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button wire:click="exportExcel" variant="ghost" size="sm" icon="arrow-down-tray" class="w-full sm:w-auto">{{ __('Format Excel') }}</flux:button>
            <flux:button wire:click="openImportModal" variant="outline" size="sm" icon="arrow-up-tray" class="w-full sm:w-auto">{{ __('Import Excel') }}</flux:button>
            <flux:button wire:click="openCreateModal" variant="filled" size="sm" icon="plus" class="w-full sm:w-auto">{{ __('Tambah Peserta') }}</flux:button>
        </div>
    </div>

    <flux:card>

        @if($students->isEmpty())
            <x-empty-state icon="academic-cap" :heading="__('Tidak Ada Data Mahasiswa')" />
        @else
            <flux:table :paginate="$students">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Kontak') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok / Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$student->name" :initials="$student->initials()" size="sm" />
                                <span class="font-medium">{{ $student->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $student->nim }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $student->prodi }}
                                <div class="text-xs text-neutral-500">{{ $student->fakultas }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="space-y-1.5 text-xs">
                                    @if($student->phone)
                                        <div>
                                            <flux:link
                                                href="{{ $student->whatsappUrl() ?? $student->phone }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                variant="subtle"
                                                class="inline-flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300"
                                                aria-label="{{ __('Hubungi :name melalui WhatsApp', ['name' => $student->name]) }}"
                                            >
                                                <img src="{{ asset('whatsapp.svg') }}" alt="" class="size-4 shrink-0 object-contain">
                                                <span>{{ $student->phone }}</span>
                                            </flux:link>
                                        </div>
                                    @endif
                                    @if($student->emergency_phone)
                                        <div>
                                            <flux:link
                                                href="{{ $student->whatsappUrl($student->emergency_phone) ?? $student->emergency_phone }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                variant="subtle"
                                                class="inline-flex items-center gap-1.5"
                                                aria-label="{{ __('Hubungi kontak darurat :name melalui WhatsApp', ['name' => $student->name]) }}"
                                            >
                                                <img src="{{ asset('whatsapp.svg') }}" alt="" class="size-3.5 shrink-0 object-contain">
                                                <span>
                                                    {{ __('Darurat') }}:
                                                    @if($student->emergency_name)
                                                        {{ $student->emergency_name }}
                                                        @if($student->emergency_relationship)
                                                            ({{ $student->emergency_relationship }})
                                                        @endif
                                                        -
                                                    @endif
                                                    {{ $student->emergency_phone }}
                                                </span>
                                            </flux:link>
                                        </div>
                                    @endif
                                    @if(! $student->phone && ! $student->emergency_phone)
                                        <flux:text variant="subtle" class="text-xs">-</flux:text>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->group)
                                    <flux:badge size="sm" color="green" inset="top bottom">{{ $student->group->name }}</flux:badge>
                                    <div class="mt-1 text-xs text-neutral-400">Semester {{ $student->group->period->semester->value }} {{ $student->group->period->year }}</div>
                                @else
                                    <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Belum Punya Kelompok') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button wire:click="openEditModal({{ $student->id }})" size="sm" variant="ghost" icon="pencil-square">{{ __('Edit') }}</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Import Mahasiswa Modal --}}
    <flux:modal wire:model="showImportModal" class="md:w-[32rem]">
        <form wire:submit.prevent="importExcel" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Import Data Mahasiswa via Excel') }}</flux:heading>
                <flux:subheading>{{ __('Unggah file Excel daftar mahasiswa dan alokasi kelompok untuk periode KKN terpilih.') }}</flux:subheading>
            </div>

            @if(! empty($importErrors))
                <div class="rounded-lg bg-red-50 dark:bg-red-950/40 p-4 border border-red-200 dark:border-red-900 text-sm text-red-700 dark:text-red-300 space-y-2">
                    <div class="font-medium flex items-center gap-1.5">
                        <flux:icon.exclamation-circle variant="micro" class="size-4 shrink-0" />
                        <span>{{ __('Gagal mengimpor data:') }}</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach($importErrors as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-4">
                <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/60 p-3 text-xs text-zinc-600 dark:text-zinc-400 space-y-1.5 border border-zinc-200 dark:border-zinc-700/60">
                    <p class="font-medium text-zinc-900 dark:text-zinc-200">{{ __('Petunjuk Format:') }}</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li>{{ __('Kolom wajib: NIM, Nama Mahasiswa, Nama Kelompok.') }}</li>
                        <li>{{ __('Nama Kelompok harus sudah terdaftar pada periode KKN yang dipilih.') }}</li>
                        <li>{{ __('Kolom opsional: Email, Fakultas, Program Studi, No HP, Ketua Kelompok (Ya/Tidak).') }}</li>
                        <li>{{ __('Jika akun mahasiswa belum ada, sistem akan otomatis membuatnya.') }}</li>
                    </ul>
                </div>

                <div>
                    <flux:input type="file" wire:model="importFile" label="{{ __('Pilih File Excel (.xlsx)') }}" accept=".xlsx" />
                    <flux:error name="importFile" />
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button wire:click="closeImportModal" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="importExcel">{{ __('Unggah & Proses') }}</span>
                    <span wire:loading wire:target="importExcel">{{ __('Memproses...') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Create/Edit Student Modal --}}
    <flux:modal wire:model="showCreateModal" class="md:w-[38rem]">
        <form wire:submit.prevent="saveStudent" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $isEditing ? __('Edit Data Mahasiswa') : __('Tambah Peserta KKN') }}</flux:heading>
                <flux:subheading>{{ $isEditing ? __('Perbarui data mahasiswa dan alokasi kelompok KKN.') : __('Daftarkan mahasiswa dan alokasikan ke kelompok KKN.') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="nim" label="{{ __('NIM') }}" placeholder="{{ __('Contoh: 21120120120001') }}" required />
                    <flux:input wire:model="name" label="{{ __('Nama Mahasiswa') }}" placeholder="{{ __('Contoh: Muhammad Budi') }}" required />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="email" label="{{ __('Email') }}" type="email" placeholder="{{ __('Otomatis: {nim}@student.sikkn.ac.id') }}" />
                    <flux:input wire:model="phone" label="{{ __('No. Telepon / WhatsApp') }}" placeholder="{{ __('Contoh: 081234567890') }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="study_program_id" label="{{ __('Program Studi') }}">
                        <flux:select.option value="">{{ __('Pilih Program Studi') }}</flux:select.option>
                        @foreach($studyPrograms as $sp)
                            <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="group_id" label="{{ __('Kelompok KKN') }}">
                        <flux:select.option value="">{{ __('Belum Ditempatkan') }}</flux:select.option>
                        @foreach($availableGroups as $g)
                            <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input wire:model="password" label="{{ __('Password') }}" type="password" placeholder="{{ $isEditing ? __('Kosongkan jika tidak ingin mengubah') : __('Default: password') }}" />
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button wire:click="closeCreateModal" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ $isEditing ? __('Simpan Perubahan') : __('Simpan Peserta') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
