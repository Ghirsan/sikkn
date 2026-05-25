<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                    <flux:icon name="user-group" class="size-5 text-purple-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Kelompok') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['total'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Sudah Ada DPL') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['with_dpl'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="exclamation-triangle" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Belum Ada DPL') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['without_dpl'] }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Groups Table --}}
    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Daftar Kelompok') }}</flux:heading>
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok atau desa...') }}" size="sm" class="w-64" />
            </div>
        </div>

        @if($groups->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="user-group" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Tidak Ada Data Kelompok') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Kelompok') }}</flux:table.column>
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
                                <flux:badge size="sm" color="zinc">{{ $group->period->name }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $group->village }}, {{ $group->district }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon name="academic-cap" class="size-4 text-neutral-400" />
                                    {{ $group->students_count }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon name="user" class="size-4 text-neutral-400" />
                                    {{ $group->dpl ? $group->dpl->name : __('Belum Ditugaskan') }}
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
