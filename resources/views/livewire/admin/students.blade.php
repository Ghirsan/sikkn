<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Students Table --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
            <div class="flex items-center gap-3">
                <flux:badge color="zinc">{{ $totalStudents }} {{ __('mahasiswa terdaftar') }}</flux:badge>
                <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau Prodi...') }}" size="sm" class="w-72" />
                <flux:button variant="ghost" size="sm" icon="arrow-up-tray">{{ __('Import Excel') }}</flux:button>
                <flux:button variant="filled" size="sm" icon="plus">{{ __('Tambah Peserta') }}</flux:button>
            </div>
        </div>

        <flux:separator />

        @if($students->isEmpty())
            <x-empty-state icon="academic-cap" :heading="__('Tidak Ada Data Mahasiswa')" />
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
                                    <div class="mt-1 text-xs text-neutral-400">Semester {{ $student->group->period->semester->value }} {{ $student->group->period->year }}</div>
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
