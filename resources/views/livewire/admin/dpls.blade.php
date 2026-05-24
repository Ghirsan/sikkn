<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="user-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total DPL') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['total'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="user-group" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Membimbing') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['assigned'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Belum Menugaskan') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['unassigned'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- DPL Table --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Dosen Pembimbing Lapangan') }}</flux:heading>
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIP...') }}" size="sm" class="w-64" />
            </div>
        </div>

        @if($dpls->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="user-circle" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Tidak Ada Data DPL') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama DPL') }}</flux:table.column>
                    <flux:table.column>{{ __('NIP') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Beban Bimbingan') }}</flux:table.column>
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
                                <flux:badge size="sm" :color="$dpl->supervised_groups_count > 0 ? 'blue' : 'zinc'" inset="top bottom">
                                    {{ $dpl->supervised_groups_count }} {{ __('Kelompok') }}
                                </flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
