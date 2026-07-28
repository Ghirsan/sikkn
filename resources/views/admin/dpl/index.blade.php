<x-layouts::app :title="__('Daftar Dosen KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dosen KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Kelola data Dosen KKN yang ditugaskan pada periode aktif.') }}</flux:text>
            </div>
        </div>
        <flux:separator />
        <livewire:admin.dpls />
    </div>
</x-layouts::app>
