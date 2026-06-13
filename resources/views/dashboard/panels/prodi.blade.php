{{-- Prodi Dashboard Panel --}}
{{-- Prodi: Melihat daftar mahasiswanya serta program multidisiplin --}}
{{-- dan pengabdian masyarakat yang diusulkan oleh mahasiswa --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-3">
    <x-stat-card icon="academic-cap" color="purple" :label="__('Mahasiswa KKN')" value="0" />
    <x-stat-card icon="light-bulb" color="blue" :label="__('Program Multidisiplin')" value="0" />
    <x-stat-card icon="heart" color="green" :label="__('Pengabdian')" value="0" />
</div>

{{-- Daftar Mahasiswa Prodi --}}
<flux:card>
    <flux:heading size="lg">{{ __('Mahasiswa KKN Program Studi') }}</flux:heading>
    <flux:separator />
    <flux:text>{{ __('Belum ada data mahasiswa KKN dari program studi ini.') }}</flux:text>
</flux:card>

{{-- Program Kerja Mahasiswa --}}
<flux:card>
    <flux:heading size="lg">{{ __('Program Kerja yang Diusulkan') }}</flux:heading>
    <flux:separator />
    <flux:text>{{ __('Program multidisiplin dan pengabdian masyarakat yang diusulkan mahasiswa prodi akan ditampilkan di sini.') }}</flux:text>
</flux:card>
