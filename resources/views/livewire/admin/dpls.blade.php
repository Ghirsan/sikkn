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
            <flux:button variant="filled" size="sm" icon="plus" class="w-full sm:w-auto">{{ __('Tambah Dosen KKN') }}</flux:button>
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
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Import DPL Modal --}}
    <flux:modal wire:model="showImportModal" class="md:w-[32rem]">
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
                        <li>{{ __('Kolom opsional: Email, Program Studi, No HP, DPL Utama (Ya/Tidak).') }}</li>
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
