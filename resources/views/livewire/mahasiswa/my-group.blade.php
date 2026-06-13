<div class="flex h-full w-full flex-1 flex-col gap-6">
    @if($group)
        {{-- Location Card --}}
        <flux:callout color="green" icon="map-pin">
            <flux:callout.heading>{{ $group->village }}, {{ $group->district }}</flux:callout.heading>
            <flux:callout.text>
                {{ $group->regency }}, {{ $group->province }}
                @if($period)
                    <br>{{ __('Periode:') }} {{ $period->name }} {{ $period->year }} ({{ $period->start_date->format('d M') }} — {{ $period->end_date->format('d M Y') }})
                @endif
            </flux:callout.text>
        </flux:callout>

        {{-- DPL Info --}}
        @if($dpls->isNotEmpty())
            <flux:card>
                <flux:text variant="subtle" class="text-xs uppercase tracking-wider">{{ __('Dosen Pembimbing Lapangan') }}</flux:text>
                <div class="mt-3 space-y-3">
                    @foreach($dpls as $dplUser)
                        <div class="flex items-center gap-3">
                            <flux:icon.user-circle variant="mini" class="text-blue-500" />
                            <div>
                                <flux:heading size="sm">{{ $dplUser->name }}</flux:heading>
                                @if($dplUser->nip)
                                    <flux:text>NIP: {{ $dplUser->nip }}</flux:text>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- Team Members --}}
        <flux:card>
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Anggota Kelompok') }} — {{ $group->name }}</flux:heading>
                <flux:badge color="zinc">{{ $members->count() }} {{ __('anggota') }}</flux:badge>
            </div>

            <flux:separator />

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($members as $member)
                        <flux:table.row :key="$member->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$member->name" :initials="$member->initials()" size="sm" />
                                <span class="font-medium">{{ $member->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $member->nim }}</flux:table.cell>
                            <flux:table.cell>{{ $member->prodi }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @else
        <flux:card class="text-center">
            <x-empty-state icon="user-group" :heading="__('Belum Ada Kelompok')" :description="__('Anda belum tergabung dalam kelompok KKN.')" />
        </flux:card>
    @endif
</div>
