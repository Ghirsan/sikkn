{{-- Mahasiswa Dashboard Panel --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-3">
    {{-- Dokumen Saya --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="document-text" class="size-8 text-blue-500" />
            <div>
                <flux:heading size="lg">{{ __('Dokumen Saya') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Total dokumen yang telah dibuat') }}</flux:text>
    </div>

    {{-- Status Pengerjaan --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="clock" class="size-8 text-amber-500" />
            <div>
                <flux:heading size="lg">{{ __('Menunggu Review') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen menunggu review DPL') }}</flux:text>
    </div>

    {{-- Dokumen Final --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="check-circle" class="size-8 text-green-500" />
            <div>
                <flux:heading size="lg">{{ __('Disetujui') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen sudah disetujui') }}</flux:text>
    </div>
</div>

{{-- Quick Actions --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
    <div class="flex flex-wrap gap-3">
        <flux:button variant="filled" icon="plus">{{ __('Buat Catatan Harian') }}</flux:button>
        <flux:button variant="ghost" icon="document-text">{{ __('Lihat Dokumen') }}</flux:button>
        <flux:button variant="ghost" icon="user-group">{{ __('Info Kelompok') }}</flux:button>
    </div>
</div>

{{-- Recent Activity --}}
<div class="flex-1 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Aktivitas Terbaru') }}</flux:heading>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Belum ada aktivitas. Mulai dengan membuat dokumen pertama Anda.') }}
    </flux:text>
</div>
