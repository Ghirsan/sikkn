<x-layouts::app :title="__('Program Kerja')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pantau usulan program kerja KKN mahasiswa Program Studi Anda.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Status Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-zinc-700">
                        <flux:icon name="pencil-square" class="size-5 text-neutral-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Draft') }}</flux:text>
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
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Direview DPL') }}</flux:text>
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
                        <flux:icon name="document-check" class="size-5 text-blue-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('PDF Final') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input icon="magnifying-glass" placeholder="{{ __('Cari program atau mahasiswa...') }}" size="sm" class="w-72" />
            <flux:select size="sm" placeholder="{{ __('Semua Status') }}" class="w-48">
                <flux:select.option>{{ __('Semua Status') }}</flux:select.option>
                <flux:select.option>{{ __('Draft') }}</flux:select.option>
                <flux:select.option>{{ __('Direview DPL') }}</flux:select.option>
                <flux:select.option>{{ __('Disetujui DPL') }}</flux:select.option>
            </flux:select>
            <flux:select size="sm" placeholder="{{ __('Semua Jenis') }}" class="w-48">
                <flux:select.option>{{ __('Semua Jenis') }}</flux:select.option>
                <flux:select.option>{{ __('Multidisiplin') }}</flux:select.option>
                <flux:select.option>{{ __('Pengabdian Masyarakat') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Programs Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Daftar Program Kerja') }}</flux:heading>
            </div>
            <div class="px-6 py-12 text-center">
                <flux:icon name="light-bulb" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Program Kerja') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Program kerja yang diusulkan mahasiswa Program Studi Anda akan tampil di sini.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
