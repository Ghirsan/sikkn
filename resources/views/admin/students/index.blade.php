<x-layouts::app :title="__('Peserta KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Peserta KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Database mahasiswa peserta Kuliah Kerja Nyata.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:admin.students />
    </div>
</x-layouts::app>
