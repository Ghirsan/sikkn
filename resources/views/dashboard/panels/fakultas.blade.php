{{-- Fakultas Dashboard Panel --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    {{-- Total Mahasiswa --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="users" class="size-8 text-rose-500" />
            <div>
                <flux:heading size="lg">{{ __('Mahasiswa') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Total mahasiswa KKN fakultas') }}</flux:text>
    </div>

    {{-- Total Prodi --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="building-library" class="size-8 text-purple-500" />
            <div>
                <flux:heading size="lg">{{ __('Program Studi') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Prodi yang terlibat KKN') }}</flux:text>
    </div>

    {{-- Total Kelompok --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="user-group" class="size-8 text-blue-500" />
            <div>
                <flux:heading size="lg">{{ __('Kelompok') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Total kelompok KKN') }}</flux:text>
    </div>

    {{-- Dokumen Final --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="check-badge" class="size-8 text-green-500" />
            <div>
                <flux:heading size="lg">{{ __('Dokumen Final') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen telah final') }}</flux:text>
    </div>
</div>

{{-- Quick Actions --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
    <div class="flex flex-wrap gap-3">
        <flux:button variant="filled" icon="chart-bar">{{ __('Laporan Fakultas') }}</flux:button>
        <flux:button variant="ghost" icon="building-library">{{ __('Daftar Prodi') }}</flux:button>
        <flux:button variant="ghost" icon="document-text">{{ __('Monitor Dokumen') }}</flux:button>
        <flux:button variant="ghost" icon="arrow-down-tray">{{ __('Export Laporan') }}</flux:button>
    </div>
</div>

{{-- Overview --}}
<div class="flex-1 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Ringkasan KKN Fakultas') }}</flux:heading>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Data ringkasan KKN seluruh fakultas akan ditampilkan di sini.') }}
    </flux:text>
</div>
