<x-layouts::app :title="__('Periode KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Periode KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Kelola siklus waktu penyelenggaraan KKN akademik.') }}</flux:text>
            </div>
            <flux:button variant="filled" icon="plus">{{ __('Tambah Periode') }}</flux:button>
        </div>

        <flux:separator />

        {{-- Active Period Card --}}
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

        {{-- Periods Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Daftar Periode') }}</flux:heading>
                    <flux:input icon="magnifying-glass" placeholder="{{ __('Cari periode...') }}" size="sm" class="w-64" />
                </div>
            </div>

            {{-- Empty State --}}
            <div class="px-6 py-12 text-center">
                <flux:icon name="calendar" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Periode') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Mulai dengan membuat periode KKN pertama untuk mengatur siklus akademik.') }}
                </flux:text>
                <div class="mt-6">
                    <flux:button variant="filled" icon="plus">{{ __('Buat Periode Baru') }}</flux:button>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
