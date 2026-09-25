<x-layouts::app :title="__('Kelompok Saya')">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item icon="home" href="{{ route('dashboard') }}" wire:navigate />
        <flux:breadcrumbs.item>{{ __('Kelompok Saya') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Kelompok Saya') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Informasi tim KKN dan lokasi penugasan Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:mahasiswa.my-group />
    </div>
</x-layouts::app>
