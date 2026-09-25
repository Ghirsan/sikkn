<x-layouts::app :title="__('Buku Pembimbingan')">
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item icon="home" href="{{ route('dashboard') }}" wire:navigate />
        <flux:breadcrumbs.item>{{ __('Pembimbingan') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Buku Pembimbingan') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Catat sesi konsultasi dan pembimbingan bersama Dosen KKN.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:mahasiswa.mentoring-logs />
    </div>
</x-layouts::app>
