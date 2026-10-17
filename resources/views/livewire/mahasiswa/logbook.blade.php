<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="book-open" color="blue" :label="__('Total Entri')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu')" :value="$stats['pending']" />
        <x-stat-card icon="calculator" color="purple" :label="__('Total Jam Kerja')" :value="$stats['totalHours']" />
    </div>

    {{-- Filter --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-tabs>
            <x-tab
                :selected="$selectedWeek === 'all'"
                wire:click="$set('selectedWeek', 'all')"
            >
                Semua Minggu
            </x-tab>

            @foreach($allWeeks as $week)
                <x-tab :selected="$selectedWeek === (string)$week" wire:click="$set('selectedWeek', '{{ $week }}')">
                    Minggu {{ $week }}
                </x-tab>
            @endforeach
        </x-tabs>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
            @if($logs->isNotEmpty())
                <flux:button variant="ghost" size="sm" icon="printer" :href="route('logbook.pdf', $student)" target="_blank" class="w-full sm:w-auto">
                    {{ __('Cetak Logbook') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Logs Timeline --}}
    @if(empty($logsGroupedByWeek))
        <flux:card>
            <x-empty-state icon="book-open" :heading="__('Belum Ada Catatan')" :description="__('Catatan aktivitas harian tersedia selama periode kelompok KKN Anda.')" />
        </flux:card>
    @else
        <div class="flex flex-col gap-6">
            @foreach($logsGroupedByWeek as $weekNumber => $weekLogs)
                <x-accordion 
                    heading="Minggu Ke-{{ $weekNumber }}" 
                    description="{{ count($weekLogs) }} hari tercatat"
                    :defaultOpen="$selectedWeek === (string)$weekNumber || $selectedWeek === 'all'"
                >
                    <div class="flex flex-col gap-4">
                        @foreach($weekLogs as $dayData)
                            @php 
                                $log = $dayData['log']; 
                                $dateObj = $dayData['date'];
                                $dayNum = $dayData['day_number'];
                                $dateStr = $dayData['dateStr'];
                            @endphp

                            @if($log)
                                <flux:card class="flex flex-col gap-4">
                                    {{-- Card Header --}}
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-start flex-col sm:flex-row sm:items-center gap-3">
                                            <flux:badge size="sm" color="zinc">
                                                {{ __('Hari ke: ') }}{{ $dayNum }}
                                            </flux:badge>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <flux:text variant="strong">
                                                    {{ $dateObj->translatedFormat('d M Y') }}
                                                </flux:text>
                                                <flux:separator vertical variant="subtle"/>
                                                <flux:text variant="subtle">
                                                    {{ $dateObj->translatedFormat('l') }}
                                                </flux:text>
                                            </div>
                                        </div>
                                        <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge> 
                                    </div>

                                    {{-- Activity Table --}}
                                    <flux:table>
                                        <flux:table.columns>
                                            <flux:table.column class="w-12">{{ __('No') }}</flux:table.column>
                                            <flux:table.column class="w-32">{{ __('Waktu') }}</flux:table.column>
                                            <flux:table.column>{{ __('Kegiatan') }}</flux:table.column>
                                        </flux:table.columns>
                                        <flux:table.rows>
                                            @forelse($log->activities as $index => $activity)
                                                <flux:table.row :key="$activity->id ?? $index">
                                                    <flux:table.cell variant="strong">{{ $index + 1 }}</flux:table.cell>
                                                    <flux:table.cell class="whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                                    </flux:table.cell>
                                                    <flux:table.cell>
                                                        {{ $activity->activity_description }}
                                                    </flux:table.cell>
                                                </flux:table.row>
                                            @empty
                                                <flux:table.row>
                                                    <flux:table.cell colspan="3" class="text-center text-zinc-500">{{ __('Belum ada kegiatan yang dicatat.') }}</flux:table.cell>
                                                </flux:table.row>
                                            @endforelse
                                        </flux:table.rows>
                                    </flux:table>

                                    {{-- Important Notes --}}
                                    @if($log->important_notes || $log->image_path)
                                        <div class="mt-2 rounded-lg bg-zinc-50 dark:bg-white/5 p-4">
                                            <flux:text variant="strong" class="mb-2 text-sm">{{ __('Catatan Penting Harian:') }}</flux:text>
                                            @if($log->important_notes)
                                                <flux:text class="text-sm italic text-zinc-600 dark:text-zinc-300">
                                                    "{{ $log->important_notes }}"
                                                </flux:text>
                                            @endif
                                            @if($log->image_path && $log->image_url)
                                                <div class="mt-3">
                                                    <img src="{{ $log->image_url }}" alt="Catatan gambar" class="max-h-48 rounded-lg border border-zinc-200 dark:border-white/10 object-cover cursor-pointer hover:opacity-80 transition" x-on:click="$flux.modal('image-preview-{{ $log->id }}').show()" />
                                                    
                                                    <x-image-preview-modal name="image-preview-{{ $log->id }}" url="{{ $log->image_url }}" />
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewLog({{ $log->id }})">{{ __('Lihat') }}</flux:button>
                                        @if($log->status === \App\Enums\LogStatus::Pending || $log->status === \App\Enums\LogStatus::Draft)
                                            <flux:button variant="ghost" size="sm" icon="pencil-square" href="{{ route('logbook.form', ['logId' => $log->id]) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                                        @endif
                                        @if($log->status === \App\Enums\LogStatus::Draft)
                                            <flux:button variant="primary" size="sm" icon="paper-airplane" wire:click="submitLog({{ $log->id }})">{{ __('Ajukan') }}</flux:button>
                                        @endif
                                    </div>
                                </flux:card>
                            @else
                                <flux:card class="flex flex-col gap-4 border-dashed border-zinc-300 dark:border-zinc-700 bg-transparent">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-center gap-3">
                                            <flux:badge size="sm" color="zinc" class="font-medium whitespace-nowrap">
                                                {{ __('Hari ke: ') }}{{ $dayNum }}
                                            </flux:badge>
                                            <flux:text variant="strong" class="text-sm text-zinc-500">
                                                {{ $dateObj->translatedFormat('d M Y') }} · <span class="font-normal">{{ $dateObj->translatedFormat('l') }}</span>
                                            </flux:text>
                                        </div>
                                        <flux:button size="sm" variant="filled" href="{{ route('logbook.form', ['date' => $dateStr]) }}" wire:navigate>
                                            {{ __('Isi Logbook') }}
                                        </flux:button>
                                    </div>
                                </flux:card>
                            @endif
                        @endforeach
                    </div>
                </x-accordion>
            @endforeach
        </div>
    @endif
    
    {{-- View Modal --}}
    <flux:modal name="log-view-modal" scroll="body" class="max-w-2xl w-full">
        @if($viewLogData)
            <div class="space-y-6">
                {{-- Header --}}
                <div>
                    <flux:heading size="lg">{{ __('Logbook Hari Ke-:day', ['day' => $viewLogData->day_number]) }}</flux:heading>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <flux:text variant="subtle" class="text-sm">{{ $viewLogData->date->translatedFormat('l, d M Y') }}</flux:text>
                        <flux:separator vertical variant="subtle" class="h-4" />
                        <flux:badge size="sm" :color="$viewLogData->status->color()">{{ $viewLogData->status->label() }}</flux:badge>
                    </div>
                </div>

                <flux:separator variant="subtle" />

                {{-- Sections --}}
                <div class="space-y-6">
                    {{-- Activities --}}
                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Kegiatan Harian') }}</flux:heading>
                        <div class="space-y-2">
                            @foreach($viewLogData->activities as $index => $activity)
                                <flux:card variant="subtle" class="flex gap-4 p-3">
                                    <div class="text-sm font-medium text-zinc-500 whitespace-nowrap min-w-[90px]">
                                        {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                    </div>
                                    <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                        {{ $activity->activity_description }}
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    </section>

                    {{-- Notes & Image --}}
                    @if($viewLogData->important_notes || ($viewLogData->image_path && $viewLogData->image_url))
                        <flux:separator variant="subtle" />

                        <section class="space-y-3">
                            <flux:heading size="sm">{{ __('Catatan & Dokumentasi') }}</flux:heading>
                            @if($viewLogData->important_notes)
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Catatan Penting') }}</flux:text>
                                    <flux:text class="whitespace-pre-wrap">{{ $viewLogData->important_notes }}</flux:text>
                                </div>
                            @endif
                            @if($viewLogData->image_path && $viewLogData->image_url)
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Foto Dokumentasi') }}</flux:text>
                                    <flux:card variant="soft" class="overflow-hidden p-0 max-w-md">
                                        <img
                                            src="{{ $viewLogData->image_url }}"
                                            alt="{{ __('Dokumentasi Kegiatan') }}"
                                            class="mx-auto block max-h-80 max-w-full object-contain cursor-pointer hover:opacity-85 transition"
                                            x-on:click="$flux.modal('image-preview-view').show()"
                                        />
                                        <x-image-preview-modal name="image-preview-view" url="{{ $viewLogData->image_url }}" />
                                    </flux:card>
                                </div>
                            @endif
                        </section>
                    @endif
                </div>
                
                {{-- Actions --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                    <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                        <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
