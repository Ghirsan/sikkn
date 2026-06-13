<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="book-open" color="blue" :label="__('Total Entri')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu')" :value="$stats['pending']" />
    </div>

    {{-- Logs Table --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Catatan Harian') }}</flux:heading>
            <div class="flex items-center gap-3">
                @if($logs->isNotEmpty())
                    <flux:button variant="ghost" size="sm" icon="printer" :href="route('logbook.pdf', $student)" target="_blank">
                        {{ __('Cetak Logbook') }}
                    </flux:button>
                @endif
                <flux:button variant="filled" size="sm" icon="plus">{{ __('Tambah Entri') }}</flux:button>
            </div>
        </div>

        <flux:separator />

        @if($logs->isEmpty())
            <x-empty-state icon="book-open" :heading="__('Belum Ada Catatan')" :description="__('Mulai catat aktivitas harian KKN Anda.')" />
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
