<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" placeholder="{{ __('Semua Program Studi') }}" size="sm" class="w-48">
                <option value="">{{ __('Semua Program Studi') }}</option>
                @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                @endforeach
            </flux:select>
            @endif

            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari judul, nama, NIM...') }}" size="sm" class="w-64" />
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

