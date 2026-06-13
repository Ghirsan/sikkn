<x-layouts::app :title="__('Program Kerja')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pemantauan program kerja KKN seluruh mahasiswa di bawah Fakultas.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Status Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <x-stat-card icon="pencil-square" color="neutral" :label="__('Draft')" value="0" />
            <x-stat-card icon="clock" color="amber" :label="__('Diajukan')" value="0" />
            <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" value="0" />
            <x-stat-card icon="document-check" color="blue" :label="__('PDF Final')" value="0" />
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap items-center gap-3">
            <flux:input icon="magnifying-glass" placeholder="{{ __('Cari program atau mahasiswa...') }}" size="sm" class="w-72" />
            <flux:select size="sm" placeholder="{{ __('Semua Prodi') }}" class="w-48">
                <flux:select.option>{{ __('Semua Prodi') }}</flux:select.option>
            </flux:select>
            <flux:select size="sm" placeholder="{{ __('Semua Status') }}" class="w-48">
                <flux:select.option>{{ __('Semua Status') }}</flux:select.option>
                <flux:select.option>{{ __('Draft') }}</flux:select.option>
                <flux:select.option>{{ __('Diajukan') }}</flux:select.option>
                <flux:select.option>{{ __('Disetujui') }}</flux:select.option>
            </flux:select>
            <flux:select size="sm" placeholder="{{ __('Semua Jenis') }}" class="w-48">
                <flux:select.option>{{ __('Semua Jenis') }}</flux:select.option>
                <flux:select.option>{{ __('Multidisiplin') }}</flux:select.option>
                <flux:select.option>{{ __('Pengabdian Masyarakat') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Programs Table --}}
        <flux:card>
            <flux:heading size="lg">{{ __('Daftar Program Kerja') }}</flux:heading>

            <flux:separator />

            <x-empty-state icon="light-bulb" :heading="__('Belum Ada Program Kerja')" :description="__('Program kerja yang diusulkan mahasiswa di seluruh Program Studi Fakultas akan tampil di sini.')" />
        </flux:card>
    </div>
</x-layouts::app>
