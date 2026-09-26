<x-layouts::app
    :title="__('Form Program')"
    :breadcrumbs="[
        ['label' => __('Program Saya'), 'url' => route('programs.index')],
        ['label' => __('Form Program')],
    ]"
>
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ request('action') == 'lpk' ? __('Lapor LPK') : __('Form Program Kerja') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Isi detail program kerja atau laporan pelaksanaan.') }}</flux:text>
            </div>
            <flux:button href="{{ route('programs.index') }}" wire:navigate icon="arrow-left" variant="ghost">{{ __('Kembali') }}</flux:button>
        </div>
        <flux:separator />
        
        <div class="w-full">
            <livewire:mahasiswa.program-form />
        </div>
    </div>
</x-layouts::app>
