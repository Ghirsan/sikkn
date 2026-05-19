<x-layouts::app :title="__('Peserta KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Peserta KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Database mahasiswa peserta Kuliah Kerja Nyata.') }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button variant="ghost" icon="arrow-up-tray">{{ __('Import Excel') }}</flux:button>
                <flux:button variant="filled" icon="plus">{{ __('Tambah Peserta') }}</flux:button>
            </div>
        </div>

        <flux:separator />

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIM...') }}" size="sm" class="w-72" />
            <flux:select size="sm" placeholder="{{ __('Semua Prodi') }}" class="w-48">
                <flux:select.option>{{ __('Semua Prodi') }}</flux:select.option>
            </flux:select>
            <flux:select size="sm" placeholder="{{ __('Semua Kelompok') }}" class="w-48">
                <flux:select.option>{{ __('Semua Kelompok') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Students Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Daftar Mahasiswa') }}</flux:heading>
                    <flux:badge color="zinc">{{ __('0 mahasiswa') }}</flux:badge>
                </div>
            </div>

            {{-- Empty State --}}
            <div class="px-6 py-12 text-center">
                <flux:icon name="academic-cap" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Peserta KKN') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Tambahkan mahasiswa peserta KKN secara manual atau melalui import file Excel/CSV.') }}
                </flux:text>
                <div class="mt-6 flex justify-center gap-3">
                    <flux:button variant="ghost" icon="arrow-up-tray">{{ __('Import Data') }}</flux:button>
                    <flux:button variant="filled" icon="plus">{{ __('Tambah Manual') }}</flux:button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
