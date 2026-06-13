{{-- Fakultas Dashboard Panel --}}
{{-- Fakultas: Melihat daftar mahasiswa dikategorikan per prodi, --}}
{{-- serta program multidisiplin dan pengabdian masyarakat yang diusulkan --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <x-stat-card icon="academic-cap" color="blue" :label="__('Mahasiswa KKN')" value="0" />
    <x-stat-card icon="building-library" color="purple" :label="__('Program Studi')" value="0" />
    <x-stat-card icon="light-bulb" color="blue" :label="__('Multidisiplin')" value="0" />
    <x-stat-card icon="heart" color="green" :label="__('Pengabdian')" value="0" />
</div>

{{-- Mahasiswa Per Prodi --}}
<flux:card>
    <flux:heading size="lg">{{ __('Mahasiswa KKN Per Program Studi') }}</flux:heading>
    <flux:separator />
    <flux:text>{{ __('Data mahasiswa KKN yang dikategorikan berdasarkan program studi akan ditampilkan di sini.') }}</flux:text>
</flux:card>

{{-- Program Kerja Overview --}}
<flux:card>
    <flux:heading size="lg">{{ __('Program Kerja yang Diusulkan') }}</flux:heading>
    <flux:separator />
    <flux:text>{{ __('Ringkasan program multidisiplin dan pengabdian masyarakat dari seluruh mahasiswa fakultas akan ditampilkan di sini.') }}</flux:text>
</flux:card>
