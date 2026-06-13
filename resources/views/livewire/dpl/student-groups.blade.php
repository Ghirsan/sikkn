<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="user-group" color="green" :label="__('Kelompok')" :value="$groups->count()" />
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$totalStudents" />
        <x-stat-card icon="map-pin" color="amber" :label="__('Lokasi KKN')" :value="$groups->count() > 0 ? $groups->first()->village : '-'" />
    </div>

    {{-- Groups --}}
    @forelse($groups as $group)
        <div class="mb-4 flex items-center justify-between">
            <div>
                <flux:heading size="lg">{{ $group->name }}</flux:heading>
                <flux:text>{{ $group->location }}</flux:text>
            </div>
            <flux:badge color="zinc">{{ $group->students->count() }} {{ __('mahasiswa') }}</flux:badge>
        </div>

        <flux:card class="mb-8">

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
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
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    @empty
        <flux:card>
            <x-empty-state icon="user-group" :heading="__('Belum Ada Kelompok')" />
        </flux:card>
    @endforelse
</div>
