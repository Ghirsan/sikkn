<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Heading --}}
    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Penilaian Mahasiswa') }}</flux:heading>
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" placeholder="{{ __('Semua Program Studi') }}" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Program Studi') }}</option>
                @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                @endforeach
            </flux:select>
            @endif

            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:button wire:click="exportSiapExcel" icon="document-arrow-down" variant="primary" size="sm" class="w-full sm:w-auto">
                {{ __('Export Nilai SIAP') }}
            </flux:button>
            @endif
        </div>
    </div>

    <flux:card>
        @if($students->isEmpty())
            <x-empty-state icon="clipboard-document-check" :heading="__('Tidak Ada Data Mahasiswa')" />
        @else
            <flux:table :paginate="$students">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Nilai Akhir') }}</flux:table.column>
                    <flux:table.column>{{ __('Huruf') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $student->name }}</div>
                                <div class="text-xs text-neutral-500">{{ $student->nim }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                {{ $student->prodi }}
                                <div class="text-xs text-neutral-500">{{ $student->fakultas }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->group)
                                    <flux:badge size="sm" color="green" inset="top bottom">{{ $student->group->name }}</flux:badge>
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->grade)
                                    <span class="font-bold">{{ number_format($student->grade->final_grade, 1) }}</span>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->grade && $student->grade->grade_letter)
                                    <flux:badge size="sm" color="blue">{{ $student->grade->grade_letter }}</flux:badge>
                                @else
                                    <span class="text-neutral-400">-</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>

