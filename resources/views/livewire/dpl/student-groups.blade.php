<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Filters --}}
    <div class="flex items-center justify-between gap-4">
        <div class="w-full max-w-sm">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Cari mahasiswa, NIM, atau prodi...') }}" clearable />
        </div>
        
        @if($allGroups->count() > 1)
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-64 shrink-0">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-2">
        @if($allGroups->count() > 1 && empty($selectedGroupId))
            <x-stat-card icon="user-group" color="green" :label="__('Kelompok')" :value="$groups->count()" />
        @else
            <x-stat-card icon="map-pin" color="amber" :label="__('Lokasi KKN')" :value="$groups->first()->village ?? '-'" />
        @endif
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$totalStudents" />
    </div>

    {{-- Groups --}}
    @forelse($groups as $group)
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="lg">{{ $group->name }}</flux:heading>
                    <flux:badge color="purple" size="sm">{{ $group->type->value }}</flux:badge>
                </div>
                <flux:text>{{ $group->location }}</flux:text>
            </div>
            <div class="flex items-center gap-2">
                @if($group->period)
                    <flux:badge color="blue" size="sm">{{ $group->period->semester->value }} {{ $group->period->year }}</flux:badge>
                    <flux:badge :color="$group->period->status->color()" size="sm">{{ $group->period->status->label() }}</flux:badge>
                @endif
                <flux:badge color="zinc">{{ $group->students->count() }} {{ __('mahasiswa') }}</flux:badge>
            </div>
        </div>

        {{-- DPL List --}}
        @if($group->dpls->isNotEmpty())
            <flux:card>
                <flux:heading size="sm" class="mb-3">{{ __('Dosen KKN') }}</flux:heading>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($group->dpls as $dpl)
                        <div class="flex items-center gap-3 rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                            <flux:avatar :name="$dpl->name" :initials="$dpl->initials()" size="sm" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <flux:text variant="strong" class="truncate">{{ $dpl->name }}</flux:text>
                                    @if($group->lead_dpl_id === $dpl->id)
                                        <flux:badge color="green" size="sm">{{ __('Ketua') }}</flux:badge>
                                    @endif
                                </div>
                                <flux:text class="text-xs truncate">{{ $dpl->nip ?? '-' }}</flux:text>
                                @if($dpl->prodi)
                                    <flux:text class="text-xs truncate">{{ $dpl->prodi }}</flux:text>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endif

        {{-- Students Table --}}
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Daftar Mahasiswa') }}</flux:heading>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Fakultas') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($group->students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$student->name" :initials="$student->initials()" size="sm" />
                                <span class="font-medium">{{ $student->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $student->nim }}</flux:table.cell>
                            <flux:table.cell>{{ $student->prodi }}</flux:table.cell>
                            <flux:table.cell>{{ $student->fakultas ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($group->student_leader_id === $student->id)
                                    <flux:badge color="blue" size="sm">{{ __('Ketua Kelompok') }}</flux:badge>
                                @elseif(is_null($group->student_leader_id))
                                    <flux:modal.trigger name="confirm-leader-{{ $student->id }}">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="star"
                                        >{{ __('Tetapkan Ketua') }}</flux:button>
                                    </flux:modal.trigger>

                                    <flux:modal name="confirm-leader-{{ $student->id }}" class="min-w-[22rem]">
                                        <div class="space-y-6">
                                            <div>
                                                <flux:heading size="lg">{{ __('Tetapkan Ketua Kelompok?') }}</flux:heading>
                                                <flux:text class="mt-2">
                                                    {{ __('Apakah Anda yakin ingin menetapkan :name sebagai Ketua Kelompok? Keputusan ini bersifat permanen dan tidak dapat diubah.', ['name' => $student->name]) }}
                                                </flux:text>
                                            </div>
                                            <div class="flex gap-2">
                                                <flux:spacer />
                                                <flux:modal.close>
                                                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                                                </flux:modal.close>
                                                <flux:button wire:click="setLeader({{ $group->id }}, {{ $student->id }})" variant="primary">{{ __('Tetapkan') }}</flux:button>
                                            </div>
                                        </div>
                                    </flux:modal>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Anggota') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @empty
        <flux:card>
            <x-empty-state icon="user-group" :heading="__('Belum Ada Kelompok')" :description="__('Belum ada kelompok bimbingan yang ditugaskan. Hubungi P2KKN untuk informasi penugasan.')" />
        </flux:card>
    @endforelse
</div>
