<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Persetujuan')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="book-open" color="blue" :label="__('Total Entri')" :value="$stats['total']" />
    </div>

    {{-- Logs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Catatan Harian Mahasiswa') }}</flux:heading>
    </div>

    <flux:card>

        @if($logs->isEmpty())
            <x-empty-state icon="book-open" :heading="__('Belum Ada Logbook')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa & Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Kegiatan') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
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
                                <div class="space-y-1">
                                    @foreach($log->activities as $activity)
                                        <div class="flex items-start gap-2 text-sm">
                                            <span class="whitespace-nowrap text-xs text-neutral-400">
                                                {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                            </span>
                                            <span class="line-clamp-1 text-neutral-600 dark:text-neutral-300">{{ $activity->activity_description }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
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
    </flux:card>
</div>
