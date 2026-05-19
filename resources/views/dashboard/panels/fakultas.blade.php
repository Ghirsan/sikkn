{{-- Fakultas Dashboard Panel --}}
{{-- Fakultas: Melihat daftar mahasiswa dikategorikan per prodi, --}}
{{-- serta program multidisiplin dan pengabdian masyarakat yang diusulkan --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-rose-50 dark:bg-rose-900/30">
                <flux:icon name="academic-cap" class="size-5 text-rose-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Mahasiswa KKN') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Seluruh fakultas') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                <flux:icon name="building-library" class="size-5 text-purple-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Studi') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Prodi dengan peserta KKN') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <flux:icon name="light-bulb" class="size-5 text-blue-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Multidisiplin') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Program multidisiplin') }}</flux:text>
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

{{-- Mahasiswa Per Prodi --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Mahasiswa KKN Per Program Studi') }}</flux:heading>
    </div>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Data mahasiswa KKN yang dikategorikan berdasarkan program studi akan ditampilkan di sini.') }}
    </flux:text>
</div>

{{-- Program Kerja Overview --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja yang Diusulkan') }}</flux:heading>
    </div>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Ringkasan program multidisiplin dan pengabdian masyarakat dari seluruh mahasiswa fakultas akan ditampilkan di sini.') }}
    </flux:text>
</div>
