<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="book-open" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Total Entri') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['total'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Disetujui DPL') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['approved'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Menunggu') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['pending'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Catatan Harian') }}</flux:heading>
        </div>
        @if($logs->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="book-open" class="mx-auto size-12 text-neutral-300" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Catatan') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Aktivitas') }}</flux:table.column>
                    <flux:table.column>{{ __('Durasi') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell variant="strong" class="whitespace-nowrap">
                                {{ $log->date->translatedFormat('d M Y') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="line-clamp-2 text-sm text-neutral-600">{{ $log->activity_description }}</span>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                {{ $log->duration_minutes }} {{ __('menit') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
