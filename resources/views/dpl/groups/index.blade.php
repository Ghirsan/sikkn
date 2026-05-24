<x-layouts::app :title="__('Mahasiswa Bimbingan')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Mahasiswa Bimbingan') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Kelompok dan mahasiswa KKN di bawah bimbingan Anda.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:dpl.student-groups />
    </div>
</x-layouts::app>
