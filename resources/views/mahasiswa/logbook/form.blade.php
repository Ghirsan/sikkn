<x-layouts::app :title="request('logId') ? __('Edit Logbook') : __('Tambah Logbook')">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item icon="home" href="{{ route('dashboard') }}" wire:navigate />
        <flux:breadcrumbs.item href="{{ route('logbook.index') }}" wire:navigate>{{ __('Logbook') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ request('logId') ? __('Edit Entri') : __('Tambah Entri') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ request('logId') ? __('Edit Catatan Harian') : __('Catatan Harian Baru') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Isi detail kegiatan dan catatan penting Anda hari ini.') }}</flux:text>
            </div>
            <flux:button href="{{ route('logbook.index') }}" wire:navigate icon="arrow-left" variant="ghost">{{ __('Kembali') }}</flux:button>
        </div>
        <flux:separator />
        <livewire:mahasiswa.logbook-form />
    </div>
</x-layouts::app>
