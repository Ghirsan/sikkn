<x-layouts::app :title="request('logId') ? __('Edit Catatan Pembimbingan') : __('Tambah Catatan Pembimbingan')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <livewire:mahasiswa.mentoring-log-form />
    </div>
</x-layouts::app>
