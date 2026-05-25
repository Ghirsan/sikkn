<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Lifecycle Summary --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-zinc-700">
                    <flux:icon name="pencil-square" class="size-5 text-neutral-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Draft') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['draft'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Review') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['submitted'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Disetujui') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['approved'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="document-check" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Tim Siap PDF') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['ready_pdf'] }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Filter Bar --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok...') }}" size="sm" class="w-72" />
    </div>

    {{-- Documents Table --}}
    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Status Dokumen Tim (LRK/LPK)') }}</flux:heading>
        </div>

        @if($groupData->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="document-text" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Tidak Ada Data Kelompok') }}</flux:heading>
            </div>
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
                            <flux:table.cell>{{ $data->group->period->name }}</flux:table.cell>
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
