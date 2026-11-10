<div class="flex h-full w-full flex-1 flex-col gap-6">
    
    @if(!$isGradingOpen)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Periode Penilaian Ditutup') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Penilaian untuk periode KKN ini telah ditutup karena periode KKN baru telah dibuat.') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Dinilai')" :value="$stats['graded']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Dinilai')" :value="$stats['ungraded']" />
    </div>

    {{-- Heading --}}
    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau prodi...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            @if($allGroups->count() > 1)
                <flux:select wire:model.live="selectedGroupId" size="sm" class="w-full sm:w-48">
                    <flux:select.option value="">{{ __('Semua Kelompok') }}</flux:select.option>
                    @foreach($allGroups as $g)
                        <flux:select.option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</flux:select.option>
                    @endforeach
                </flux:select>
            @endif
            <flux:button wire:click="exportExcel" variant="ghost" size="sm" icon="arrow-down-tray" class="w-full sm:w-auto">
                {{ __('Format Excel') }}
            </flux:button>
            <flux:button wire:click="openImportModal" variant="outline" size="sm" icon="arrow-up-tray" :disabled="!$isGradingOpen" class="w-full sm:w-auto">
                {{ __('Import Excel') }}
            </flux:button>
        </div>
    </div>

    <flux:card>

        @if($students->isEmpty())
            <x-empty-state icon="academic-cap" :heading="__('Belum Ada Mahasiswa')" />
        @else
            <flux:table :paginate="$students">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Nilai Akhir') }}</flux:table.column>
                    <flux:table.column>{{ __('Nilai Huruf') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$student->name" :initials="$student->initials()" size="sm" />
                                <flux:text class="font-medium">{{ $student->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>{{ $student->nim }}</flux:table.cell>
                            <flux:table.cell>{{ $student->prodi }}</flux:table.cell>
                            <flux:table.cell>
                                @if($student->grade)
                                    <flux:text variant="strong">{{ $student->grade->final_grade }}</flux:text>
                                @else
                                    <flux:text variant="subtle">-</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->grade)
                                    <flux:text variant="strong">{{ $student->grade->grade_letter }}</flux:text>
                                @else
                                    <flux:text variant="subtle">-</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $canGradeStudent = $currentPeriod && $student->group?->period_id === $currentPeriod->id;
                                @endphp
                                <flux:button size="sm" variant="ghost" :icon="$canGradeStudent ? 'pencil-square' : 'eye'" href="{{ route('dpl.grades.form', $student->id) }}" wire:navigate>
                                    {{ $canGradeStudent ? __('Nilai Mahasiswa') : __('Lihat Nilai') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Import Nilai Modal --}}
    <flux:modal wire:model="showImportModal" class="md:w-[32rem]">
        <form wire:submit.prevent="importExcel" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Import Nilai Mahasiswa via Excel') }}</flux:heading>
                <flux:subheading>{{ __('Unggah file Excel nilai mahasiswa untuk kelompok yang Anda bimbing.') }}</flux:subheading>
            </div>

            @if(! empty($importErrors))
                <div class="rounded-lg bg-red-50 dark:bg-red-950/40 p-4 border border-red-200 dark:border-red-900 text-sm text-red-700 dark:text-red-300 space-y-2">
                    <div class="font-medium flex items-center gap-1.5">
                        <flux:icon.exclamation-circle variant="micro" class="size-4 shrink-0" />
                        <span>{{ __('Gagal mengimpor data:') }}</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-xs max-h-48 overflow-y-auto">
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
                        <li>{{ __('Gunakan tombol "Format Excel" untuk mengunduh template nilai kelompok.') }}</li>
                        <li>{{ __('Kolom wajib: NIM dan 8 aspek penilaian mahasiswa (skala 0 - 100).') }}</li>
                        <li>{{ __('Nilai desimal dapat menggunakan titik (.) atau koma (,).') }}</li>
                        <li>{{ __('Mahasiswa yang belum dinilai dapat dikosongkan dan akan otomatis dilewati.') }}</li>
                        <li>{{ __('Sistem akan menghitung komponen SIAP dan nilai akhir secara otomatis.') }}</li>
                    </ul>
                </div>

                <div>
                    <flux:input type="file" wire:model="gradeFile" label="{{ __('Pilih File Excel (.xlsx)') }}" accept=".xlsx" />
                    <flux:error name="gradeFile" />
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
