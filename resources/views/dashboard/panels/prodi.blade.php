{{-- Prodi Dashboard Panel --}}
{{-- Prodi: Melihat daftar mahasiswanya serta program multidisiplin --}}
{{-- dan pengabdian masyarakat yang diusulkan oleh mahasiswa --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                <flux:icon name="academic-cap" class="size-5 text-purple-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Mahasiswa KKN') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Dari program studi ini') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <flux:icon name="light-bulb" class="size-5 text-blue-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Multidisiplin') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Diusulkan mahasiswa prodi') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                <flux:icon name="heart" class="size-5 text-green-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Pengabdian') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Program pengabdian masyarakat') }}</flux:text>
    </div>
</div>

{{-- Daftar Mahasiswa Prodi --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Mahasiswa KKN Program Studi') }}</flux:heading>
    </div>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Belum ada data mahasiswa KKN dari program studi ini.') }}
    </flux:text>
</div>

{{-- Program Kerja Mahasiswa --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja yang Diusulkan') }}</flux:heading>
    </div>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Program multidisiplin dan pengabdian masyarakat yang diusulkan mahasiswa prodi akan ditampilkan di sini.') }}
    </flux:text>
</div>
