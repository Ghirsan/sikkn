<x-layouts::app :title="__('LRK Tim')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Laporan Rencana Kegiatan (LRK)') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kompilasi dokumen rencana program kerja seluruh anggota tim KKN Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:mahasiswa.lrk-documents />
    </div>
</x-layouts::app>
