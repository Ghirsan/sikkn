<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Active Period Card --}}
    @if($activePeriod)
        <flux:callout variant="success" icon="calendar">
            <flux:callout.heading>{{ $activePeriod->display_name }}</flux:callout.heading>
            <flux:callout.text>{{ __('Sedang berjalan dari ') }} {{ $activePeriod->start_date->translatedFormat('d F Y') }} {{ __(' hingga ') }} {{ $activePeriod->end_date->translatedFormat('d F Y') }}.</flux:callout.text>
        </flux:callout>
    @else
        <flux:callout variant="warning" icon="calendar">
            <flux:callout.heading>{{ __('Belum ada periode aktif') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Buat periode KKN baru dan aktifkan untuk memulai siklus KKN.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Create Period Action --}}
    <div class="flex flex-col items-end gap-2">
        <flux:button wire:click="startCreating" variant="filled" icon="plus" :disabled="(bool) $activePeriod">{{ __('Tambah Periode') }}</flux:button>
    </div>

    {{-- Periods Table --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Daftar Periode') }}</flux:heading>
    </div>

    <flux:card>
        @if($periods->isEmpty())
            <x-empty-state icon="calendar" :heading="__('Belum Ada Periode')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Periode') }}</flux:table.column>
                    <flux:table.column>{{ __('Semester & Tahun') }}</flux:table.column>
                    <flux:table.column>{{ __('Rentang Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($periods as $period)
                        <flux:table.row :key="$period->id">
                            <flux:table.cell variant="strong">{{ $period->name ?: '-' }}</flux:table.cell>
                            <flux:table.cell>Semester {{ $period->semester->value }} {{ $period->year }}</flux:table.cell>
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

    {{-- Create Period Modal --}}
    <flux:modal name="period-modal" class="md:w-[40rem]">
        <form wire:submit.prevent="createPeriod">
            <flux:heading size="lg">{{ __('Buat Periode Baru') }}</flux:heading>
            
            <div class="space-y-6 my-4">
                <flux:input wire:model="name" label="{{ __('Nama Periode') }}" placeholder="KKN TIM I TA 2026/2027" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="semester" label="{{ __('Semester') }}">
                        @foreach(\App\Enums\Semester::cases() as $s)
                            <flux:select.option value="{{ $s->value }}">{{ $s->value }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:input wire:model.live="year" label="{{ __('Tahun') }}" placeholder="e.g. 2026" type="number" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input wire:model="start_date" label="{{ __('Tanggal Mulai') }}" type="date" min="{{ $year ? $year.'-01-01' : '' }}" max="{{ $year ? $year.'-12-31' : '' }}" />
                    <flux:input wire:model="end_date" label="{{ __('Tanggal Selesai') }}" type="date" min="{{ $year ? $year.'-01-01' : '' }}" max="{{ $year ? $year.'-12-31' : '' }}" />
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
