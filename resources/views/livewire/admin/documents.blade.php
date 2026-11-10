<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Summary Stats --}}
    <div class="grid auto-rows-min gap-4 sm:grid-cols-2 md:grid-cols-4">
        <x-stat-card icon="user-group" color="blue" :label="__('Total Kelompok')" :value="$stats['total']" />
        <x-stat-card icon="document-text" color="green" :label="__('LRK Disetujui')" :value="$stats['lrk_approved']" />
        <x-stat-card icon="document-check" color="purple" :label="__('LPK Disetujui')" :value="$stats['lpk_approved']" />
        <x-stat-card icon="check-badge" color="amber" :label="__('Dokumen Lengkap')" :value="$stats['completed']" />
    </div>

    {{-- Search and filters --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-80">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok, lokasi, atau DPL...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            <flux:select wire:model.live="periodId" size="sm" class="w-full sm:w-48" placeholder="{{ __('Semua Periode') }}">
                <flux:select.option value="">{{ __('Semua Periode') }}</flux:select.option>
                @foreach($periods as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->display_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="statusFilter" size="sm" class="w-full sm:w-48">
                <flux:select.option value="all">{{ __('Semua Status') }}</flux:select.option>
                <flux:select.option value="lrk_pending">{{ __('LRK Belum Disetujui') }}</flux:select.option>
                <flux:select.option value="lpk_pending">{{ __('LPK Belum Final') }}</flux:select.option>
                <flux:select.option value="completed">{{ __('Dokumen Lengkap') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <flux:card>
        @if($groups->isEmpty())
            <x-empty-state icon="document-text" :heading="__('Tidak Ada Data Dokumen')" :description="__('Belum ada data kelompok yang sesuai dengan kriteria pencarian dan filter saat ini.')" />
        @else
            <flux:table :paginate="$groups">
                <flux:table.columns>
                    <flux:table.column>{{ __('Kelompok & Anggota') }}</flux:table.column>
                    <flux:table.column>{{ __('Lokasi KKN') }}</flux:table.column>
                    <flux:table.column>{{ __('DPL Pembimbing') }}</flux:table.column>
                    <flux:table.column>{{ __('Dokumen LRK') }}</flux:table.column>
                    <flux:table.column>{{ __('Dokumen LPK') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($groups as $group)
                        <flux:table.row :key="$group->id">
                            {{-- Kelompok & Anggota --}}
                            <flux:table.cell>
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $group->name }}</div>
                                <div class="mt-1">
                                    <flux:badge size="xs" color="zinc" inset="top bottom">{{ $group->students->count() }} {{ __('Mahasiswa') }}</flux:badge>
                                </div>
                            </flux:table.cell>

                            {{-- Lokasi KKN --}}
                            <flux:table.cell>
                                <div class="text-sm text-zinc-800 dark:text-zinc-200">{{ $group->village }}, {{ $group->district }}</div>
                                @if($group->regency)
                                    <div class="text-xs text-zinc-500">{{ $group->regency }}</div>
                                @endif
                            </flux:table.cell>

                            {{-- DPL Pembimbing --}}
                            <flux:table.cell>
                                @if($group->dpls->isNotEmpty())
                                    <div class="space-y-1">
                                        @if($group->leadDpl)
                                            <div class="text-xs font-medium text-zinc-900 dark:text-zinc-100 flex items-center gap-1.5">
                                                <span>{{ $group->leadDpl->name }}</span>
                                                <flux:badge size="xs" color="blue" inset="top bottom">{{ __('Utama') }}</flux:badge>
                                            </div>
                                        @endif
                                        @foreach($group->dpls->where('id', '!=', $group->lead_dpl_id) as $dpl)
                                            <div class="text-xs text-zinc-600 dark:text-zinc-400">{{ $dpl->name }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">{{ __('Belum Ditugaskan') }}</span>
                                @endif
                            </flux:table.cell>

                            {{-- Dokumen LRK --}}
                            <flux:table.cell>
                                <div class="space-y-1.5">
                                    <div>
                                        @if($group->isLrkFinalized())
                                            <flux:badge size="sm" color="green" inset="top bottom">{{ __('Disetujui') }}</flux:badge>
                                        @elseif($group->lrk_status === \App\Enums\DocumentStatus::Submitted)
                                            <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Menunggu Review') }}</flux:badge>
                                        @elseif($group->lrk_status === \App\Enums\DocumentStatus::NeedsRevision)
                                            <flux:badge size="sm" color="red" inset="top bottom">{{ __('Perlu Revisi') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Draf') }}</flux:badge>
                                        @endif
                                    </div>
                                    <div>
                                        @if($group->isLrkFinalized())
                                            <flux:button :href="URL::signedRoute('lrk.pdf', $group)" target="_blank" size="xs" variant="primary" icon="arrow-down-tray">
                                                {{ __('Unduh LRK') }}
                                            </flux:button>
                                        @else
                                            <flux:button :href="URL::signedRoute('lrk.preview', $group)" target="_blank" size="xs" variant="ghost" icon="arrow-top-right-on-square">
                                                {{ __('Pratinjau LRK') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>

                            {{-- Dokumen LPK --}}
                            <flux:table.cell>
                                <div class="space-y-1.5">
                                    <div>
                                        @if($group->isLpkFinalized())
                                            <flux:badge size="sm" color="green" inset="top bottom">{{ __('Final') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Dalam Proses') }}</flux:badge>
                                        @endif
                                    </div>
                                    <div>
                                        @if($group->isLpkFinalized())
                                            <flux:button :href="URL::signedRoute('lpk.pdf', $group)" target="_blank" size="xs" variant="primary" icon="arrow-down-tray">
                                                {{ __('Unduh LPK') }}
                                            </flux:button>
                                        @else
                                            <flux:button :href="URL::signedRoute('lpk.preview', $group)" target="_blank" size="xs" variant="ghost" icon="arrow-top-right-on-square">
                                                {{ __('Pratinjau LPK') }}
                                            </flux:button>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
