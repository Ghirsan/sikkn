<x-layouts::app :title="__('LPK Tim')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Laporan Pelaksanaan Kegiatan (LPK)') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kompilasi dokumen laporan pelaksanaan program kerja seluruh anggota tim KKN Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:mahasiswa.lpk-documents />
    </div>
</x-layouts::app>
