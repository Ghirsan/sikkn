{{-- P2KKN Admin Dashboard Panel --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    {{-- Total Mahasiswa --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="users" class="size-8 text-blue-500" />
            <div>
                <flux:heading size="lg">{{ __('Mahasiswa') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Total mahasiswa KKN') }}</flux:text>
    </div>

    {{-- Total Kelompok --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="user-group" class="size-8 text-green-500" />
            <div>
                <flux:heading size="lg">{{ __('Kelompok') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Total kelompok KKN') }}</flux:text>
    </div>

    {{-- Perlu Verifikasi --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="shield-check" class="size-8 text-amber-500" />
            <div>
                <flux:heading size="lg">{{ __('Perlu Verifikasi') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen menunggu verifikasi') }}</flux:text>
    </div>

    {{-- Periode Aktif --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="calendar" class="size-8 text-purple-500" />
            <div>
                <flux:heading size="lg">{{ __('Periode') }}</flux:heading>
                <flux:text class="text-2xl font-bold">-</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Periode KKN aktif') }}</flux:text>
    </div>
</div>

{{-- Quick Actions --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
    <div class="flex flex-wrap gap-3">
        <flux:button variant="filled" icon="shield-check">{{ __('Verifikasi Dokumen') }}</flux:button>
        <flux:button variant="ghost" icon="user-group">{{ __('Kelola Kelompok') }}</flux:button>
        <flux:button variant="ghost" icon="calendar">{{ __('Kelola Periode') }}</flux:button>
        <flux:button variant="ghost" icon="users">{{ __('Kelola Pengguna') }}</flux:button>
    </div>
</div>

{{-- Pending Verifications --}}
<div class="flex-1 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Dokumen Menunggu Verifikasi') }}</flux:heading>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Tidak ada dokumen yang menunggu verifikasi saat ini.') }}
    </flux:text>
</div>
