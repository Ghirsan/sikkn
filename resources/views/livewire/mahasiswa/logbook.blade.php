<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="book-open" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Total Entri') }}</flux:text>
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
                    <flux:text class="text-sm text-neutral-500">{{ __('Disetujui DPL') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['approved'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Menunggu') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['pending'] }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Logs Table --}}
    <flux:card class="!p-0">
        <div class="flex items-center justify-between border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Catatan Harian') }}</flux:heading>
            @if($logs->isNotEmpty())
                <flux:button variant="filled" size="sm" icon="printer" :href="route('logbook.pdf', $student)" target="_blank">
                    {{ __('Cetak Logbook') }}
                </flux:button>
            @endif
        </div>
        @if($logs->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="book-open" class="mx-auto size-12 text-neutral-300" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Catatan') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500">{{ __('Mulai catat aktivitas harian KKN Anda.') }}</flux:text>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Kegiatan') }}</flux:table.column>
                    <flux:table.column>{{ __('Catatan Penting') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell variant="strong" class="whitespace-nowrap align-top">
                                {{ $log->date->translatedFormat('d M Y') }}
                                <br>
                                <span class="text-xs font-normal text-neutral-400">{{ $log->date->translatedFormat('l') }}</span>
                            </flux:table.cell>
                            <flux:table.cell class="align-top">
                                <div class="space-y-1">
                                    @foreach($log->activities as $activity)
                                        <div class="flex items-start gap-2 text-sm">
                                            <span class="whitespace-nowrap text-xs text-neutral-400">
                                                {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                            </span>
                                            <span class="text-neutral-600 dark:text-neutral-300">{{ $activity->activity_description }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="align-top">
                                <span class="line-clamp-2 text-sm text-neutral-500">{{ $log->important_notes ?? '-' }}</span>
                            </flux:table.cell>
                            <flux:table.cell class="align-top">
                                <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
