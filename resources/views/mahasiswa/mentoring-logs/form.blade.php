<x-layouts::app :title="request('logId') ? __('Edit Catatan Pembimbingan') : __('Tambah Catatan Pembimbingan')">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item icon="home" href="{{ route('dashboard') }}" wire:navigate />
        <flux:breadcrumbs.item href="{{ route('mentoring-logs.index') }}" wire:navigate>{{ __('Pembimbingan') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ request('logId') ? __('Edit Catatan') : __('Tambah Catatan') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ request('logId') ? __('Edit Catatan Pembimbingan') : __('Catatan Pembimbingan Baru') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Catat detail kegiatan pembimbingan dan diskusi bersama Dosen KKN Anda.') }}</flux:text>
            </div>
            <flux:button href="{{ route('mentoring-logs.index') }}" wire:navigate icon="arrow-left" variant="ghost">{{ __('Kembali') }}</flux:button>
        </div>
        <flux:separator />
        <livewire:mahasiswa.mentoring-log-form />
    </div>
</x-layouts::app>
