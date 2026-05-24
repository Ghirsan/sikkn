<x-layouts::app :title="__('Review Program')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Review Program Kerja') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Tinjau, setujui, atau minta revisi program kerja mahasiswa bimbingan.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:dpl.review-programs />
    </div>
</x-layouts::app>
