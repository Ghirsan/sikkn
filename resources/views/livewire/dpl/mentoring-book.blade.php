<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Menunggu Feedback') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['pending'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Sudah Difeedback') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['reviewed'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                    <flux:icon name="clipboard-document-list" class="size-5 text-purple-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Total Sesi') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['total'] }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Catatan Pembimbingan') }}</flux:heading>
        </div>
        @if($logs->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="clipboard-document-list" class="mx-auto size-12 text-neutral-300" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Catatan') }}</flux:heading>
            </div>
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
                                    <div class="rounded-lg border border-green-200 bg-green-50 p-2 text-xs text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                                        <strong>{{ __('Feedback Anda:') }}</strong> {{ $log->dpl_feedback }}
                                    </div>
                                @elseif($feedbackLogId === $log->id)
                                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20">
                                        <flux:textarea wire:model="feedback" placeholder="{{ __('Berikan feedback...') }}" rows="3" />
                                        @error('feedback') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                                        <div class="mt-2 flex gap-2">
                                            <flux:button wire:click="submitFeedback" size="sm" variant="filled">{{ __('Kirim') }}</flux:button>
                                            <flux:button wire:click="$set('feedbackLogId', 0)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                                        </div>
                                    </div>
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
