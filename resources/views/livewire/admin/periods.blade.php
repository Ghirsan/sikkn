<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Active Period Card --}}
    @if($activePeriod)
        <div class="rounded-xl border-2 border-green-300 bg-green-50/50 p-6 dark:border-green-700 dark:bg-green-900/10">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="calendar" class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-400">{{ __('Periode Aktif') }}</flux:text>
                    <flux:heading size="lg">{{ $activePeriod->name }} {{ $activePeriod->year }}</flux:heading>
                </div>
            </div>
            <flux:text class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                {{ __('Sedang berjalan dari ') }} {{ $activePeriod->start_date->translatedFormat('d F Y') }} {{ __(' hingga ') }} {{ $activePeriod->end_date->translatedFormat('d F Y') }}.
            </flux:text>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/50 p-6 dark:border-amber-700 dark:bg-amber-900/10">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="calendar" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-amber-600 dark:text-amber-400">{{ __('Periode Aktif') }}</flux:text>
                    <flux:heading size="lg">{{ __('Belum ada periode aktif') }}</flux:heading>
                </div>
            </div>
            <flux:text class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                {{ __('Buat periode KKN baru dan aktifkan untuk memulai siklus KKN.') }}
            </flux:text>
        </div>
    @endif

    {{-- Create Period Form --}}
    @if($isCreating)
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-6 dark:border-blue-800 dark:bg-blue-900/10">
            <flux:heading size="lg" class="mb-4">{{ __('Buat Periode Baru') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <flux:input wire:model="name" label="{{ __('Nama Periode') }}" placeholder="e.g. KKN Reguler" />
                <flux:input wire:model="year" label="{{ __('Tahun') }}" placeholder="e.g. 2026" type="number" />
                <flux:input wire:model="start_date" label="{{ __('Tanggal Mulai') }}" type="date" />
                <flux:input wire:model="end_date" label="{{ __('Tanggal Selesai') }}" type="date" />
            </div>
            <div class="mt-4 flex gap-2">
                <flux:button wire:click="createPeriod" variant="filled" size="sm">{{ __('Simpan Periode') }}</flux:button>
                <flux:button wire:click="$set('isCreating', false)" variant="ghost" size="sm">{{ __('Batal') }}</flux:button>
            </div>
        </div>
    @else
        <div class="flex flex-col items-end gap-2">
            @error('creation_failed')
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                    <flux:icon name="exclamation-circle" class="mr-1 inline size-4" />
                    {{ $message }}
                </div>
            @enderror
            <flux:button wire:click="startCreating" variant="filled" icon="plus">{{ __('Tambah Periode') }}</flux:button>
        </div>
    @endif

    {{-- Periods Table --}}
    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <flux:heading size="lg">{{ __('Daftar Periode') }}</flux:heading>
        </div>

        @if($periods->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="calendar" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Periode') }}</flux:heading>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama & Tahun') }}</flux:table.column>
                    <flux:table.column>{{ __('Rentang Tanggal') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($periods as $period)
                        <flux:table.row :key="$period->id">
                            <flux:table.cell variant="strong">{{ $period->name }} {{ $period->year }}</flux:table.cell>
                            <flux:table.cell>{{ $period->start_date->format('d M Y') }} - {{ $period->end_date->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$period->status->color()">{{ $period->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $period->groups_count }} {{ __('Kelompok') }}</flux:table.cell>
                            <flux:table.cell>
                                @if($period->status->value === 'inactive')
                                    <flux:button wire:click="toggleStatus({{ $period->id }}, 'active')" size="sm" variant="filled" color="green" icon="play">{{ __('Aktifkan') }}</flux:button>
                                @elseif($period->status->value === 'active')
                                    <flux:button wire:click="toggleStatus({{ $period->id }}, 'completed')" size="sm" variant="filled" color="amber" icon="check-circle">{{ __('Selesaikan') }}</flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
