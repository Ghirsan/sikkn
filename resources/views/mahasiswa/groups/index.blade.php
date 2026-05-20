<x-layouts::app :title="__('Kelompok Saya')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Kelompok Saya') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Informasi tim KKN dan lokasi penugasan Anda.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Location Card --}}
        <div class="rounded-xl border-2 border-dashed border-green-300 bg-green-50/50 p-6 dark:border-green-700 dark:bg-green-900/10">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="map-pin" class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-400">{{ __('Lokasi Penugasan KKN') }}</flux:text>
                    <flux:heading size="lg">{{ __('Belum ditentukan') }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        {{ __('Informasi lokasi (Desa, Kecamatan, Kabupaten) akan tampil setelah Anda ditugaskan ke kelompok.') }}
                    </flux:text>
                </div>
            </div>
        </div>

        {{-- DPL Info --}}
        <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                    <flux:icon name="user-circle" class="size-5 text-blue-500" />
                </div>
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-neutral-500 dark:text-neutral-400">{{ __('Dosen Pembimbing Lapangan') }}</flux:text>
                    <flux:heading size="lg">{{ __('Belum ditugaskan') }}</flux:heading>
                </div>
            </div>
        </div>

        {{-- Team Members --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Anggota Kelompok') }}</flux:heading>
                    <flux:badge color="zinc">{{ __('0 anggota') }}</flux:badge>
                </div>
            </div>
            <div class="px-6 py-12 text-center">
                <flux:icon name="user-group" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Kelompok') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Anda belum tergabung dalam kelompok KKN. Hubungi P2KKN untuk informasi penugasan.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
