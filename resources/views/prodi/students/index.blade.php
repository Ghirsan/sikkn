<x-layouts::app :title="__('Mahasiswa KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Mahasiswa KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pemantauan keikutsertaan mahasiswa Program Studi dalam KKN.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Summary Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                        <flux:icon name="academic-cap" class="size-5 text-purple-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Peserta Prodi') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <flux:icon name="user-group" class="size-5 text-green-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Sudah Berkelompok') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <flux:icon name="clipboard-document-check" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Sudah Dinilai') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIM...') }}" size="sm" class="w-72" />
            <flux:select size="sm" placeholder="{{ __('Semua Status') }}" class="w-48">
                <flux:select.option>{{ __('Semua Status') }}</flux:select.option>
                <flux:select.option>{{ __('Sudah Berkelompok') }}</flux:select.option>
                <flux:select.option>{{ __('Belum Berkelompok') }}</flux:select.option>
                <flux:select.option>{{ __('Sudah Dinilai') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Students Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Daftar Mahasiswa KKN') }}</flux:heading>
                    <flux:badge color="zinc">{{ __('0 mahasiswa') }}</flux:badge>
                </div>
            </div>
            <div class="px-6 py-12 text-center">
                <flux:icon name="academic-cap" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Data Mahasiswa') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Data mahasiswa KKN dari Program Studi Anda akan tampil di sini setelah periode KKN aktif.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
