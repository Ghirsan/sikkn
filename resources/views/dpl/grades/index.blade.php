<x-layouts::app :title="__('Penilaian Mahasiswa')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Penilaian Mahasiswa') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Berikan penilaian akhir KKN untuk setiap mahasiswa bimbingan Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:dpl.student-grades />
    </div>
</x-layouts::app>
