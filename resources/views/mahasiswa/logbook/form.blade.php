<x-layouts::app :title="request('logId') ? __('Edit Logbook') : __('Tambah Logbook')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <livewire:mahasiswa.logbook-form />
    </div>
</x-layouts::app>
