<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Menunggu Persetujuan') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['pending'] }}</flux:text>
                </div>
            </div>
        </div>
        <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Disetujui') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['approved'] }}</flux:text>
                </div>
            </div>
        </div>
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
    </div>

    {{-- Logs --}}
    <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Catatan Harian Mahasiswa') }}</flux:heading>
        </div>
        @if($logs->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="book-open" class="mx-auto size-12 text-neutral-300" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Logbook') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa & Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Aktivitas') }}</flux:table.column>
                    <flux:table.column>{{ __('Status & Durasi') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                <span class="font-medium">{{ $log->student->name }}</span>
                                <div class="text-xs text-neutral-500">{{ $log->date->translatedFormat('l, d F Y') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="line-clamp-2 text-sm text-neutral-600">{{ $log->activity_description }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                                <div class="mt-1 text-xs text-neutral-500">{{ $log->duration_minutes }} {{ __('menit') }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($log->status->value === 'pending')
                                    <flux:button wire:click="approveDailyLog({{ $log->id }})" size="sm" variant="filled" icon="check" inset="top bottom">{{ __('Setujui') }}</flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</div>
