<x-layouts::app :title="__('Dokumen Tim')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <flux:heading size="xl">{{ __('Dokumen Tim') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Lihat dokumen lengkap hasil pengisian form program kerja satu tim KKN.') }}</flux:text>
        </div>
        <flux:separator />
        <livewire:dpl.team-documents />
    </div>
</x-layouts::app>
