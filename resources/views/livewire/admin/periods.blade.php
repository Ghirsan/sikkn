<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Active Period Card --}}
    @if($activePeriod)
        <flux:callout variant="success" icon="calendar">
            <flux:callout.heading>Semester {{ $activePeriod->semester->value }} {{ $activePeriod->year }}</flux:callout.heading>
            <flux:callout.text>{{ __('Sedang berjalan dari ') }} {{ $activePeriod->start_date->translatedFormat('d F Y') }} {{ __(' hingga ') }} {{ $activePeriod->end_date->translatedFormat('d F Y') }}.</flux:callout.text>
        </flux:callout>
    @else
        <flux:callout variant="warning" icon="calendar">
            <flux:callout.heading>{{ __('Belum ada periode aktif') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Buat periode KKN baru dan aktifkan untuk memulai siklus KKN.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Create Period Form --}}
    @if($isCreating)
        <flux:card class="border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/10">
            <flux:heading size="lg" class="mb-4">{{ __('Buat Periode Baru') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <flux:select wire:model="semester" label="{{ __('Semester') }}">
                    @foreach(\App\Enums\Semester::cases() as $s)
                        <flux:select.option value="{{ $s->value }}">{{ $s->value }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="year" label="{{ __('Tahun') }}" placeholder="e.g. 2026" type="number" />
                <flux:input wire:model="start_date" label="{{ __('Tanggal Mulai') }}" type="date" />
                <flux:input wire:model="end_date" label="{{ __('Tanggal Selesai') }}" type="date" />
            </div>
            <div class="mt-4 flex gap-2">
                <flux:button wire:click="createPeriod" variant="filled" size="sm">{{ __('Simpan Periode') }}</flux:button>
                <flux:button wire:click="$set('isCreating', false)" variant="ghost" size="sm">{{ __('Batal') }}</flux:button>
            </div>
        </flux:card>
    @else
        <div class="flex flex-col items-end gap-2">
            <flux:button wire:click="startCreating" variant="filled" icon="plus" :disabled="(bool) $activePeriod">{{ __('Tambah Periode') }}</flux:button>
        </div>
    @endif

    {{-- Periods Table --}}
    <flux:card>
        <flux:heading size="lg">{{ __('Daftar Periode') }}</flux:heading>

        <flux:separator />

        @if($periods->isEmpty())
            <x-empty-state icon="calendar" :heading="__('Belum Ada Periode')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Semester & Tahun') }}</flux:table.column>
                    <flux:table.column>{{ __('Rentang Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($periods as $period)
                        <flux:table.row :key="$period->id">
                            <flux:table.cell variant="strong">Semester {{ $period->semester->value }} {{ $period->year }}</flux:table.cell>
                            <flux:table.cell>{{ $period->start_date->format('d M Y') }} - {{ $period->end_date->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$period->status->color()">{{ $period->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $period->groups_count }} {{ __('Kelompok') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
