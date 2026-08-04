<div class="flex h-full w-full flex-1 flex-col gap-6">
    @forelse($groupData as $data)
        {{-- Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">{{ $data->group->name }}</flux:heading>
                <flux:text>{{ $data->group->location }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                <flux:badge :color="$data->lrkReady ? 'green' : 'zinc'">{{ $data->lrkReady ? __('Siap PDF') : $data->approvedCount.'/'.$data->totalPrograms.' '.__('approved') }}</flux:badge>
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
                    @if($data->lrkReady)
                        <flux:badge size="sm" color="green">{{ __('Siap Cetak') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">{{ __('Belum Siap') }}</flux:badge>
                    @endif
                </div>
                @if($data->isLrkLocked)
                    <flux:callout variant="success" icon="lock-closed" class="mb-3">
                        <flux:callout.text>{{ __('LRK telah dikunci dan tidak dapat diedit.') }}</flux:callout.text>
                    </flux:callout>
                @endif
                @if($data->lrkReady)
                    <flux:button :href="route('lrk.pdf', $data->group)" target="_blank" variant="filled" size="sm" icon="arrow-down-tray" class="w-full justify-center">
                        {{ __('Download LRK PDF') }}
                    </flux:button>
                @endif
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
                        <flux:badge size="sm" color="green">{{ __('Siap Cetak') }}</flux:badge>
                    @else
                        <flux:badge size="sm" color="zinc">{{ __('Belum Siap') }}</flux:badge>
                    @endif
                </div>
                @if($data->isLpkLocked)
                    <flux:callout variant="success" icon="lock-closed" class="mb-3">
                        <flux:callout.text>{{ __('LPK telah dikunci dan tidak dapat diedit.') }}</flux:callout.text>
                    </flux:callout>
                    <flux:button :href="route('lpk.pdf', $data->group)" target="_blank" variant="filled" size="sm" icon="arrow-down-tray" class="w-full justify-center">
                        {{ __('Download LPK PDF') }}
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
                                    <flux:badge size="sm" color="zinc">{{ $participant->program->type->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>{{ $participant->student?->name ?? __('Kelompok') }}</flux:table.cell>
                                <flux:table.cell>
                                    @php
                                        $statusColor = match($participant->status->value) {
                                            'approved' => 'green',
                                            'submitted' => 'amber',
                                            'needs_revision' => 'red',
                                            default => 'zinc',
                                        };
                                        $statusLabel = match($participant->status->value) {
                                            'approved' => __('Disetujui'),
                                            'submitted' => __('Diajukan'),
                                            'needs_revision' => __('Revisi'),
                                            default => __('Draft'),
                                        };
                                    @endphp
                                    <flux:badge size="sm" :color="$statusColor" inset="top bottom">{{ $statusLabel }}</flux:badge>
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
</div>
