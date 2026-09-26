<x-layouts::app
    :title="__('Form Laporan LRK')"
    :breadcrumbs="[
        ['label' => __('LRK'), 'url' => route('lrk.index')],
        ['label' => __('Form LRK')],
    ]"
>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Form Laporan LRK') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Isi paragraf dokumen LRK sebagai ketua tim.') }}</flux:text>
            </div>
            <flux:button href="{{ route('lrk.index') }}" wire:navigate icon="arrow-left" variant="ghost">{{ __('Kembali') }}</flux:button>
        </div>
        <flux:separator />

        <div class="w-full">
            <livewire:mahasiswa.lrk-form />
        </div>
    </div>
</x-layouts::app>
