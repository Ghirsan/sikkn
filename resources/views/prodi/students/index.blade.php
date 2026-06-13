<x-layouts::app :title="__('Mahasiswa KKN')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Mahasiswa KKN') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pemantauan keikutsertaan mahasiswa Program Studi dalam KKN.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Summary Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <x-stat-card icon="academic-cap" color="purple" :label="__('Total Peserta Prodi')" value="0" />
            <x-stat-card icon="user-group" color="green" :label="__('Sudah Berkelompok')" value="0" />
            <x-stat-card icon="clipboard-document-check" color="amber" :label="__('Sudah Dinilai')" value="0" />
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input icon="magnifying-glass" placeholder="{{ __('Cari nama atau NIM...') }}" size="sm" class="w-72" />
            <flux:select size="sm" placeholder="{{ __('Semua Status') }}" class="w-48">
                <flux:select.option>{{ __('Semua Status') }}</flux:select.option>
                <flux:select.option>{{ __('Sudah Berkelompok') }}</flux:select.option>
                <flux:select.option>{{ __('Belum Berkelompok') }}</flux:select.option>
                <flux:select.option>{{ __('Sudah Dinilai') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Students Table --}}
        <flux:card>
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Daftar Mahasiswa KKN') }}</flux:heading>
                <flux:badge color="zinc">{{ __('0 mahasiswa') }}</flux:badge>
            </div>

            <flux:separator />

            <x-empty-state icon="academic-cap" :heading="__('Belum Ada Data Mahasiswa')" :description="__('Data mahasiswa KKN dari Program Studi Anda akan tampil di sini setelah periode KKN aktif.')" />
        </flux:card>
    </div>
</x-layouts::app>
