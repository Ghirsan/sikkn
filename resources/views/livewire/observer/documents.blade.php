<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Dokumen Tim (LRK & LPK)') }}</flux:heading>
        <div class="flex items-center gap-3">
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari kelompok...') }}" size="sm" class="w-64" />
        </div>
    </div>

    <flux:card>
        @if($groups->isEmpty())
            <x-empty-state icon="document-text" :heading="__('Tidak Ada Data Kelompok')" />
        @else
            <flux:table :paginate="$groups">
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Periode') }}</flux:table.column>
                    <flux:table.column>{{ __('Anggota (Sesuai Scope)') }}</flux:table.column>
                    <flux:table.column>{{ __('Dokumen LRK') }}</flux:table.column>
                    <flux:table.column>{{ __('Dokumen LPK') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($groups as $group)
                        <flux:table.row :key="$group->id">
                            <flux:table.cell>
                                <div class="font-medium">{{ $group->name }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($group->period)
                                    {{ $group->period->year }} {{ $group->period->semester->value }}
                                @else
                                    -
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    // Count students from this group that match the observer's scope
                                    $user = auth()->user();
                                    $count = $group->students->filter(function($student) use ($user) {
                                        if ($user->hasRole(\App\Enums\UserRole::Prodi)) {
                                            return $student->study_program_id == $user->study_program_id;
                                        }
                                        if ($user->hasRole(\App\Enums\UserRole::Fakultas)) {
                                            return $student->faculty_id == $user->faculty_id;
                                        }
                                        return true;
                                    })->count();
                                @endphp
                                <flux:badge size="sm" color="zinc">{{ $count }} Mahasiswa</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($group->lrk_status && $group->lrk_status->value !== 'draft')
                                    <flux:badge size="sm" color="{{ $group->lrk_status->color() }}">{{ $group->lrk_status->label() }}</flux:badge>
                                @else
                                    <span class="text-xs text-neutral-400">Belum Ada</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($group->lpk_status && $group->lpk_status->value !== 'draft')
                                    <flux:badge size="sm" color="{{ $group->lpk_status->color() }}">{{ $group->lpk_status->label() }}</flux:badge>
                                @else
                                    <span class="text-xs text-neutral-400">Belum Ada</span>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>

