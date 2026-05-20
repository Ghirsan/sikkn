<x-layouts::app :title="__('Logbook Mahasiswa')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Logbook Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Tinjau dan sahkan catatan harian kegiatan individu mahasiswa bimbingan.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Summary Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <flux:icon name="clock" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Menunggu Persetujuan') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <flux:icon name="check-circle" class="size-5 text-green-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Disetujui') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <flux:icon name="book-open" class="size-5 text-blue-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Entri') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input icon="magnifying-glass" placeholder="{{ __('Cari mahasiswa...') }}" size="sm" class="w-72" />
            <flux:select size="sm" placeholder="{{ __('Semua Status') }}" class="w-48">
                <flux:select.option>{{ __('Semua Status') }}</flux:select.option>
                <flux:select.option>{{ __('Menunggu Persetujuan') }}</flux:select.option>
                <flux:select.option>{{ __('Disetujui') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Logbook Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Catatan Harian Mahasiswa') }}</flux:heading>
            </div>

            {{-- Empty State --}}
            <div class="px-6 py-12 text-center">
                <flux:icon name="book-open" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Logbook') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Catatan harian kegiatan mahasiswa bimbingan akan tampil di sini setelah mereka mulai mengisi logbook.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
