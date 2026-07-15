<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Feedback')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Difeedback')" :value="$stats['reviewed']" />
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Catatan Pembimbingan') }}</flux:heading>
    </div>

    @if($logs->isEmpty())
        <flux:card>
            <x-empty-state icon="clipboard-document-list" :heading="__('Belum Ada Catatan')" :description="__('Belum ada catatan pembimbingan dari mahasiswa.')" />
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
                            <div class="text-xs text-zinc-500 flex items-center gap-2 mt-1">
                                <span class="font-medium text-zinc-700 dark:text-zinc-300"><flux:icon.user variant="micro" class="inline mr-1"/>{{ $log->student->name }}</span>
                                @if($log->program)
                                    <span>·</span>
                                    <span><flux:icon.folder variant="micro" class="inline mr-1"/>[{{ $log->program->type }}] {{ $log->program->title }}</span>
                                @endif
                            </div>
                        </div>
                        <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                    </div>

                    {{-- Body --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Uraian & Hambatan') }}</flux:text>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-white/5 rounded-lg p-3 whitespace-pre-wrap h-full">{{ $log->discussion_summary }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Feedback Anda') }}</flux:text>
                            @if($log->dpl_feedback)
                                <flux:callout variant="success" icon="check-circle" class="h-full">
                                    <div class="whitespace-pre-wrap">{{ $log->dpl_feedback }}</div>
                                </flux:callout>
                            @elseif($feedbackLogId === $log->id)
                                <div class="border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20 rounded-lg p-3 h-full">
                                    <flux:textarea wire:model="feedback" placeholder="{{ __('Berikan feedback atau solusi...') }}" rows="3" />
                                    @error('feedback') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                                    <div class="mt-3 flex gap-2 justify-end">
                                        <flux:button wire:click="$set('feedbackLogId', 0)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                                        <flux:button wire:click="submitFeedback" size="sm" variant="primary">{{ __('Kirim Feedback') }}</flux:button>
                                    </div>
                                </div>
                            @else
                                <div class="bg-zinc-50 dark:bg-white/5 rounded-lg p-3 h-full flex flex-col justify-center items-center gap-2 border border-dashed border-zinc-300 dark:border-zinc-700">
                                    <span class="text-sm text-zinc-500 italic">{{ __('Belum ada feedback.') }}</span>
                                    <flux:button wire:click="startFeedback({{ $log->id }})" size="sm" variant="filled" icon="chat-bubble-left-right">{{ __('Beri Feedback') }}</flux:button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom info --}}
                    <div class="flex flex-wrap gap-2 pt-4 border-t border-zinc-800/10 dark:border-white/10">
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
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
