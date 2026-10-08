<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Students Table --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
        <div class="flex flex-wrap items-center gap-3">
            <flux:badge color="zinc">{{ $totalStudents }} {{ __('mahasiswa') }}</flux:badge>
            
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" placeholder="{{ __('Semua Program Studi') }}" size="sm" class="w-48">
                <option value="">{{ __('Semua Program Studi') }}</option>
                @foreach($studyPrograms as $prodi)
                    <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                @endforeach
            </flux:select>
            @endif

            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau Prodi...') }}" size="sm" class="w-64" />
        </div>
    </div>

    <flux:card>
        @if($students->isEmpty())
            <x-empty-state icon="academic-cap" :heading="__('Tidak Ada Data Mahasiswa')" />
        @else
            <flux:table :paginate="$students">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Program Studi') }}</flux:table.column>
                    <flux:table.column>{{ __('Kontak') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok') }}</flux:table.column>
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
                                <div class="space-y-1 text-xs">
                                    @if($student->phone)
                                        <a
                                            href="{{ $student->whatsappUrl() ?? $student->phone }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center gap-1"
                                        >
                                            <img src="{{ asset('whatsapp.svg') }}" alt="" class="size-4 object-contain">
                                            <span>{{ $student->phone }}</span>
                                        </a>
                                    @else
                                        <span class="text-neutral-400">-</span>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($student->group)
                                    <flux:badge size="sm" color="green" inset="top bottom">{{ $student->group->name }}</flux:badge>
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

