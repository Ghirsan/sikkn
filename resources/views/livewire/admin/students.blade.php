<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Students Table --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
        <div class="flex items-center gap-3">
            <flux:badge color="zinc">{{ $totalStudents }} {{ __('mahasiswa terdaftar') }}</flux:badge>
            <flux:select wire:model.live="periodId" size="sm" class="w-48" placeholder="{{ __('Semua Periode') }}">
                <flux:select.option value="">{{ __('Semua Periode') }}</flux:select.option>
                @foreach($periods as $p)
                    <flux:select.option value="{{ $p->id }}">{{ $p->display_name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:input wire:model.live="search" icon="magnifying-glass" placeholder="{{ __('Cari nama, NIM, atau Prodi...') }}" size="sm" class="w-72" />
            <flux:button variant="ghost" size="sm" icon="arrow-up-tray">{{ __('Import Excel') }}</flux:button>
            <flux:button variant="filled" size="sm" icon="plus">{{ __('Tambah Peserta') }}</flux:button>
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
                                <div class="space-y-1 text-xs">
                                    @if($student->phone)
                                        <a
                                            href="{{ $student->whatsappUrl() ?? $student->phone }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="{{ __('Hubungi :name melalui WhatsApp', ['name' => $student->name]) }}"
                                        >
                                            <img src="{{ asset('whatsapp.svg') }}" alt="" class="block size-5 object-contain">
                                        </a>
                                    @endif
                                    @if($student->emergency_phone)
                                        <a  target="_blank" rel="noopener noreferrer" class="block text-amber-600 underline underline-offset-2" aria-label="{{ __('Hubungi kontak darurat :name melalui WhatsApp', ['name' => $student->name]) }}">
                                            {{ __('Darurat') }}: {{ $student->emergency_phone }}
                                        </a>
                                        <a
                                            href="{{ $student->whatsappUrl($student->emergency_phone) ?? $student->emergency_phone }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            aria-label="{{ __('Hubungi :name melalui WhatsApp', ['name' => $student->name]) }}"
                                        >
                                            <img src="{{ asset('whatsapp.svg') }}" alt="" class="block size-5 object-contain">
                                        </a>
                                    @endif
                                    @if(! $student->phone && ! $student->emergency_phone)
                                        <span class="text-neutral-400">-</span>
                                    @endif
                                </div>
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
