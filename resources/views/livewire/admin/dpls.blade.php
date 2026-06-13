<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="user-circle" color="green" :label="__('Total DPL')" :value="$stats['total']" />
        <x-stat-card icon="user-group" color="blue" :label="__('Membimbing')" :value="$stats['assigned']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Menugaskan')" :value="$stats['unassigned']" />
    </div>

    {{-- DPL Table --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Dosen Pembimbing Lapangan') }}</flux:heading>
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIP...') }}" size="sm" class="w-64" />
        </div>

        <flux:separator />

        @if($dpls->isEmpty())
            <x-empty-state icon="user-circle" :heading="__('Tidak Ada Data DPL')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama DPL') }}</flux:table.column>
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
                                @if($dpl->group)
                                    <flux:badge size="sm" color="blue" inset="top bottom">{{ $dpl->group->name }}</flux:badge>
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
