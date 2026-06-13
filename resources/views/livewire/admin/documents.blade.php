<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Lifecycle Summary --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="pencil-square" color="neutral" :label="__('Program Draft')" :value="$stats['draft']" />
        <x-stat-card icon="clock" color="amber" :label="__('Program Review')" :value="$stats['submitted']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Program Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="document-check" color="blue" :label="__('Tim Siap PDF')" :value="$stats['ready_pdf']" />
    </div>

    {{-- Filter Bar --}}
    <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok...') }}" size="sm" class="w-72" />

    {{-- Documents Table --}}
    <flux:card>
        <flux:heading size="lg">{{ __('Status Dokumen Tim (LRK/LPK)') }}</flux:heading>

        <flux:separator />

        @if($groupData->isEmpty())
            <x-empty-state icon="document-text" :heading="__('Tidak Ada Data Kelompok')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Lokasi') }}</flux:table.column>
                    <flux:table.column>{{ __('Periode') }}</flux:table.column>
                    <flux:table.column>{{ __('Status Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($groupData as $data)
                        <flux:table.row :key="$data->group->id">
                            <flux:table.cell variant="strong">{{ $data->group->name }}</flux:table.cell>
                            <flux:table.cell>{{ $data->group->village }}</flux:table.cell>
                            <flux:table.cell>Semester {{ $data->group->period->semester->value }} {{ $data->group->period->year }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$data->allApproved ? 'green' : 'zinc'" inset="top bottom">
                                    {{ $data->allApproved ? __('Siap PDF') : $data->approvedCount.'/'.$data->totalPrograms.' approved' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($data->allApproved)
                                    <flux:button size="sm" variant="ghost" icon="arrow-down-tray" inset="top bottom">{{ __('Unduh LRK') }}</flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
