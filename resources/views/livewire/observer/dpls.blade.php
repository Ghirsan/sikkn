<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Daftar Dosen KKN (DPL)') }}</flux:heading>
        <div class="flex flex-wrap items-center gap-3">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" placeholder="{{ __('Semua Program Studi') }}" size="sm" class="w-48">
                <option value="">{{ __('Semua Program Studi') }}</option>
                @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                @endforeach
            </flux:select>
            @endif

            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIP...') }}" size="sm" class="w-64" />
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
                            <flux:table.cell>
                                {{ $dpl->prodi }}
                                <div class="text-xs text-neutral-500">{{ $dpl->fakultas }}</div>
                            </flux:table.cell>
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
</div>

