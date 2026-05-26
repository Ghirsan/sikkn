<div class="flex h-full w-full flex-1 flex-col gap-6">
    @if($group)
        {{-- Location Card --}}
        <flux:card class="border-2 border-dashed border-green-300 bg-green-50/50 dark:border-green-700 dark:bg-green-900/10">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="map-pin" class="size-5 text-green-600" />
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-green-600">{{ __('Lokasi Penugasan KKN') }}</flux:text>
                    <flux:heading size="lg">{{ $group->village }}, {{ $group->district }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-500">{{ $group->regency }}, {{ $group->province }}</flux:text>
                    @if($period)
                        <flux:text class="mt-1 text-xs text-neutral-400">{{ __('Periode:') }} {{ $period->name }} {{ $period->year }} ({{ $period->start_date->format('d M') }} — {{ $period->end_date->format('d M Y') }})</flux:text>
                    @endif
                </div>
            </div>
        </flux:card>

        {{-- DPL Info --}}
        @if($dpls->isNotEmpty())
            <flux:card>
                <flux:text class="text-xs font-medium uppercase tracking-wider text-neutral-500">{{ __('Dosen Pembimbing Lapangan') }}</flux:text>
                <div class="mt-3 space-y-3">
                    @foreach($dpls as $dplUser)
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                                <flux:icon name="user-circle" class="size-5 text-blue-500" />
                            </div>
                            <div>
                                <flux:heading size="sm">{{ $dplUser->name }}</flux:heading>
                                @if($dplUser->nip)
                                    <flux:text class="text-sm text-neutral-500">NIP: {{ $dplUser->nip }}</flux:text>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- Team Members --}}
        <flux:card class="!p-0">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Anggota Kelompok') }} — {{ $group->name }}</flux:heading>
                    <flux:badge color="zinc">{{ $members->count() }} {{ __('anggota') }}</flux:badge>
                </div>
            </div>
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
            <flux:icon name="user-group" class="mx-auto size-12 text-neutral-300" />
            <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Kelompok') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-neutral-500">{{ __('Anda belum tergabung dalam kelompok KKN.') }}</flux:text>
        </flux:card>
    @endif
</div>
