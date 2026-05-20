<x-layouts::app :title="__('Logbook Harian')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Logbook Harian') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Catat aktivitas harian KKN Anda di lapangan.') }}</flux:text>
            </div>
            <flux:button variant="filled" icon="plus">{{ __('Tambah Entri') }}</flux:button>
        </div>

        <flux:separator />

        {{-- Summary Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
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
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <flux:icon name="check-circle" class="size-5 text-green-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Disetujui DPL') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <flux:icon name="clock" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Menunggu') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Logbook Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Catatan Harian') }}</flux:heading>
            </div>
            <div class="px-6 py-12 text-center">
                <flux:icon name="book-open" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Catatan') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Mulai catat aktivitas harian KKN Anda. Setiap entri memuat tanggal, deskripsi kegiatan, durasi kerja, dan dokumentasi foto.') }}
                </flux:text>
                <div class="mt-6">
                    <flux:button variant="filled" icon="plus">{{ __('Tambah Entri Baru') }}</flux:button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
