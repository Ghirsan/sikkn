<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Tanggapan')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ditanggapi')" :value="$stats['reviewed']" />
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
    </div>

    {{-- Filter --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-end">
        <div class="flex flex-wrap items-center gap-3">
            @if($allGroups->count() > 1)
                <flux:select wire:model.live="selectedGroupId" size="sm" class="w-full sm:w-48">
                    <option value="">{{ __('Semua Kelompok') }}</option>
                    @foreach($allGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </flux:select>
            @endif

            <flux:select wire:model.live="filterStudent" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Mahasiswa') }}</option>
                @foreach($students as $student)
                    <option value="{{ $student->id }}">{{ $student->name }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Status') }}</option>
                <option value="pending">{{ __('Menunggu Tanggapan') }}</option>
                <option value="approved">{{ __('Ditanggapi') }}</option>
            </flux:select>
        </div>
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
                                    <span><flux:icon.folder variant="micro" class="inline mr-1"/>[{{ $log->program->programType?->name ?? 'Program' }}] {{ $log->program->title }}</span>
                                @endif
                            </div>
                        </div>
                        <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->mentoringLabel() }}</flux:badge>
                    </div>

                    {{-- Body --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Uraian & Hambatan') }}</flux:text>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-white/5 rounded-lg p-3 whitespace-pre-wrap h-full">{{ $log->discussion_summary }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Tanggapan Anda') }}</flux:text>
                            @if($log->dpl_feedback)
                                <flux:callout variant="success" icon="check-circle" class="h-full">
                                    <div class="whitespace-pre-wrap">{{ $log->dpl_feedback }}</div>
                                </flux:callout>
                            @else
                                <div class="bg-zinc-50 dark:bg-white/5 rounded-lg p-3 h-full flex flex-col justify-center items-center gap-2 border border-dashed border-zinc-300 dark:border-zinc-700">
                                    <span class="text-sm text-zinc-500 italic">{{ __('Belum ditanggapi.') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                        <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewLog({{ $log->id }})">{{ __('Lihat Detail') }}</flux:button>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- View & Feedback Modal --}}
    <flux:modal name="mentoring-view-modal" scroll="body" @close="closeView" class="max-w-2xl w-full">
        @if($this->viewLogData)
            <div class="flex flex-col gap-6">
                {{-- Header --}}
                <div class="flex flex-col gap-3">
                    <flux:heading size="lg">{{ $this->viewLogData->topic }}</flux:heading>
                    <div class="flex flex-wrap items-center gap-2 text-sm text-zinc-500">
                        <span>{{ $this->viewLogData->date->translatedFormat('l, d M Y') }}</span>
                        <span>·</span>
                        <span class="font-medium text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
                            <flux:icon.user variant="micro" />
                            {{ $this->viewLogData->student->name }}
                        </span>
                        @if($this->viewLogData->group)
                            <span>·</span>
                            <span>{{ $this->viewLogData->group->name }}</span>
                        @endif
                    </div>
                    <div>
                        <flux:badge size="sm" :color="$this->viewLogData->status->color()">{{ $this->viewLogData->status->mentoringLabel() }}</flux:badge>
                    </div>
                    @if($this->viewLogData->program)
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 flex items-center gap-2 mt-1">
                            <flux:icon.folder variant="micro" class="text-zinc-400" />
                            <span>[{{ $this->viewLogData->program->programType?->name ?? 'Program' }}] {{ $this->viewLogData->program->title }}</span>
                        </div>
                    @else
                        <div class="text-sm text-zinc-600 dark:text-zinc-400 flex items-center gap-2 mt-1">
                            <flux:icon.folder variant="micro" class="text-zinc-400" />
                            <span>{{ __('Umum / Tidak terikat program spesifik') }}</span>
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <flux:heading size="sm" class="font-medium">{{ __('Uraian & Hambatan') }}</flux:heading>
                        <div class="p-3 rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-sm text-zinc-800 dark:text-zinc-200 whitespace-pre-wrap">{{ $this->viewLogData->discussion_summary }}</div>
                    </div>

                    @if($this->viewLogData->dpl_feedback && ! $showFeedbackForm)
                        <div class="flex flex-col gap-2">
                            <flux:heading size="sm" class="font-medium">{{ __('Tanggapan Anda') }}</flux:heading>
                            <flux:callout variant="success" icon="check-circle">
                                <div class="whitespace-pre-wrap text-sm">{{ $this->viewLogData->dpl_feedback }}</div>
                            </flux:callout>
                        </div>
                    @elseif(! $this->viewLogData->dpl_feedback && ! $showFeedbackForm)
                        <div class="flex flex-col gap-2">
                            <flux:heading size="sm" class="font-medium">{{ __('Tanggapan Anda') }}</flux:heading>
                            <div class="p-3 rounded-lg border border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/5 text-sm text-zinc-400 dark:text-zinc-500 italic flex items-center">
                                {{ __('Belum ada tanggapan yang diberikan.') }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Metrics --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-white/10">
                        <span class="text-xs text-zinc-500">{{ __('Kelompok Sasaran') }}</span>
                        <span class="text-sm font-medium">{{ $this->viewLogData->target_group ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-white/10">
                        <span class="text-xs text-zinc-500">{{ __('Jumlah Mahasiswa') }}</span>
                        <span class="text-sm font-medium">{{ $this->viewLogData->student_count ?? '-' }}</span>
                    </div>
                    <div class="flex flex-col gap-1 p-3 rounded-lg border border-zinc-200 dark:border-white/10">
                        <span class="text-xs text-zinc-500">{{ __('Luaran') }}</span>
                        <span class="text-sm font-medium">{{ $this->viewLogData->output ?? '-' }}</span>
                    </div>
                </div>

                {{-- Feedback Form Section (ReviewPrograms modal style) --}}
                @if($showFeedbackForm)
                    <flux:separator variant="subtle" />
                    <section class="space-y-3">
                        <flux:textarea wire:model="feedback" label="{{ __('Tanggapan / Solusi DPL') }}" placeholder="{{ __('Berikan arahan, tanggapan, atau solusi untuk mahasiswa...') }}" rows="3" />
                        @error('feedback')
                            <flux:text class="text-xs text-red-500">{{ $message }}</flux:text>
                        @enderror
                        <div class="flex justify-end gap-2">
                            <flux:button wire:click="cancelFeedback" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                            <flux:button wire:click="submitFeedback" size="sm" variant="primary" icon="paper-airplane">{{ __('Kirim Tanggapan') }}</flux:button>
                        </div>
                    </section>
                @endif

                {{-- Actions --}}
                <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200 dark:border-white/10">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                    @if(! $showFeedbackForm)
                        @if(! $this->viewLogData->dpl_feedback)
                            <flux:button variant="primary" icon="chat-bubble-left-right" wire:click="startFeedback">{{ __('Beri Tanggapan') }}</flux:button>
                        @else
                            <flux:button variant="ghost" size="sm" icon="pencil-square" wire:click="startFeedback">{{ __('Ubah Tanggapan') }}</flux:button>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
