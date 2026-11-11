<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Heading --}}
    <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>

    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="academic-cap" color="blue" :label="__('Total Mahasiswa')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Ditempatkan')" :value="$stats['assigned']" />
        <x-stat-card icon="clock" color="amber" :label="__('Belum Ditempatkan')" :value="$stats['unassigned']" />
    </div>

    {{-- Search bar (justify between) filters, buttons --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:w-72">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau Prodi...') }}" size="sm" class="w-full" />
        </div>
        <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
            @if(auth()->user()->hasRole(\App\Enums\UserRole::Fakultas))
            <flux:select wire:model.live="studyProgramId" size="sm" class="w-full sm:w-48">
                <flux:select.option value="">{{ __('Semua Program Studi') }}</flux:select.option>
                @foreach($studyPrograms as $prodi)
                    <flux:select.option value="{{ $prodi->id }}">{{ $prodi->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @endif
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
                                <div class="space-y-1.5 text-xs">
                                    @if($student->phone)
                                        <div>
                                            <flux:link
                                                href="{{ $student->whatsappUrl() ?? $student->phone }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                variant="subtle"
                                                class="inline-flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300"
                                                aria-label="{{ __('Hubungi :name melalui WhatsApp', ['name' => $student->name]) }}"
                                            >
                                                <img src="{{ asset('whatsapp.svg') }}" alt="" class="size-4 shrink-0 object-contain">
                                                <span>{{ $student->phone }}</span>
                                            </flux:link>
                                        </div>
                                    @endif
                                    @if($student->emergency_phone)
                                        <div>
                                            <flux:link
                                                href="{{ $student->whatsappUrl($student->emergency_phone) ?? $student->emergency_phone }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                variant="subtle"
                                                class="inline-flex items-center gap-1.5"
                                                aria-label="{{ __('Hubungi kontak darurat :name melalui WhatsApp', ['name' => $student->name]) }}"
                                            >
                                                <img src="{{ asset('whatsapp.svg') }}" alt="" class="size-3.5 shrink-0 object-contain">
                                                <span>
                                                    {{ __('Darurat') }}:
                                                    @if($student->emergency_name)
                                                        {{ $student->emergency_name }}
                                                        @if($student->emergency_relationship)
                                                            ({{ $student->emergency_relationship }})
                                                        @endif
                                                        -
                                                    @endif
                                                    {{ $student->emergency_phone }}
                                                </span>
                                            </flux:link>
                                        </div>
                                    @endif
                                    @if(! $student->phone && ! $student->emergency_phone)
                                        <flux:text variant="subtle" class="text-xs">-</flux:text>
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

