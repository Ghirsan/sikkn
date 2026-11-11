<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Heading --}}
    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
    </div>

    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 sm:grid-cols-2 md:grid-cols-4">
        <x-stat-card icon="light-bulb" color="blue" :label="__('Total Usulan Program')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('LRK Disetujui')" :value="$stats['lrk_approved']" />
        <x-stat-card icon="document-check" color="purple" :label="__('LPK Disetujui')" :value="$stats['lpk_approved']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Review')" :value="$stats['pending']" />
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari judul, nama, NIM...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" size="sm" class="w-full sm:w-48">
                <flux:select.option value="">{{ __('Semua Program Studi') }}</flux:select.option>
                @foreach($studyPrograms as $prodi)
                    <flux:select.option value="{{ $prodi->id }}">{{ $prodi->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @endif
        </div>
    </div>

    <flux:card>
        @if($participants->isEmpty())
            <x-empty-state icon="light-bulb" :heading="__('Tidak Ada Data Program Kerja')" />
        @else
            <flux:table :paginate="$participants">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Kerja') }}</flux:table.column>
                    <flux:table.column>{{ __('Tipe') }}</flux:table.column>
                    <flux:table.column>{{ __('Status LRK') }}</flux:table.column>
                    <flux:table.column>{{ __('Status LPK') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($participants as $participant)
                        <flux:table.row :key="$participant->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $participant->student->name }}</div>
                                <div class="text-xs text-neutral-500">{{ $participant->student->nim }} &middot; {{ $participant->student->prodi }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="line-clamp-2 max-w-sm" title="{{ $participant->program->title }}">
                                    {{ $participant->program->title }}
                                </div>
                                <div class="mt-1 text-xs text-neutral-500">Kelompok {{ $participant->program->group->name ?? '-' }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $participant->program->programType?->code === 'monodisiplin' ? 'blue' : 'amber' }}">
                                    {{ $participant->program->programType?->name ?? '-' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $participant->status->color() }}">
                                    {{ $participant->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $participant->lpk_status->color() }}">
                                    {{ $participant->lpk_status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>

