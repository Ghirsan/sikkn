<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 sm:grid-cols-2 md:grid-cols-4">
        <x-stat-card icon="document-text" color="blue" :label="__('Total Program Tim')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Program Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Review')" :value="$stats['pending']" />
        <x-stat-card icon="document-check" color="purple" :label="__('Tim Siap Cetak')" :value="$stats['ready_groups']" />
    </div>

    @if($allGroups->count() > 1)
        <div class="flex flex-col sm:flex-row sm:justify-end w-full">
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        </div>
    @endif

    @forelse($groupData as $data)
        {{-- Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">{{ $data->group->name }}</flux:heading>
                <flux:text>{{ $data->group->location }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge size="sm" :color="$data->lrkReady ? 'green' : 'zinc'">{{ $data->lrkReady ? __('Siap PDF') : $data->approvedCount.'/'.$data->totalPrograms.' '.__('approved') }}</flux:badge>
            </div>
        </div>

        {{-- Document Cards --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- LRK Card --}}
            <flux:card class="border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <flux:icon.document-text variant="mini" class="text-blue-500" />
                        <div>
                            <flux:heading size="sm">{{ __('LRK') }}</flux:heading>
                            <flux:text class="text-xs">{{ __('Laporan Rencana Kegiatan') }}</flux:text>
                        </div>
                    </div>
                    @if($data->isLrkLocked || $data->lrkStatus === \App\Enums\DocumentStatus::Approved)
                        <flux:badge size="sm" color="green">{{ __('Disetujui') }}</flux:badge>
                    @elseif($data->lrkStatus === \App\Enums\DocumentStatus::Submitted)
                        <flux:badge size="sm" color="amber">{{ __('Menunggu Review') }}</flux:badge>
                    @elseif($data->lrkStatus === \App\Enums\DocumentStatus::NeedsRevision)
                        <flux:badge size="sm" color="red">{{ __('Perlu Revisi') }}</flux:badge>
                    @elseif($data->lrkReady)
                        <flux:badge size="sm" color="blue">{{ __('Siap Diajukan') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">{{ __('Draf') }}</flux:badge>
                    @endif
                </div>

                @if($data->isLrkLocked || $data->lrkStatus === \App\Enums\DocumentStatus::Approved)
                    <flux:callout variant="success" icon="check-badge" class="mb-3">
                        <flux:callout.text>{{ __('Dokumen LRK telah disetujui DPL dan siap dicetak.') }}</flux:callout.text>
                    </flux:callout>
                @elseif($data->lrkStatus === \App\Enums\DocumentStatus::Submitted)
                    <flux:callout variant="warning" icon="clock" class="mb-3">
                        <flux:callout.text>{{ __('Ketua tim telah mengajukan dokumen LRK untuk ditinjau.') }}</flux:callout.text>
                    </flux:callout>
                @elseif($data->lrkStatus === \App\Enums\DocumentStatus::NeedsRevision)
                    <flux:callout variant="danger" icon="exclamation-circle" class="mb-3">
                        <flux:callout.text>{{ __('Dokumen LRK dikembalikan ke kelompok dengan catatan revisi.') }}</flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex flex-col sm:flex-row gap-2">
                    @if($data->isLrkLocked || $data->lrkStatus === \App\Enums\DocumentStatus::Approved)
                        <flux:button :href="URL::signedRoute('lrk.pdf', $data->group)" target="_blank" variant="filled" size="sm" icon="printer" class="w-full justify-center">
                            {{ __('Cetak LRK PDF') }}
                        </flux:button>
                    @else
                        <flux:button :href="URL::signedRoute('lrk.preview', $data->group)" target="_blank" variant="subtle" size="sm" icon="arrow-top-right-on-square" class="w-full justify-center">
                            {{ __('Pratinjau LRK PDF') }}
                        </flux:button>
                    @endif
                    <flux:button type="button" wire:click="inspectLrk({{ $data->group->id }})" variant="{{ $data->lrkStatus === \App\Enums\DocumentStatus::Submitted ? 'primary' : 'outline' }}" size="sm" icon="clipboard-document-check" class="w-full justify-center">
                        {{ __('Periksa LRK') }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- LPK Card --}}
            <flux:card class="border border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <flux:icon.document-check variant="mini" class="text-green-500" />
                        <div>
                            <flux:heading size="sm">{{ __('LPK') }}</flux:heading>
                            <flux:text class="text-xs">{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
                        </div>
                    </div>
                    @if($data->isLpkLocked)
                        <flux:badge size="sm" color="green">{{ __('Final') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">{{ __('Belum Siap') }}</flux:badge>
                    @endif
                </div>
                @if($data->isLpkLocked)
                    <flux:callout variant="success" icon="check-badge" class="mb-3">
                        <flux:callout.text>{{ __('LPK telah difinalisasi dan siap dicetak.') }}</flux:callout.text>
                    </flux:callout>
                    <flux:button :href="URL::signedRoute('lpk.pdf', $data->group)" target="_blank" variant="filled" size="sm" icon="printer" class="w-full justify-center">
                        {{ __('Cetak LPK PDF') }}
                    </flux:button>
                @else
                    <flux:button :href="URL::signedRoute('lpk.preview', $data->group)" target="_blank" variant="subtle" size="sm" icon="arrow-top-right-on-square" class="w-full justify-center">
                        {{ __('Pratinjau LPK PDF') }}
                    </flux:button>
                @endif
            </flux:card>
        </div>

        {{-- Programs Status Table --}}
        <flux:card>
            <div class="flex items-center justify-between mb-3">
                <flux:heading size="sm">{{ __('Status Program Kerja') }}</flux:heading>
                <div class="flex items-center gap-2">
                    @if($data->pendingPrograms->isNotEmpty())
                        <flux:badge size="sm" color="amber">{{ $data->pendingPrograms->count() }} {{ __('menunggu') }}</flux:badge>
                    @endif
                    @if($data->revisionPrograms->isNotEmpty())
                        <flux:badge size="sm" color="red">{{ $data->revisionPrograms->count() }} {{ __('revisi') }}</flux:badge>
                    @endif
                    @if($data->approvedPrograms->isNotEmpty())
                        <flux:badge size="sm" color="green">{{ $data->approvedCount }} {{ __('disetujui') }}</flux:badge>
                    @endif
                </div>
            </div>

            @if($data->allPrograms->isEmpty())
                <x-empty-state icon="light-bulb" :heading="__('Belum Ada Program')" :description="__('Mahasiswa belum mengusulkan program kerja.')" />
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Program') }}</flux:table.column>
                        <flux:table.column>{{ __('Tipe') }}</flux:table.column>
                        <flux:table.column>{{ __('Pengusul') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($data->allPrograms as $participant)
                            <flux:table.row :key="$participant->id">
                                <flux:table.cell>
                                    <span class="font-medium">{{ $participant->program->title }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc">{{ $participant->program->programType?->name }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $participant->student?->name ?? __('Kelompok') }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$participant->status->color()" inset="top bottom">{{ $participant->status->label() }}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    @empty
        <flux:card>
            <x-empty-state icon="document-text" :heading="__('Belum Ada Kelompok')" :description="__('Belum ada kelompok bimbingan yang ditugaskan.')" />
        </flux:card>
    @endforelse

    {{-- Review LRK Modal --}}
    <flux:modal name="review-lrk-modal" scroll="body" @close="closeInspectLrk" class="max-w-2xl w-full">
        @if($reviewingGroup)
            <div class="space-y-6">
                {{-- Header --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <flux:heading size="lg">{{ __('Tinjau Dokumen LRK') }}</flux:heading>
                        @if($reviewingGroup->isLrkFinalized())
                            <flux:badge color="green" size="sm">{{ __('Disetujui') }}</flux:badge>
                        @elseif($reviewingGroup->lrk_status === \App\Enums\DocumentStatus::Submitted)
                            <flux:badge color="amber" size="sm">{{ __('Diajukan') }}</flux:badge>
                        @elseif($reviewingGroup->lrk_status === \App\Enums\DocumentStatus::NeedsRevision)
                            <flux:badge color="red" size="sm">{{ __('Perlu Revisi') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Draf') }}</flux:badge>
                        @endif
                    </div>
                    <flux:text class="text-sm text-zinc-500">
                        {{ $reviewingGroup->name }} · {{ $reviewingGroup->location }}
                    </flux:text>
                </div>

                <flux:separator variant="subtle" />

                {{-- Status & Summary --}}
                <section class="space-y-3">
                    <flux:heading size="sm">{{ __('Informasi Pengajuan') }}</flux:heading>
                    <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2 text-sm">
                        <div>
                            <span class="text-zinc-500 block">{{ __('Ketua Tim') }}</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $reviewingGroup->studentLeader?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 block">{{ __('Waktu Pengajuan') }}</span>
                            <span class="font-medium text-zinc-800 dark:text-zinc-200">
                                {{ $reviewingGroup->lrk_submitted_at ? $reviewingGroup->lrk_submitted_at->translatedFormat('d F Y H:i') : __('Belum diajukan') }}
                            </span>
                        </div>
                    </div>
                    <div class="pt-2">
                        <flux:button :href="URL::signedRoute('lrk.preview', $reviewingGroup)" target="_blank" icon="arrow-top-right-on-square" size="sm" variant="subtle" class="w-full sm:w-auto">
                            {{ __('Buka Dokumen PDF Lengkap') }}
                        </flux:button>
                    </div>
                </section>

                <flux:separator variant="subtle" />

                {{-- Latar Belakang --}}
                <section class="space-y-2">
                    <flux:heading size="sm">{{ __('Latar Belakang & Analisis Potensi') }}</flux:heading>
                    <div class="p-3 bg-zinc-50 dark:bg-white/5 rounded-lg border border-zinc-200 dark:border-white/10 text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                        {{ $reviewingGroup->background ?: __('(Belum diisi)') }}
                    </div>
                </section>

                <flux:separator variant="subtle" />

                {{-- Usulan Rencana Program --}}
                <section class="space-y-3">
                    <flux:heading size="sm">{{ __('Usulan Rencana Program') }}</flux:heading>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300 block mb-1">{{ __('Program Multidisiplin') }}</span>
                            <div class="p-2.5 bg-zinc-50 dark:bg-white/5 rounded border border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-300">
                                {{ $reviewingGroup->program_multidisiplin_text ?: __('(Belum diisi)') }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300 block mb-1">{{ __('Program Sosial Kemasyarakatan') }}</span>
                            <div class="p-2.5 bg-zinc-50 dark:bg-white/5 rounded border border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-300">
                                {{ $reviewingGroup->program_sosmas_text ?: __('(Belum diisi)') }}
                            </div>
                        </div>
                        <div>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300 block mb-1">{{ __('Program Lainnya') }}</span>
                            <div class="p-2.5 bg-zinc-50 dark:bg-white/5 rounded border border-zinc-200 dark:border-white/10 text-zinc-600 dark:text-zinc-300">
                                {{ $reviewingGroup->program_lainnya_text ?: __('(Belum diisi)') }}
                            </div>
                        </div>
                    </div>
                </section>

                @if($reviewingGroup->storyboard_text || $reviewingGroup->video_script_text)
                    <flux:separator variant="subtle" />
                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Storyboard & Skrip Video Profil') }}</flux:heading>
                        <div class="space-y-2 text-sm">
                            @if($reviewingGroup->storyboard_text)
                                <div>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300 block mb-1">{{ __('Storyboard') }}</span>
                                    <div class="p-2.5 bg-zinc-50 dark:bg-white/5 rounded border border-zinc-200 dark:border-white/10 whitespace-pre-line text-zinc-600 dark:text-zinc-300">
                                        {{ $reviewingGroup->storyboard_text }}
                                    </div>
                                </div>
                            @endif
                            @if($reviewingGroup->video_script_text)
                                <div>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300 block mb-1">{{ __('Skrip Video') }}</span>
                                    <div class="p-2.5 bg-zinc-50 dark:bg-white/5 rounded border border-zinc-200 dark:border-white/10 whitespace-pre-line text-zinc-600 dark:text-zinc-300">
                                        {{ $reviewingGroup->video_script_text }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif

                <flux:separator variant="subtle" />

                {{-- Catatan / Arahan DPL --}}
                <section class="space-y-2">
                    <flux:heading size="sm">{{ __('Catatan / Arahan Pembimbing') }}</flux:heading>
                    <flux:textarea wire:model="lrkReviewNote" rows="3" placeholder="{{ __('Tuliskan catatan arahan atau catatan revisi jika meminta perbaikan...') }}" />
                </section>

                {{-- Actions --}}
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                    <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                        <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>

                    @if($reviewingGroup->isLrkFinalized())
                        <flux:button wire:click="revertLrkToDraft" variant="danger" icon="arrow-uturn-left" class="w-full sm:w-auto">
                            {{ __('Batalkan Persetujuan') }}
                        </flux:button>
                    @else
                        <flux:button wire:click="requestLrkRevision" variant="danger" icon="arrow-path" class="w-full sm:w-auto">
                            {{ __('Minta Revisi') }}
                        </flux:button>
                        <flux:button wire:click="approveLrk" variant="primary" icon="check" class="w-full sm:w-auto">
                            {{ __('Setujui Dokumen LRK') }}
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
