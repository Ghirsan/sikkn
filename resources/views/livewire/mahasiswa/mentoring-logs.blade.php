<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Difeedback')" :value="$stats['reviewed']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Feedback')" :value="$stats['pending']" />
    </div>

    {{-- Mentoring Logs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Catatan Pembimbingan') }}</flux:heading>
        <div class="flex items-center gap-3">
            @if($logs->isNotEmpty())
                <flux:button variant="ghost" size="sm" icon="printer" :href="route('mentoring-logs.pdf', $student)" target="_blank">
                    {{ __('Cetak Buku') }}
                </flux:button>
            @endif
            <flux:button variant="filled" size="sm" icon="plus" href="{{ route('mentoring-logs.form') }}" wire:navigate>
                {{ __('Tambah Catatan') }}
            </flux:button>
        </div>
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
                                    <span>[{{ $log->program->type }}] {{ $log->program->title }}</span>
                                </div>
                            @endif
                        </div>
                        <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
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
                                <div class="text-sm text-zinc-400 dark:text-zinc-500 italic bg-zinc-50 dark:bg-white/5 rounded-lg p-3 h-full flex items-center">{{ __('Belum ada feedback dari Dosen KKN.') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom info & Actions --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4 border-t border-zinc-800/10 dark:border-white/10">
                        <div class="flex flex-wrap gap-2">
                            @if($log->target_group)
                                <flux:badge size="sm" color="zinc" icon="user-group">{{ __('Sasaran: ') }} {{ $log->target_group }}</flux:badge>
                            @endif
                            @if($log->student_count)
                                <flux:badge size="sm" color="zinc" icon="users">{{ __('Jml: ') }} {{ $log->student_count }} {{ __('Mahasiswa') }}</flux:badge>
                            @endif
                            @if($log->output)
                                <flux:badge size="sm" color="zinc" icon="document-text">{{ __('Luaran: ') }} {{ $log->output }}</flux:badge>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewLog({{ $log->id }})">{{ __('Lihat') }}</flux:button>
                            @if($log->status === \App\Enums\LogStatus::Pending)
                                <flux:button variant="ghost" size="sm" icon="pencil-square" href="{{ route('mentoring-logs.form', ['logId' => $log->id]) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- View Modal --}}
    <flux:modal name="mentoring-view-modal" class="md:w-3/4 lg:w-[40rem]">
        @if($viewLogData)
            <div class="flex flex-col gap-6">
                {{-- Header --}}
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <flux:heading size="lg">{{ $viewLogData->topic }}</flux:heading>
                        <flux:badge size="sm" :color="$viewLogData->status->color()">{{ $viewLogData->status->label() }}</flux:badge>
                    </div>
                    <flux:text class="text-sm text-zinc-500">
                        {{ $viewLogData->date->translatedFormat('l, d M Y') }}
                    </flux:text>
                    @if($viewLogData->program)
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 flex items-center gap-2 mt-1">
                            <flux:icon.folder variant="micro" class="text-zinc-400" />
                            <span>[{{ $viewLogData->program->type }}] {{ $viewLogData->program->title }}</span>
                        </div>
                    @else
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 flex items-center gap-2 mt-1">
                            <flux:icon.folder variant="micro" class="text-zinc-400" />
                            <span>Umum / Tidak terikat program spesifik</span>
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <flux:heading size="sm" class="font-medium">Uraian & Hambatan</flux:heading>
                        <div class="p-3 rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-sm text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap">{{ $viewLogData->discussion_summary }}</div>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <flux:heading size="sm" class="font-medium">Solusi / Saran Dosen KKN</flux:heading>
                        @if($viewLogData->dpl_feedback)
                            <flux:callout variant="success" icon="check-circle">
                                <div class="whitespace-pre-wrap text-sm">{{ $viewLogData->dpl_feedback }}</div>
                            </flux:callout>
                        @else
                            <div class="p-3 rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-sm text-zinc-400 dark:text-zinc-500 italic flex items-center">
                                {{ __('Belum ada feedback dari Dosen KKN.') }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Metrics --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-white/10">
                        <span class="text-xs text-zinc-500">Kelompok Sasaran</span>
                        <span class="text-sm font-medium">{{ $viewLogData->target_group ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-white/10">
                        <span class="text-xs text-zinc-500">Jml Mahasiswa</span>
                        <span class="text-sm font-medium">{{ $viewLogData->student_count ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-white/10">
                        <span class="text-xs text-zinc-500">Luaran</span>
                        <span class="text-sm font-medium">{{ $viewLogData->output ?? '-' }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end pt-2 border-t border-zinc-200 dark:border-white/10">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
