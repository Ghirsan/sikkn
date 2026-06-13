<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Feedback')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Difeedback')" :value="$stats['reviewed']" />
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
    </div>

    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Catatan Pembimbingan') }}</flux:heading>
    </div>

    <flux:card>

        @if($logs->isEmpty())
            <x-empty-state icon="clipboard-document-list" :heading="__('Belum Ada Catatan')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Topik & Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Rangkuman Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Feedback DPL') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                <div class="font-medium text-neutral-900 dark:text-white">{{ $log->topic }}</div>
                                <div class="text-xs text-neutral-500">{{ $log->student->name }} — {{ $log->date->translatedFormat('d F Y') }}</div>
                                <flux:badge size="sm" :color="$log->status->color()" class="mt-1">{{ $log->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="text-sm text-neutral-600 dark:text-neutral-300">{{ $log->discussion_summary }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($log->dpl_feedback)
                                    <flux:callout variant="success" icon="chat-bubble-left-right" :heading="__('Feedback Anda: ') . $log->dpl_feedback" />
                                @elseif($feedbackLogId === $log->id)
                                    <flux:card class="border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20">
                                        <flux:textarea wire:model="feedback" placeholder="{{ __('Berikan feedback...') }}" rows="3" />
                                        @error('feedback') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                                        <div class="mt-2 flex gap-2">
                                            <flux:button wire:click="submitFeedback" size="sm" variant="filled">{{ __('Kirim') }}</flux:button>
                                            <flux:button wire:click="$set('feedbackLogId', 0)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                                        </div>
                                    </flux:card>
                                @else
                                    <flux:button wire:click="startFeedback({{ $log->id }})" size="sm" variant="ghost" icon="chat-bubble-left-right" inset="top bottom">{{ __('Beri Feedback') }}</flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
