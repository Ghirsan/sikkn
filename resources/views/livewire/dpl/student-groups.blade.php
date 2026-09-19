<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Group Filter --}}
    @if($allGroups->count() > 1)
        <div class="flex justify-end">
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-64 shrink-0">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-2">
        @if($allGroups->count() > 1 && empty($selectedGroupId))
            <x-stat-card icon="user-group" color="green" :label="__('Kelompok')" :value="$groups->count()" />
        @else
            <x-stat-card icon="map-pin" color="amber" :label="__('Lokasi KKN')" :value="$groups->first()->village ?? '-'" />
        @endif
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$totalStudents" />
    </div>
    
    {{-- Search Filter (All Groups) --}}
    @if(empty($selectedGroupId))
        <div class="flex items-center gap-4">
            <div class="w-full max-w-sm">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Cari mahasiswa, NIM, atau prodi...') }}" clearable />
            </div>
        </div>
    @endif
    
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
    @if($group->dpls->isNotEmpty() && !($search && empty($selectedGroupId)))
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
    
        {{-- Search Filter (Specific Group) --}}
        @if(!empty($selectedGroupId))
            <div class="mb-4 flex items-center gap-4">
                <div class="w-full max-w-sm">
                    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Cari mahasiswa, NIM, atau prodi...') }}" clearable />
                </div>
            </div>
        @endif
        
        {{-- Students Table --}}
        <flux:card>
            <flux:heading size="sm" class="mb-3">{{ __('Daftar Mahasiswa') }}</flux:heading>
            @if($group->students->isEmpty())
                <div class="py-4">
                    <x-empty-state icon="magnifying-glass" :heading="__('Tidak Ada Mahasiswa')" :description="__('Tidak ada mahasiswa dalam kelompok ini yang sesuai dengan kata kunci pencarian.')" />
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">{{ __('Mahasiswa') }}</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'nim'" :direction="$sortDirection" wire:click="sort('nim')">{{ __('NIM') }}</flux:table.column>
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
                                    @else
                                        <flux:button
                                            wire:click="confirmLeader({{ $group->id }}, {{ $student->id }}, '{{ addslashes($student->name) }}', '{{ addslashes($group->studentLeader?->name ?? '') }}')"
                                            size="sm"
                                            variant="ghost"
                                            icon="star"
                                        >{{ __('Tetapkan Ketua') }}</flux:button>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    @empty
        <flux:card>
            @if($search)
                <x-empty-state icon="magnifying-glass" :heading="__('Pencarian Tidak Ditemukan')" :description="__('Tidak ada mahasiswa yang sesuai dengan kata kunci pencarian Anda.')" />
            @else
                <x-empty-state icon="user-group" :heading="__('Belum Ada Kelompok')" :description="__('Belum ada kelompok bimbingan yang ditugaskan. Hubungi P2KKN untuk informasi penugasan.')" />
            @endif
        </flux:card>
    @endforelse

    {{-- Confirm Leader Modal --}}
    <flux:modal name="confirm-leader" @close="resetLeaderState" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $isReplacing ? __('Ganti Ketua Kelompok?') : __('Tetapkan Ketua Kelompok?') }}</flux:heading>
                <flux:text class="mt-2">
                    @if($isReplacing)
                        {{ __('Apakah Anda yakin ingin mengganti ketua dari :old menjadi :new?', ['old' => $pendingCurrentLeaderName, 'new' => $pendingStudentName]) }}
                    @else
                        {{ __('Apakah Anda yakin ingin menetapkan :name sebagai Ketua Kelompok?', ['name' => $pendingStudentName]) }}
                    @endif
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="setLeader" variant="primary">{{ $isReplacing ? __('Ganti Ketua') : __('Tetapkan') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
