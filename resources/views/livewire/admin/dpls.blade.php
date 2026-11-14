<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="user-circle" color="green" :label="__('Total Dosen KKN')" :value="$stats['total']" />
        <x-stat-card icon="user-group" color="blue" :label="__('Membimbing')" :value="$stats['assigned']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Menugaskan')" :value="$stats['unassigned']" />
    </div>

    {{-- Heading --}}
    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Dosen KKN') }}</flux:heading>
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIP...') }}" size="sm" class="w-full" />
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
            <flux:button wire:click="openCreateModal" variant="filled" size="sm" icon="plus" class="w-full sm:w-auto">{{ __('Tambah Dosen KKN') }}</flux:button>
        </div>
    </div>

    <flux:card>

        @if($dpls->isEmpty())
            <x-empty-state icon="user-circle" :heading="__('Tidak Ada Data Dosen KKN')" />
        @else
            <flux:table :paginate="$dpls">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Dosen KKN') }}</flux:table.column>
                    <flux:table.column>{{ __('NIP') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($dpls as $dpl)
                        <flux:table.row :key="$dpl->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$dpl->name" :initials="$dpl->initials()" size="sm" />
                                <span class="font-medium">{{ $dpl->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $dpl->nip }}</flux:table.cell>
                            <flux:table.cell>{{ $dpl->prodi }}</flux:table.cell>
                            <flux:table.cell>
                                @if($dpl->dplGroups->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($dpl->dplGroups as $group)
                                            <flux:badge size="sm" color="blue" inset="top bottom">{{ $group->name }}</flux:badge>
                                        @endforeach
                                    </div>
                                @else
                                    <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Belum Ditugaskan') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @php($canDelete = ! $dpl->hasDplActivityRecords())
                                <div class="flex items-center gap-2">
                                    <flux:button wire:click="openEditModal({{ $dpl->id }})" size="sm" variant="ghost" icon="pencil-square">{{ __('Edit') }}</flux:button>
                                    <flux:button
                                        wire:click="confirmDelete({{ $dpl->id }})"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        class="{{ $canDelete ? '' : 'cursor-pointer opacity-50 hover:bg-red-500! dark:hover:bg-red-600!' }}"
                                        aria-disabled="{{ $canDelete ? 'false' : 'true' }}"
                                        tooltip="{{ $canDelete ? __('Hapus Dosen KKN') : __('Tidak dapat dihapus: sudah memiliki riwayat penilaian/aktivitas') }}"
                                    >{{ __('Hapus') }}</flux:button>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Import DPL Modal --}}
    <flux:modal wire:model="showImportModal" @close="closeImportModal" class="md:w-[32rem]">
        @if($generatedCredentialsCount > 0)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Import Dosen KKN Selesai') }}</flux:heading>
                    <flux:subheading>{{ $importSummaryMessage }}</flux:subheading>
                </div>

                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 space-y-2 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-200">
                    <div class="font-medium">{{ __('Kredensial Akun Baru Tersedia (:count akun)', ['count' => $generatedCredentialsCount]) }}</div>
                    <p class="text-xs leading-relaxed">
                        {{ __('Unduh file Excel password sementara untuk akun dosen baru sekarang. Demi keamanan, password sementara tidak disimpan dalam bentuk teks biasa dan hanya dapat diunduh satu kali.') }}
                    </p>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                    <flux:button wire:click="closeImportModal" variant="ghost" class="w-full sm:w-auto">{{ __('Tutup') }}</flux:button>
                    <flux:button wire:click="downloadGeneratedCredentials" variant="primary" icon="arrow-down-tray" class="w-full sm:w-auto">
                        {{ __('Unduh Kredensial (.xlsx)') }}
                    </flux:button>
                </div>
            </div>
        @else
            <form wire:submit.prevent="importExcel" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Import Data Dosen KKN (DPL) via Excel') }}</flux:heading>
                    <flux:subheading>{{ __('Unggah file Excel daftar dosen pembimbing dan penugasan kelompok untuk periode KKN terpilih.') }}</flux:subheading>
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
                            <li>{{ __('Kolom wajib: NIP, Nama DPL, Nama Kelompok.') }}</li>
                            <li>{{ __('Nama Kelompok harus sudah terdaftar pada periode KKN yang dipilih.') }}</li>
                            <li>{{ __('Dosen dengan multi-kelompok dapat ditulis beberapa baris dengan NIP sama dan Nama Kelompok berbeda.') }}</li>
                            <li>{{ __('Kolom opsional: Email, Program Studi, No HP, DPL Utama (Ya/Tidak), Password.') }}</li>
                            <li>{{ __('Akun baru tanpa kolom Password akan dibuatkan password sementara acak yang dapat diunduh setelah proses selesai.') }}</li>
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
        @endif
    </flux:modal>

    {{-- Create/Edit DPL Modal --}}
    <flux:modal wire:model="showCreateModal" class="md:w-[38rem]">
        <form wire:submit.prevent="saveDpl" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $isEditing ? __('Edit Data Dosen KKN') : __('Tambah Dosen Pembimbing (DPL)') }}</flux:heading>
                <flux:subheading>{{ $isEditing ? __('Perbarui data dosen pembimbing dan penugasan kelompok KKN.') : __('Daftarkan dosen pembimbing dan tugaskan ke kelompok KKN.') }}</flux:subheading>
            </div>

            <div class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="nip" label="{{ __('NIP') }}" placeholder="{{ __('Contoh: 198001012005011001') }}" required />
                    <flux:input wire:model="name" label="{{ __('Nama Dosen (Lengkap dengan Gelar)') }}" placeholder="{{ __('Contoh: Dr. Ir. Sutrisno, M.T.') }}" required />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="email" label="{{ __('Email') }}" type="email" placeholder="{{ __('Otomatis: {nip}@lecturer.sikkn.ac.id') }}" />
                    <flux:input wire:model="phone" label="{{ __('No. Telepon / WhatsApp') }}" placeholder="{{ __('Contoh: 081234567890') }}" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="study_program_id" label="{{ __('Program Studi') }}">
                        <flux:select.option value="">{{ __('Pilih Program Studi') }}</flux:select.option>
                        @foreach($studyPrograms as $sp)
                            <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model="group_id" label="{{ __('Tugaskan ke Kelompok') }}">
                        <flux:select.option value="">{{ __('Belum Ditugaskan') }}</flux:select.option>
                        @foreach($availableGroups as $g)
                            <flux:select.option value="{{ $g->id }}">{{ $g->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                @if($isEditing)
                    <div class="flex flex-col gap-3 border-t border-zinc-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
                        <div class="space-y-0.5">
                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('Password Akun') }}</flux:text>
                            <flux:text class="text-xs">{{ __('Buat password sementara acak baru untuk login berikutnya.') }}</flux:text>
                        </div>
                        <flux:button type="button" wire:click="resetPassword" variant="outline" icon="key" class="w-full shrink-0 sm:w-auto">
                            {{ __('Reset Password Sementara') }}
                        </flux:button>
                    </div>
                @else
                    <flux:input wire:model="password" label="{{ __('Password') }}" type="password" placeholder="{{ __('Kosongkan untuk password sementara acak') }}" viewable />
                @endif
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button wire:click="closeCreateModal" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ $isEditing ? __('Simpan Perubahan') : __('Simpan Dosen') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete DPL Confirmation Modal --}}
    <flux:modal name="delete-dpl-modal" @close="cancelDelete" class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Dosen KKN?') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menghapus akun dosen KKN') }} <strong class="text-zinc-900 dark:text-zinc-100">{{ $deletingDplName }}</strong>? {{ __('Penugasan kelompok maupun status DPL utama akan dilepas otomatis.') }}
                </flux:text>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button wire:click="cancelDelete" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" class="w-full sm:w-auto">{{ __('Ya, Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    <x-issued-credential-modal :credential="$issuedCredential" />
</div>
