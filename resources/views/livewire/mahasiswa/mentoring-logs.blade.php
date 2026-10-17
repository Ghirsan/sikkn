<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ditanggapi')" :value="$stats['reviewed']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Tanggapan')" :value="$stats['pending']" />
    </div>

    {{-- Mentoring Logs Actions --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-end gap-2 w-full">
        @if($logs->isNotEmpty())
            <flux:button variant="ghost" size="sm" icon="printer" :href="route('mentoring-logs.pdf', $student)" target="_blank" class="w-full sm:w-auto">
                {{ __('Cetak Buku') }}
            </flux:button>
        @endif
        <flux:button variant="filled" size="sm" icon="plus" href="{{ route('mentoring-logs.form') }}" wire:navigate class="w-full sm:w-auto">
            {{ __('Tambah Catatan') }}
        </flux:button>
    </div>

    @if($logs->isEmpty())
        <flux:card>
            <x-empty-state icon="clipboard-document-list" :heading="__('Belum Ada Catatan')" :description="__('Mulai catat aktivitas pembimbingan Anda dengan Dosen KKN.')" />
        </flux:card>
    @else
        <div class="flex flex-col gap-4">
            @foreach($logs as $log)
                <flux:card class="flex flex-col gap-4">
                    {{-- Top bar --}}
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-800/10 dark:border-white/10 pb-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-3">
                                <flux:badge size="sm" color="zinc" class="font-medium whitespace-nowrap">
                                    {{ $log->date->translatedFormat('d M Y') }}
                                </flux:badge>
                                <flux:text variant="strong" class="text-sm">
                                    {{ $log->topic }}
                                </flux:text>
                            </div>
                            @if($log->program)
                                <div class="text-xs text-zinc-500 flex items-center gap-1 mt-1">
                                    <flux:icon.folder variant="micro" />
                                    <span>[{{ $log->program->programType?->name ?? 'Program' }}] {{ $log->program->title }}</span>
                                </div>
                            @endif
                        </div>
                        <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->mentoringLabel() }}</flux:badge>
                    </div>

                    {{-- Body --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Uraian & Hambatan') }}</flux:text>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-white/5 rounded-lg p-3 whitespace-pre-wrap">{{ $log->discussion_summary }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Solusi / Saran Dosen KKN') }}</flux:text>
                            @if($log->dpl_feedback)
                                <flux:callout variant="success" icon="check-circle" class="h-full">
                                    <div class="whitespace-pre-wrap">{{ $log->dpl_feedback }}</div>
                                </flux:callout>
                            @else
                                <div class="text-sm text-zinc-400 dark:text-zinc-500 italic bg-zinc-50 dark:bg-white/5 rounded-lg p-3 h-full flex items-center">{{ __('Belum ada tanggapan dari Dosen KKN.') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200 dark:border-white/10">
                        <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewLog({{ $log->id }})">{{ __('Lihat') }}</flux:button>
                        @if($log->status === \App\Enums\LogStatus::Draft)
                            <flux:button variant="ghost" size="sm" icon="pencil-square" href="{{ route('mentoring-logs.form', ['logId' => $log->id]) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                            <flux:button variant="primary" size="sm" icon="paper-airplane" wire:click="submitLog({{ $log->id }})">{{ __('Ajukan') }}</flux:button>
                        @endif
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- View Modal --}}
    <flux:modal name="mentoring-view-modal" scroll="body" class="max-w-2xl w-full">
        @if($viewLogData)
            <div class="space-y-6">
                {{-- Header --}}
                <div>
                    <flux:heading size="lg">{{ $viewLogData->topic }}</flux:heading>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <flux:text variant="subtle" class="text-sm">{{ $viewLogData->date->translatedFormat('l, d M Y') }}</flux:text>
                        <flux:separator vertical variant="subtle" class="h-4" />
                        <flux:badge size="sm" :color="$viewLogData->status->color()">{{ $viewLogData->status->mentoringLabel() }}</flux:badge>
                    </div>
                </div>

                <flux:separator variant="subtle" />

                {{-- Sections --}}
                <div class="space-y-6">
                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Identitas Bimbingan') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Program Terkait') }}</flux:text>
                                @if($viewLogData->program)
                                    <flux:text>[{{ $viewLogData->program->programType?->name ?? 'Program' }}] {{ $viewLogData->program->title }}</flux:text>
                                @else
                                    <flux:text>{{ __('Umum / Tidak terikat program spesifik') }}</flux:text>
                                @endif
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Kelompok Sasaran') }}</flux:text>
                                <flux:text>{{ $viewLogData->target_group ?: '-' }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Jumlah Mahasiswa') }}</flux:text>
                                <flux:text>{{ $viewLogData->student_count ?: '-' }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Luaran (Output)') }}</flux:text>
                                <flux:text>{{ $viewLogData->output ?: '-' }}</flux:text>
                            </div>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Uraian & Hambatan') }}</flux:heading>
                        <div>
                            <flux:text class="whitespace-pre-wrap">{{ $viewLogData->discussion_summary }}</flux:text>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Solusi / Saran Dosen KKN') }}</flux:heading>
                        @if($viewLogData->dpl_feedback)
                            <flux:callout variant="success" icon="check-circle">
                                <div class="whitespace-pre-wrap text-sm">{{ $viewLogData->dpl_feedback }}</div>
                            </flux:callout>
                        @else
                            <flux:text class="italic text-zinc-500">{{ __('Belum ada tanggapan dari Dosen KKN.') }}</flux:text>
                        @endif
                    </section>
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
