{{-- Prodi Dashboard Panel --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-3">
    {{-- Mahasiswa Prodi --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="academic-cap" class="size-8 text-purple-500" />
            <div>
                <flux:heading size="lg">{{ __('Mahasiswa') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Mahasiswa KKN dari prodi') }}</flux:text>
    </div>

    {{-- Dokumen Prodi --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="document-text" class="size-8 text-blue-500" />
            <div>
                <flux:heading size="lg">{{ __('Total Dokumen') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen mahasiswa prodi') }}</flux:text>
    </div>

    {{-- DPL Prodi --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="user-circle" class="size-8 text-green-500" />
            <div>
                <flux:heading size="lg">{{ __('DPL') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('DPL dari program studi') }}</flux:text>
    </div>
</div>

{{-- Quick Actions --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
    <div class="flex flex-wrap gap-3">
        <flux:button variant="filled" icon="chart-bar">{{ __('Laporan Prodi') }}</flux:button>
        <flux:button variant="ghost" icon="academic-cap">{{ __('Daftar Mahasiswa') }}</flux:button>
        <flux:button variant="ghost" icon="document-text">{{ __('Monitor Dokumen') }}</flux:button>
    </div>
</div>

{{-- Overview --}}
<div class="flex-1 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Ringkasan Progress KKN') }}</flux:heading>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Data ringkasan progress KKN program studi akan ditampilkan di sini.') }}
    </flux:text>
</div>
