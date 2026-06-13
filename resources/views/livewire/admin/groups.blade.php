<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="user-group" color="purple" :label="__('Total Kelompok')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ada DPL')" :value="$stats['with_dpl']" />
        <x-stat-card icon="exclamation-triangle" color="amber" :label="__('Belum Ada DPL')" :value="$stats['without_dpl']" />
    </div>

    {{-- Groups Table --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Daftar Kelompok') }}</flux:heading>
        <div class="flex items-center gap-3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok atau desa...') }}" size="sm" class="w-64" />
            <flux:button variant="filled" size="sm" icon="plus">{{ __('Buat Kelompok') }}</flux:button>
        </div>
    </div>

    <flux:card>

        @if($groups->isEmpty())
            <x-empty-state icon="user-group" :heading="__('Tidak Ada Data Kelompok')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                    <flux:table.column>{{ __('Periode') }}</flux:table.column>
                    <flux:table.column>{{ __('Lokasi (Desa, Kec)') }}</flux:table.column>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('DPL') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($groups as $group)
                        <flux:table.row :key="$group->id">
                            <flux:table.cell variant="strong">{{ $group->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="blue">{{ $group->type->value }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc">Semester {{ $group->period->semester->value }} {{ $group->period->year }}</flux:badge>
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
                                    @else
                                        {{ __('Belum Ditugaskan') }}
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
