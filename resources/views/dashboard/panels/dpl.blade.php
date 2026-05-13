{{-- DPL Dashboard Panel --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-3">
    {{-- Kelompok Bimbingan --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="user-group" class="size-8 text-green-500" />
            <div>
                <flux:heading size="lg">{{ __('Kelompok Bimbingan') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Total kelompok yang dibimbing') }}</flux:text>
    </div>

    {{-- Dokumen Perlu Review --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="inbox" class="size-8 text-amber-500" />
            <div>
                <flux:heading size="lg">{{ __('Perlu Review') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen menunggu review Anda') }}</flux:text>
    </div>

    {{-- Dokumen Disetujui --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <flux:icon name="check-circle" class="size-8 text-green-500" />
            <div>
                <flux:heading size="lg">{{ __('Disetujui') }}</flux:heading>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-sm">{{ __('Dokumen yang telah disetujui') }}</flux:text>
    </div>
</div>

{{-- Quick Actions --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
    <div class="flex flex-wrap gap-3">
        <flux:button variant="filled" icon="inbox">{{ __('Review Dokumen') }}</flux:button>
        <flux:button variant="ghost" icon="user-group">{{ __('Kelompok Bimbingan') }}</flux:button>
        <flux:button variant="ghost" icon="clipboard-document-list">{{ __('Buku Pembimbingan') }}</flux:button>
    </div>
</div>

{{-- Pending Reviews --}}
<div class="flex-1 rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <flux:heading size="lg" class="mb-4">{{ __('Dokumen Menunggu Review') }}</flux:heading>
    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Tidak ada dokumen yang menunggu review saat ini.') }}
    </flux:text>
</div>
