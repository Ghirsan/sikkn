<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="book-open" color="blue" :label="__('Total Entri')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu')" :value="$stats['pending']" />
        <x-stat-card icon="calculator" color="purple" :label="__('Total Jam Kerja')" :value="$stats['totalHours']" />
    </div>

    {{-- Header & Actions --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <x-tabs>
            <x-tab :selected="$selectedWeek === 'all'" wire:click="$set('selectedWeek', 'all')">
                Semua Minggu
            </x-tab>
            @foreach($allWeeks as $week)
                <x-tab :selected="$selectedWeek === (string)$week" wire:click="$set('selectedWeek', '{{ $week }}')">
                    Minggu {{ $week }}
                </x-tab>
            @endforeach
        </x-tabs>
        <div class="flex items-center gap-3">
            @if($logs->isNotEmpty())
                <flux:button variant="ghost" size="sm" icon="printer" :href="route('logbook.pdf', $student)" target="_blank">
                    {{ __('Cetak Logbook') }}
                </flux:button>
            @endif
            <flux:button variant="filled" size="sm" icon="plus" href="{{ route('logbook.form') }}" wire:navigate>
                {{ __('Tambah Entri') }}
            </flux:button>
        </div>
    </div>

    {{-- Logs Timeline --}}
    @if(empty($logsGroupedByWeek))
        <flux:card>
            <x-empty-state icon="book-open" :heading="__('Belum Ada Catatan')" :description="__('Mulai catat aktivitas harian KKN Anda.')" />
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
                        @foreach($weekLogs as $log)
                            <flux:card class="flex flex-col gap-4">
                                {{-- Card Header --}}
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-800/10 dark:border-white/10 pb-4">
                                    <div class="flex items-center gap-3">
                                        <flux:badge size="sm" color="zinc" class="font-medium whitespace-nowrap">
                                            {{ __('Hari ke: ') }}{{ $log->day_number }}
                                        </flux:badge>
                                        <flux:text variant="strong" class="text-sm">
                                            {{ $log->date->translatedFormat('d M Y') }} · <span class="text-neutral-500 font-normal">{{ $log->date->translatedFormat('l') }}</span>
                                        </flux:text>
                                    </div>
                                    <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                                </div>

                                {{-- Activity Table --}}
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-b border-zinc-800/10 dark:border-white/10 text-left">
                                                <th class="pb-2 font-medium text-zinc-500 dark:text-zinc-400 w-12">{{ __('No') }}</th>
                                                <th class="pb-2 font-medium text-zinc-500 dark:text-zinc-400 w-32">{{ __('Waktu') }}</th>
                                                <th class="pb-2 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kegiatan') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-800/10 dark:divide-white/10">
                                            @foreach($log->activities as $index => $activity)
                                                <tr>
                                                    <td class="py-2 text-zinc-500 dark:text-zinc-400 align-top">{{ $index + 1 }}</td>
                                                    <td class="py-2 text-zinc-500 dark:text-zinc-400 align-top whitespace-nowrap">
                                                        {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                                    </td>
                                                    <td class="py-2 text-zinc-800 dark:text-zinc-200 align-top">
                                                        {{ $activity->activity_description }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            @if($log->activities->isEmpty())
                                                <tr>
                                                    <td colspan="3" class="py-4 text-center text-zinc-500">Belum ada kegiatan yang dicatat.</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Important Notes --}}
                                @if($log->important_notes || $log->image_path)
                                    <div class="mt-2 rounded-lg bg-zinc-50 dark:bg-white/5 p-4">
                                        <flux:text variant="strong" class="mb-2 text-sm">{{ __('Catatan Penting Harian:') }}</flux:text>
                                        @if($log->important_notes)
                                            <flux:text class="text-sm italic text-zinc-600 dark:text-zinc-300">
                                                "{{ $log->important_notes }}"
                                            </flux:text>
                                        @endif
                                        @if($log->image_path)
                                            <div class="mt-3">
                                                <img src="{{ asset('storage/' . $log->image_path) }}" alt="Catatan gambar" class="max-h-48 rounded-lg border border-zinc-200 dark:border-white/10 object-cover cursor-pointer hover:opacity-80 transition" />
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                {{-- Actions --}}
                                <div class="flex items-center justify-end gap-2 pt-2">
                                    <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewLog({{ $log->id }})">{{ __('Lihat') }}</flux:button>
                                    @if($log->status === \App\Enums\LogStatus::Pending)
                                        <flux:button variant="ghost" size="sm" icon="pencil-square" href="{{ route('logbook.form', ['logId' => $log->id]) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                                    @endif
                                </div>
                            </flux:card>
                        @endforeach
                    </div>
                </x-accordion>
            @endforeach
        </div>
    @endif
    
    {{-- View Modal --}}
    <flux:modal name="log-view-modal" class="md:w-3/4 lg:w-[50rem]">
        @if($viewLogData)
            <div class="space-y-8 p-4">
                <div class="text-center space-y-2 border-b border-zinc-200 dark:border-white/10 pb-6">
                    <flux:heading size="xl" class="font-bold tracking-tight uppercase">{{ __('Buku Catatan Harian') }}</flux:heading>
                    <flux:heading size="lg" class="font-bold tracking-tight uppercase">{{ __('Kuliah Kerja Nyata') }}</flux:heading>
                    <flux:heading size="lg" class="font-bold tracking-tight uppercase">{{ __('Universitas Diponegoro') }}</flux:heading>
                    
                    <div class="mt-4 flex flex-wrap justify-center items-center gap-x-6 gap-y-2 text-sm">
                        <span><span class="font-medium">Hari ke:</span> {{ $viewLogData->day_number }}</span>
                        <span><span class="font-medium">Hari:</span> {{ $viewLogData->date->translatedFormat('l') }}</span>
                        <span><span class="font-medium">Tanggal:</span> {{ $viewLogData->date->translatedFormat('d M Y') }}</span>
                        <span><span class="font-medium">Status:</span> <flux:badge size="sm" :color="$viewLogData->status->color()" class="ml-1">{{ $viewLogData->status->label() }}</flux:badge></span>
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:heading size="md" class="font-bold">1. Jadwal Kegiatan</flux:heading>
                    <div class="overflow-x-auto border border-zinc-200 dark:border-white/10 rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-zinc-50 dark:bg-white/5">
                                <tr class="text-left border-b border-zinc-200 dark:border-white/10">
                                    <th class="py-3 px-4 font-medium w-16">No</th>
                                    <th class="py-3 px-4 font-medium w-40">Waktu</th>
                                    <th class="py-3 px-4 font-medium">Kegiatan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                                @foreach($viewLogData->activities as $index => $activity)
                                    <tr>
                                        <td class="py-3 px-4 align-top">{{ $index + 1 }}</td>
                                        <td class="py-3 px-4 align-top whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                        </td>
                                        <td class="py-3 px-4 align-top">{{ $activity->activity_description }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-4">
                    <flux:heading size="md" class="font-bold">2. Catatan Penting Harian</flux:heading>
                    <div class="p-4 border border-zinc-200 dark:border-white/10 rounded-lg min-h-[100px] text-sm">
                        @if($viewLogData->important_notes)
                            {!! nl2br(e($viewLogData->important_notes)) !!}
                        @else
                            <span class="text-zinc-400 italic">Tidak ada catatan penting.</span>
                        @endif
                    </div>
                    @if($viewLogData->image_path)
                        <div class="mt-2">
                            <flux:text variant="strong" class="mb-2 text-sm">{{ __('Gambar Pendukung:') }}</flux:text>
                            <img src="{{ asset('storage/' . $viewLogData->image_path) }}" alt="Catatan gambar" class="max-h-80 rounded-lg border border-zinc-200 dark:border-white/10 object-contain" />
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-4 border-t border-zinc-200 dark:border-white/10">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
