<div class="flex h-full w-full flex-1 flex-col gap-6">
    @if($allGroups->count() > 1)
        <div class="flex items-center justify-end">
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        </div>
    @endif
    @if(!$periodCompleted)
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('Penilaian Belum Dibuka') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Formulir penilaian akan terbuka setelah masa pelaksanaan KKN berakhir.') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Dinilai')" :value="$stats['graded']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Dinilai')" :value="$stats['ungraded']" />
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
        <div class="flex flex-wrap items-center gap-2">
            <flux:button wire:click="toggleImportPanel" icon="arrow-up-tray" variant="{{ $showImportPanel ? 'filled' : 'subtle' }}" size="sm" :disabled="!$periodCompleted">
                {{ __('Import Nilai Excel') }}
            </flux:button>
            <flux:button wire:click="exportExcel" icon="document-arrow-down" variant="primary" size="sm">
                {{ __('Export Format Excel') }}
            </flux:button>
        </div>
    </div>

    @if($showImportPanel)
        <flux:card class="border-2 border-dashed border-neutral-300 dark:border-neutral-700 bg-neutral-50/50 dark:bg-neutral-900/50">
            <div class="flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="base">{{ __('Import Nilai Mahasiswa (Excel)') }}</flux:heading>
                        <flux:text class="text-sm mt-1">
                            {{ __('Unggah file .xlsx yang telah diisi 8 aspek penilaian (0-100). Sistem akan memproses dan menghitung komponen SIAP serta nilai akhir secara otomatis.') }}
                        </flux:text>
                    </div>
                    <flux:button wire:click="toggleImportPanel" icon="x-mark" variant="ghost" size="sm" aria-label="{{ __('Tutup') }}" />
                </div>

                @if(!empty($importErrors))
                    <flux:callout variant="danger" icon="exclamation-circle">
                        <flux:callout.heading>{{ __('Terdapat kesalahan pada file:') }}</flux:callout.heading>
                        <ul class="mt-2 list-disc pl-5 space-y-1 text-sm max-h-48 overflow-y-auto">
                            @foreach($importErrors as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </flux:callout>
                @endif

                <form wire:submit="importExcel" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <flux:field>
                            <flux:label>{{ __('Pilih File Excel (.xlsx)') }}</flux:label>
                            <input
                                type="file"
                                wire:model="gradeFile"
                                accept=".xlsx"
                                class="block w-full text-sm text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-neutral-200 dark:file:bg-neutral-800 file:text-neutral-700 dark:file:text-neutral-200 hover:file:bg-neutral-300 dark:hover:file:bg-neutral-700 cursor-pointer border border-neutral-300 dark:border-neutral-700 rounded-lg p-1 bg-white dark:bg-neutral-800"
                            />
                            <flux:error name="gradeFile" />
                        </flux:field>
                    </div>

                    <div class="flex items-center gap-2">
                        <flux:button type="submit" variant="primary" icon="arrow-up-tray" wire:loading.attr="disabled" wire:target="importExcel, gradeFile">
                            <span wire:loading.remove wire:target="importExcel">{{ __('Unggah & Proses') }}</span>
                            <span wire:loading wire:target="importExcel">{{ __('Memproses...') }}</span>
                        </flux:button>
                        <flux:button type="button" wire:click="toggleImportPanel" variant="ghost">
                            {{ __('Batal') }}
                        </flux:button>
                    </div>
                </form>

                <div wire:loading wire:target="gradeFile" class="text-xs text-neutral-500">
                    <flux:icon name="arrow-path" class="inline w-3 h-3 animate-spin mr-1" />
                    {{ __('Mengunggah file ke server...') }}
                </div>
            </div>
        </flux:card>
    @endif

    <flux:card>

        @if($students->isEmpty())
            <x-empty-state icon="academic-cap" :heading="__('Belum Ada Mahasiswa')" />
        @else
            <flux:table>
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
                                <flux:button size="sm" variant="ghost" icon="pencil-square" href="{{ route('dpl.grades.form', $student->id) }}" wire:navigate :disabled="!$periodCompleted">
                                    {{ __('Nilai Mahasiswa') }}
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
