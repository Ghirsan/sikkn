<x-layouts::app :title="__('Mahasiswa Bimbingan')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Mahasiswa Bimbingan') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Kelompok dan mahasiswa KKN di bawah bimbingan Anda.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Summary Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <flux:icon name="user-group" class="size-5 text-green-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Kelompok') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <flux:icon name="academic-cap" class="size-5 text-blue-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Mahasiswa') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <flux:icon name="map-pin" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Lokasi KKN') }}</flux:text>
                        <flux:text class="text-2xl font-bold">-</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Group Card --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Kelompok Bimbingan') }}</flux:heading>
            </div>

            {{-- Empty State --}}
            <div class="px-6 py-12 text-center">
                <flux:icon name="user-group" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Kelompok') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Anda belum ditugaskan sebagai pembimbing kelompok KKN. Hubungi P2KKN untuk informasi penugasan.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
