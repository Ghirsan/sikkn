<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Tanggapan')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ditanggapi')" :value="$stats['reviewed']" />
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full max-w-sm">
            <x-search-bar placeholder="{{ __('Cari mahasiswa, NIM, atau topik...') }}" clearable class="w-full sm:w-80" />
        </div>
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

    {{-- Mentoring Logs Table --}}
    <flux:card>
        @if($logs->isEmpty())
            <x-empty-state icon="clipboard-document-list" :heading="__('Belum Ada Catatan')" :description="__('Belum ada catatan pembimbingan dari mahasiswa.')" />
        @else
            <flux:table :paginate="$logs">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa & Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Topik & Pembahasan') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($logs as $log)
                        <flux:table.row :key="$log->id">
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $log->student->name }}</flux:text>
                                <flux:text variant="subtle" class="text-xs block">{{ $log->student->nim }} &middot; {{ $log->group?->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $log->date->translatedFormat('d M Y') }}</flux:text>
                                <flux:text variant="subtle" class="text-xs block">{{ $log->date->translatedFormat('l') }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong" class="block max-w-sm truncate" title="{{ $log->topic }}">{{ $log->topic }}</flux:text>
                                <flux:text variant="subtle" class="text-xs line-clamp-1 max-w-sm mt-0.5" title="{{ $log->discussion_summary }}">
                                    {{ $log->discussion_summary }}
                                </flux:text>
                                @if($log->program)
                                    <flux:badge size="sm" color="zinc" class="mt-1">
                                        [{{ $log->program->programType?->name ?? 'Program' }}] {{ $log->program->title }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->mentoringLabel() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button wire:click="viewLog({{ $log->id }})" size="sm" variant="ghost" icon="eye">{{ __('Periksa') }}</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- View & Feedback Modal --}}
    <flux:modal name="mentoring-view-modal" scroll="body" @close="closeView" class="max-w-2xl w-full">
        @if($this->viewLogData)
            <div class="space-y-6">
                {{-- Header --}}
                <div class="flex items-start gap-3">
                    <flux:avatar
                        :name="$this->viewLogData->student->name"
                        :initials="$this->viewLogData->student->initials()"
                        size="sm"
                        class="mt-0.5"
                    />
                    <div class="flex min-w-0 flex-col gap-1.5">
                        <flux:heading size="lg">{{ $this->viewLogData->topic }}</flux:heading>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:text variant="subtle" class="text-sm">
                                {{ $this->viewLogData->student->name }}
                                @if($this->viewLogData->student->nim)
                                    ({{ $this->viewLogData->student->nim }})
                                @endif
                            </flux:text>
                            <flux:separator vertical variant="subtle" class="h-4" />
                            <flux:text variant="subtle" class="text-sm">{{ $this->viewLogData->date->translatedFormat('l, d M Y') }}</flux:text>
                            @if($this->viewLogData->group)
                                <flux:separator vertical variant="subtle" class="h-4" />
                                <flux:text variant="subtle" class="text-sm">{{ $this->viewLogData->group->name }}</flux:text>
                            @endif
                        </div>
                        <div>
                            <flux:badge size="sm" :color="$this->viewLogData->status->color()">{{ $this->viewLogData->status->mentoringLabel() }}</flux:badge>
                        </div>
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
                                @if($this->viewLogData->program)
                                    <flux:text>[{{ $this->viewLogData->program->programType?->name ?? 'Program' }}] {{ $this->viewLogData->program->title }}</flux:text>
                                @else
                                    <flux:text>{{ __('Umum / Tidak terikat program spesifik') }}</flux:text>
                                @endif
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Kelompok Sasaran') }}</flux:text>
                                <flux:text>{{ $this->viewLogData->target_group ?: '-' }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Jumlah Mahasiswa') }}</flux:text>
                                <flux:text>{{ $this->viewLogData->student_count ?: '-' }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Luaran (Output)') }}</flux:text>
                                <flux:text>{{ $this->viewLogData->output ?: '-' }}</flux:text>
                            </div>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Uraian & Hambatan') }}</flux:heading>
                        <div>
                            <flux:text class="whitespace-pre-wrap">{{ $this->viewLogData->discussion_summary }}</flux:text>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Tanggapan / Arahan DPL') }}</flux:heading>
                        @if($this->viewLogData->dpl_feedback && ! $showFeedbackForm)
                            <flux:callout variant="success" icon="check-circle">
                                <div class="whitespace-pre-wrap text-sm">{{ $this->viewLogData->dpl_feedback }}</div>
                            </flux:callout>
                        @elseif(! $this->viewLogData->dpl_feedback && ! $showFeedbackForm)
                            <flux:text class="italic text-zinc-500">{{ __('Belum ada tanggapan yang diberikan.') }}</flux:text>
                        @endif
                    </section>

                    @if($showFeedbackForm)
                        <flux:separator variant="subtle" />
                        <section class="space-y-3">
                            <flux:textarea wire:model="feedback" label="{{ __('Tanggapan / Solusi DPL') }}" placeholder="{{ __('Berikan arahan, tanggapan, atau solusi untuk mahasiswa...') }}" rows="3" />
                            @error('feedback')
                                <flux:text class="text-xs text-red-500">{{ $message }}</flux:text>
                            @enderror
                            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                                <flux:button wire:click="cancelFeedback" variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
                                <flux:button wire:click="submitFeedback" variant="primary" icon="paper-airplane" class="w-full sm:w-auto">{{ __('Kirim Tanggapan') }}</flux:button>
                            </div>
                        </section>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                    <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                        <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                    @if(! $showFeedbackForm)
                        @if(! $this->viewLogData->dpl_feedback)
                            <flux:button variant="primary" icon="chat-bubble-left-right" wire:click="startFeedback" class="w-full sm:w-auto">{{ __('Beri Tanggapan') }}</flux:button>
                        @else
                            <flux:button variant="ghost" icon="pencil-square" wire:click="startFeedback" class="w-full sm:w-auto">{{ __('Ubah Tanggapan') }}</flux:button>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
