<x-layouts::app :title="__('Dokumen Tim')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Dokumen Tim (LRK/LPK)') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kompilasi dokumen program kerja seluruh anggota tim KKN Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:mahasiswa.documents />
    </div>
</x-layouts::app>
