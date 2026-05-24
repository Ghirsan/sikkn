<x-layouts::app :title="__('Buku Pembimbingan')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Buku Pembimbingan') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Tinjau catatan konsultasi dan berikan feedback pembimbingan kepada mahasiswa.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:dpl.mentoring-book />
    </div>
</x-layouts::app>
