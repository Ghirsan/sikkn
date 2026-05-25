<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="user-group" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Kelompok') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $groups->count() }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="academic-cap" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Total Mahasiswa') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $totalStudents }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="map-pin" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Lokasi KKN') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $groups->count() > 0 ? $groups->first()->village : '-' }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Groups --}}
    @forelse($groups as $group)
        <flux:card class="!p-0">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ $group->name }}</flux:heading>
                        <flux:text class="text-sm text-neutral-500">{{ $group->location }}</flux:text>
                    </div>
                    <flux:badge color="zinc">{{ $group->students->count() }} {{ __('mahasiswa') }}</flux:badge>
                </div>
            </div>
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
        <flux:card class="text-center">
            <flux:icon name="user-group" class="mx-auto size-12 text-neutral-300" />
            <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Kelompok') }}</flux:heading>
        </flux:card>
    @endforelse
</div>
