<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="user-group" color="purple" :label="__('Total Kelompok')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ada Dosen KKN')" :value="$stats['with_dpl']" />
        <x-stat-card icon="exclamation-triangle" color="amber" :label="__('Belum Ada Dosen KKN')" :value="$stats['without_dpl']" />
    </div>

    {{-- Heading --}}
    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Daftar Kelompok') }}</flux:heading>
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok atau desa...') }}" size="sm" class="w-full" />
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
            <flux:button variant="filled" size="sm" icon="plus" class="w-full sm:w-auto">{{ __('Buat Kelompok') }}</flux:button>
        </div>
    </div>

    <flux:card>

        @if($groups->isEmpty())
            <x-empty-state icon="user-group" :heading="__('Tidak Ada Data Kelompok')" />
        @else
            <flux:table :paginate="$groups">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                    <flux:table.column>{{ __('Periode') }}</flux:table.column>
                    <flux:table.column>{{ __('Lokasi (Desa, Kec)') }}</flux:table.column>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Dosen KKN') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($groups as $group)
                        <flux:table.row :key="$group->id">
                            <flux:table.cell variant="strong">{{ $group->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="blue">{{ $group->groupType?->name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">Semester {{ $group->period->semester->value }} {{ $group->period->year }}</flux:badge>
                                @if($group->start_date && $group->end_date)
                                    <flux:badge size="sm" color="blue" class="mt-1">{{ $group->start_date->format('d/m/Y') }} - {{ $group->end_date->format('d/m/Y') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>{{ $group->village }}, {{ $group->district }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.academic-cap variant="micro" class="text-neutral-400" />
                                    {{ $group->students_count }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.user variant="micro" class="text-neutral-400" />
                                    @if($group->dpls->isNotEmpty())
                                        {{ $group->dpls->pluck('name')->join(', ') }}
                                        @if($group->leadDpl)
                                            <flux:badge size="sm" color="green" class="ml-1">{{ __('Ketua:') }} {{ $group->leadDpl->name }}</flux:badge>
                                        @endif
                                    @else
                                        <flux:badge size="sm" color="zinc">{{ __('Belum Ditugaskan') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button wire:click="openAssignModal({{ $group->id }})" size="sm" variant="ghost" icon="user-plus">{{ __('Atur DPL') }}</flux:button>
                                <flux:button wire:click="openDatesModal({{ $group->id }})" size="sm" variant="ghost" icon="calendar">{{ __('Atur Waktu') }}</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Assign DPL Modal --}}
    <flux:modal wire:model="showAssignModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Atur Dosen Pembimbing Lapangan') }}</flux:heading>
                <flux:subheading>{{ __('Pilih dosen yang akan mendampingi kelompok ini.') }}</flux:subheading>
            </div>

            @if($assigningGroup)
                <div class="space-y-4">
                    <flux:checkbox.group wire:model.live="selectedDpls" label="{{ __('Pilih DPL') }}">
                        @foreach($availableDpls as $dpl)
                            <flux:checkbox value="{{ (string)$dpl->id }}" label="{{ $dpl->name }} ({{ $dpl->nip }})" />
                        @endforeach
                    </flux:checkbox.group>

                    @if(count($selectedDpls) > 0)
                        <flux:radio.group wire:model="leadDplId" label="{{ __('Pilih Ketua DPL (Opsional)') }}">
                            <flux:radio value="" label="{{ __('Tidak Ada Ketua') }}" />
                            @foreach($availableDpls->whereIn('id', $selectedDpls) as $dpl)
                                <flux:radio value="{{ $dpl->id }}" label="{{ $dpl->name }}" />
                            @endforeach
                        </flux:radio.group>
                    @endif
                </div>
            @endif

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button wire:click="closeAssignModal" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="saveAssignments" variant="primary" class="w-full sm:w-auto">{{ __('Simpan Perubahan') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Assign Dates Modal --}}
    <flux:modal wire:model="showDatesModal" class="md:w-96">
        <form wire:submit.prevent="saveDates" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Atur Waktu KKN Kelompok') }}</flux:heading>
                <flux:subheading>{{ __('Tentukan waktu mulai dan selesai KKN untuk kelompok ini.') }}</flux:subheading>
            </div>

            @if($editingGroup)
                <div class="space-y-4">
                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800 p-3 text-sm text-zinc-600 dark:text-zinc-400">
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('Waktu Periode KKN:') }}</span><br>
                        {{ $editingGroup->period->start_date->format('d/m/Y') }} - {{ $editingGroup->period->end_date->format('d/m/Y') }}
                    </div>

                    <flux:input type="date" wire:model="startDate" label="{{ __('Tanggal Mulai (Opsional)') }}" />
                    <flux:input type="date" wire:model="endDate" label="{{ __('Tanggal Selesai (Opsional)') }}" />
                    
                    <flux:text class="text-xs">{{ __('Biarkan kosong untuk menggunakan waktu periode KKN.') }}</flux:text>
                </div>
            @endif

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button wire:click="closeDatesModal" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="w-full sm:w-auto">{{ __('Simpan Perubahan') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Import Groups Modal --}}
    <flux:modal wire:model="showImportModal" class="md:w-[32rem]">
        <form wire:submit.prevent="importExcel" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Import Data Kelompok via Excel') }}</flux:heading>
                <flux:subheading>{{ __('Unggah file Excel berisi daftar kelompok untuk periode KKN yang dipilih.') }}</flux:subheading>
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
                        <li>{{ __('Kolom wajib: Nama Kelompok (unik per periode).') }}</li>
                        <li>{{ __('Kolom opsional: Jenis Kelompok (Reguler/Tematik), Desa, Kecamatan, Kabupaten, Provinsi, Mitra, Kepala Desa, Tanggal Mulai, Tanggal Selesai.') }}</li>
                        <li>{{ __('Gunakan tombol "Format Excel" untuk mengunduh template tabel.') }}</li>
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
</div>
