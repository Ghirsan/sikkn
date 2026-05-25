<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Filter Bar --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau Prodi...') }}" size="sm" class="w-72" />
    </div>

    {{-- Students Table --}}
    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
                <flux:badge color="zinc">{{ $totalStudents }} {{ __('mahasiswa terdaftar') }}</flux:badge>
            </div>
        </div>

        @if($students->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="academic-cap" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Tidak Ada Data Mahasiswa') }}</flux:heading>
            </div>
        @else
            <flux:table :paginate="$students">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok / Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($students as $student)
                        <flux:table.row :key="$student->id">
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$student->name" :initials="$student->initials()" size="sm" />
                                <span class="font-medium">{{ $student->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $student->nim }}</flux:table.cell>
                            <flux:table.cell>
                                {{ $student->prodi }}
                                <div class="text-xs text-neutral-500">{{ $student->fakultas }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->group)
                                    <flux:badge size="sm" color="green" inset="top bottom">{{ $student->group->name }}</flux:badge>
                                    <div class="mt-1 text-xs text-neutral-400">{{ $student->group->period->name }}</div>
                                @else
                                    <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Belum Punya Kelompok') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
