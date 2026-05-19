{{-- DPL Dashboard Panel --}}
{{-- DPL: Melihat daftar mahasiswa bimbingan, approve/revisi program, --}}
{{-- melihat dokumen lengkap tim, form penilaian setelah periode selesai --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    {{-- Mahasiswa Bimbingan --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                <flux:icon name="academic-cap" class="size-5 text-green-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Mahasiswa') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('Total mahasiswa bimbingan') }}</flux:text>
    </div>

    {{-- Program Perlu Review --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                <flux:icon name="light-bulb" class="size-5 text-amber-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Review') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('Program menunggu approve') }}</flux:text>
    </div>

    {{-- Dokumen Tim --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <flux:icon name="document-text" class="size-5 text-blue-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Dokumen Tim') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('LRK & LPK kelompok') }}</flux:text>
    </div>

    {{-- Penilaian --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                <flux:icon name="clipboard-document-check" class="size-5 text-purple-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Penilaian') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('Mahasiswa sudah dinilai') }}</flux:text>
    </div>
</div>

{{-- Two-Column Layout --}}
<div class="grid gap-4 md:grid-cols-2">
    {{-- Program Perlu Review --}}
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="lg">{{ __('Program Menunggu Review') }}</flux:heading>
            <flux:badge color="amber" size="sm">0</flux:badge>
        </div>

        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
            {{ __('Tidak ada program yang menunggu review saat ini.') }}
        </flux:text>
    </div>

    {{-- Quick Actions --}}
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button variant="filled" icon="light-bulb" class="justify-start">{{ __('Review Program Mahasiswa') }}</flux:button>
            <flux:button variant="ghost" icon="document-text" class="justify-start">{{ __('Lihat Dokumen Tim') }}</flux:button>
            <flux:button variant="ghost" icon="user-group" class="justify-start">{{ __('Daftar Mahasiswa Bimbingan') }}</flux:button>
            <flux:button variant="ghost" icon="clipboard-document-check" class="justify-start">{{ __('Penilaian Mahasiswa') }}</flux:button>
        </div>
    </div>
</div>

{{-- Kelompok Bimbingan --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Kelompok Bimbingan') }}</flux:heading>

    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Belum ada kelompok bimbingan yang ditugaskan. Hubungi P2KKN untuk informasi penugasan.') }}
    </flux:text>
</div>

{{-- Dokumen Tim Overview --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Status Dokumen Tim') }}</flux:heading>

    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Dokumen lengkap tim (LRK/LPK) akan tampil di sini setelah mahasiswa mengisi form program kerja.') }}
    </flux:text>
</div>
