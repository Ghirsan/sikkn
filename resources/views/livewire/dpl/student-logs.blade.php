<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3 {{ isset($totalHours) ? 'lg:grid-cols-4' : '' }}">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Persetujuan')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="book-open" color="blue" :label="__('Total Entri')" :value="$stats['total']" />
        @if(isset($totalHours))
            <x-stat-card icon="calculator" color="zinc" :label="__('Total Jam Kerja')" :value="$totalHours . ' Jam'" />
        @endif
    </div>

    {{-- Filters + Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end">

        {{-- Filters --}}
        <div class="flex flex-wrap items-center gap-3">
            @if($allGroups->count() > 1)
                <flux:select
                    wire:model.live="selectedGroupId"
                    size="sm"
                    class="w-full sm:w-48"
                >
                    <option value="">{{ __('Semua Kelompok') }}</option>

                    @foreach($allGroups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </flux:select>
            @endif

            <flux:select
                wire:model.live="filterStudent"
                size="sm"
                class="w-full sm:w-48"
            >
                <option value="">{{ __('Semua Mahasiswa') }}</option>

                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model.live="filterStatus"
                size="sm"
                class="w-full sm:w-48"
            >
                <option value="">{{ __('Semua Status') }}</option>
                <option value="pending">{{ __('Menunggu Persetujuan') }}</option>
                <option value="approved">{{ __('Disetujui') }}</option>
            </flux:select>
        </div>

        {{-- Actions --}}
        <div class="mt-3 flex items-center gap-3 sm:mt-0 sm:ml-3 shrink-0">
            @if(count($selectedLogs) > 0)
                <flux:modal.trigger name="confirm-bulk-approve-modal">
                    <flux:button
                        variant="primary"
                        size="sm"
                        icon="check-circle"
                    >
                        {{ __('Setujui Terpilih (:count)', ['count' => count($selectedLogs)]) }}
                    </flux:button>
                </flux:modal.trigger>
            @endif

            @if($filterStudent && $logs->isNotEmpty())
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="printer"
                    :href="route('logbook.pdf', $filterStudent)"
                    target="_blank"
                >
                    {{ __('Cetak') }}
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Weeks --}}
    @if($selectedGroupId && count($weeks) > 0)
        <x-tabs>
            <x-tab
                wire:click="$set('selectedWeek', 'all')"
                :selected="$selectedWeek === 'all'"
            >
                Semua Minggu
            </x-tab>

            @foreach($weeks as $week)
                <x-tab
                    wire:click="$set('selectedWeek', '{{ $week }}')"
                    :selected="$selectedWeek == $week"
                >
                    {{ __('Minggu ') . $week }}
                </x-tab>
            @endforeach
        </x-tabs>
    @endif


    {{-- Logs --}}
    <flux:card>
        @if($logs->isEmpty())
            <x-empty-state icon="book-open" :heading="__('Belum Ada Logbook')" :description="__('Tidak ada logbook yang sesuai dengan filter pencarian.')" />
        @else
            <flux:table :paginate="$logs">
                <flux:table.columns>
                    <flux:table.column>
                        <flux:checkbox wire:model.live="selectAll" />
                    </flux:table.column>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'date'" :direction="$sortDirection" wire:click="sort('date')">{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Kegiatan') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                @if($log->status->value === 'pending')
                                    <flux:checkbox wire:model.live="selectedLogs" value="{{ $log->id }}" />
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $log->student->name }}</flux:text>
                                <flux:text variant="subtle" class="text-xs">{{ $log->student->nim }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $log->date->translatedFormat('d M Y') }}</flux:text>
                                <flux:text variant="subtle" class="text-xs">{{ $log->date->translatedFormat('l') }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="space-y-1">
                                    @foreach($log->activities->take(3) as $activity)
                                        <div class="flex items-start gap-2 text-sm">
                                            <flux:text variant="subtle" class="text-xs">
                                                {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                            </flux:text>
                                            <flux:text variant="strong">{{ $activity->activity_description }}</flux:text>
                                        </div>
                                    @endforeach
                                    @if($log->activities->count() > 3)
                                        <flux:text variant="subtle" class="text-xs"> {{ __('+ :count kegiatan lainnya', ['count' => $log->activities->count() - 3]) }}</flux:text>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex gap-2">
                                    <flux:button wire:click="viewLog({{ $log->id }})" size="sm" variant="ghost" icon="eye">{{ __('Lihat') }}</flux:button>
                                    @if($log->status->value === 'pending')
                                        <flux:button wire:click="confirmApprove({{ $log->id }})" size="sm" variant="filled" icon="check" class="text-green-600 bg-green-50 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20">{{ __('Setujui') }}</flux:button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- View Modal --}}
    <flux:modal name="log-view-modal" scroll="body" class="max-w-2xl w-full">
        @if($viewLogData)
            <div class="flex flex-col gap-6">
                {{-- Header --}}
                <div class="flex items-start gap-3">
                    <flux:avatar
                        :name="$viewLogData->student->name"
                        :initials="$viewLogData->student->initials()"
                        size="sm"
                        class="mt-0.5"
                    />
                    <div class="flex flex-col gap-1.5">
                        <flux:heading size="lg">{{ $viewLogData->student->name }}</flux:heading>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:text variant="subtle">Logbook Hari Ke-{{ $viewLogData->day_number }}</flux:text>
                            <flux:separator vertical variant="subtle" />
                            <flux:text variant="subtle">{{ $viewLogData->date->translatedFormat('l, d M Y') }}</flux:text>
                        </div>
                        <div class="space-y-1.5">
                            <flux:badge size="sm" :color="$viewLogData->status->color()">{{ $viewLogData->status->label() }}</flux:badge>
                        </div>
                    </div>
                </div>

                <flux:separator />

                {{-- Activities --}}
                <div class="flex flex-col gap-3">
                    <flux:heading size="sm">Kegiatan</flux:heading>
                    <div class="flex flex-col gap-2">
                        @foreach($viewLogData->activities as $index => $activity)
                            <flux:card variant="subtle" class="flex gap-4 p-2">
                                <flux:text variant="subtle" class="text-sm">
                                    {{ \Carbon\Carbon::parse($activity->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($activity->end_time)->format('H:i') }}
                                </flux:text>
                                <flux:text variant="strong" class="text-sm">
                                    {{ $activity->activity_description }}
                                </flux:text>
                            </flux:card>
                        @endforeach
                    </div>
                </div>

                <flux:separator variant="subtle" />

                {{-- Notes & Image --}}
                @if($viewLogData->important_notes || $viewLogData->image_path)
                    <flux:heading size="sm">Catatan Penting</flux:heading>
                    @if($viewLogData->important_notes)
                        <flux:text>
                            {{ ($viewLogData->important_notes) }}
                        </flux:text>
                    @endif
                    <div>
                        <flux:card variant="soft" class="overflow-hidden p-0">
                            @if($viewLogData->image_path)
                                <img
                                    src="{{ asset('storage/' . $viewLogData->image_path) }}"
                                    alt="Catatan gambar"
                                    class="mx-auto block max-h-96 max-w-full object-contain"
                                    x-on:click="$flux.modal('image-preview-view').show()"
                                    />
                                
                                <x-image-preview-modal name="image-preview-view" url="{{ asset('storage/' . $viewLogData->image_path) }}" />
                            @endif
                        </flux:card>
                    </div>
                @endif
                
                {{-- Actions --}}
                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                        
                    @if($viewLogData->status->value === 'pending')
                        <flux:button wire:click="approveDailyLog({{ $viewLogData->id }})" variant="primary" icon="check">
                            {{ __('Setujui Logbook') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>

    {{-- Confirm Approve Modal --}}
    <flux:modal name="confirm-approve-modal" class="max-w-sm w-full">
        <form wire:submit.prevent="executeApprove" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Konfirmasi Persetujuan') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menyetujui logbook ini? Tindakan ini tidak dapat dibatalkan.') }}
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Ya, Setujui') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Confirm Bulk Approve Modal --}}
    <flux:modal name="confirm-bulk-approve-modal" class="max-w-sm w-full">
        <form wire:submit.prevent="executeBulkApprove" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Konfirmasi Persetujuan') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menyetujui :count logbook yang dipilih? Tindakan ini tidak dapat dibatalkan.', ['count' => count($selectedLogs)]) }}
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Ya, Setujui') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
