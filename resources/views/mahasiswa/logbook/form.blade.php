<x-layouts::app
    :title="request('logId') ? __('Edit Logbook') : __('Tambah Logbook')"
    :breadcrumbs="[
        ['label' => __('Logbook'), 'url' => route('logbook.index')],
        ['label' => __('Form Logbook')],
    ]"
>
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
