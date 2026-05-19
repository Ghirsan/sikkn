{{-- P2KKN Admin Dashboard Panel --}}
{{-- P2KKN: Melihat rancangan dokumen tiap mahasiswa & tim beserta status (lifecycle), --}}
{{-- melihat daftar DPL dan mahasiswa peserta KKN --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                <flux:icon name="calendar" class="size-5 text-amber-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Periode Aktif') }}</flux:text>
                <flux:text class="text-2xl font-bold">-</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Periode KKN berjalan') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <flux:icon name="academic-cap" class="size-5 text-blue-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Peserta KKN') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Total mahasiswa terdaftar') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                <flux:icon name="user-circle" class="size-5 text-green-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('DPL') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Dosen pembimbing lapangan') }}</flux:text>
    </div>

    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                <flux:icon name="user-group" class="size-5 text-purple-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Kelompok') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400">{{ __('Total tim KKN') }}</flux:text>
    </div>
</div>

<div class="grid gap-4 md:grid-cols-2">
    {{-- Document Lifecycle Overview --}}
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Status Dokumen') }}</flux:heading>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">{{ __('Draft') }}</flux:text>
                <flux:text class="text-sm font-bold">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">{{ __('Disubmit') }}</flux:text>
                <flux:text class="text-sm font-bold">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">{{ __('Revisi DPL') }}</flux:text>
                <flux:text class="text-sm font-bold">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">{{ __('Disetujui DPL') }}</flux:text>
                <flux:text class="text-sm font-bold">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">{{ __('Menunggu Verifikasi') }}</flux:text>
                <flux:text class="text-sm font-bold text-amber-500">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text class="text-sm">{{ __('Final') }}</flux:text>
                <flux:text class="text-sm font-bold text-green-500">0</flux:text>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button variant="filled" icon="shield-check" class="justify-start">{{ __('Verifikasi Dokumen') }}</flux:button>
            <flux:button variant="ghost" icon="document-text" class="justify-start">{{ __('Lihat Rancangan Dokumen') }}</flux:button>
            <flux:button variant="ghost" icon="academic-cap" class="justify-start">{{ __('Daftar Peserta KKN') }}</flux:button>
            <flux:button variant="ghost" icon="user-circle" class="justify-start">{{ __('Daftar DPL') }}</flux:button>
            <flux:button variant="ghost" icon="user-group" class="justify-start">{{ __('Kelola Kelompok') }}</flux:button>
            <flux:button variant="ghost" icon="calendar" class="justify-start">{{ __('Kelola Periode') }}</flux:button>
        </div>
    </div>
</div>

{{-- DPL & Mahasiswa Lists --}}
<div class="grid gap-4 md:grid-cols-2">
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Daftar DPL') }}</flux:heading>
        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
            {{ __('Belum ada DPL terdaftar pada periode ini.') }}
        </flux:text>
    </div>
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Daftar Peserta KKN') }}</flux:heading>
        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
            {{ __('Belum ada mahasiswa peserta KKN pada periode ini.') }}
        </flux:text>
    </div>
</div>

{{-- Rancangan Dokumen Per Tim --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Rancangan Dokumen Per Tim') }}</flux:heading>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Rancangan dokumen tiap tim KKN beserta status lifecycle akan ditampilkan di sini.') }}
    </flux:text>
</div>
