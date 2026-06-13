{{-- P2KKN Admin Dashboard Panel --}}
{{-- P2KKN: Melihat rancangan dokumen tiap mahasiswa & tim beserta status (lifecycle), --}}
{{-- melihat daftar DPL dan mahasiswa peserta KKN --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <x-stat-card icon="calendar" color="amber" :label="__('Periode Aktif')" value="-" />
    <x-stat-card icon="academic-cap" color="blue" :label="__('Peserta KKN')" value="0" />
    <x-stat-card icon="user-circle" color="green" :label="__('DPL')" value="0" />
    <x-stat-card icon="user-group" color="purple" :label="__('Kelompok')" value="0" />
</div>

<div class="grid gap-4 md:grid-cols-2">
    {{-- Document Lifecycle Overview --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Status Dokumen (Program Kerja)') }}</flux:heading>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Draft Mahasiswa') }}</flux:text>
                <flux:text variant="strong">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Diajukan') }}</flux:text>
                <flux:text variant="strong" color="amber">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Disetujui') }}</flux:text>
                <flux:text variant="strong" color="green">0</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('PDF Final Terbentuk') }}</flux:text>
                <flux:text variant="strong" color="blue">0</flux:text>
            </div>
        </div>
    </flux:card>

    {{-- Quick Actions --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button variant="filled" icon="document-text" class="justify-start">{{ __('Lihat Rancangan Dokumen') }}</flux:button>
            <flux:button variant="ghost" icon="academic-cap" class="justify-start">{{ __('Daftar Peserta KKN') }}</flux:button>
            <flux:button variant="ghost" icon="user-circle" class="justify-start">{{ __('Daftar DPL') }}</flux:button>
            <flux:button variant="ghost" icon="user-group" class="justify-start">{{ __('Kelola Kelompok') }}</flux:button>
            <flux:button variant="ghost" icon="calendar" class="justify-start">{{ __('Kelola Periode') }}</flux:button>
        </div>
    </flux:card>
</div>

{{-- DPL & Mahasiswa Lists --}}
<div class="grid gap-4 md:grid-cols-2">
    <flux:card>
        <flux:heading size="lg">{{ __('Daftar DPL') }}</flux:heading>
        <flux:separator />
        <flux:text>{{ __('Belum ada DPL terdaftar pada periode ini.') }}</flux:text>
    </flux:card>
    <flux:card>
        <flux:heading size="lg">{{ __('Daftar Peserta KKN') }}</flux:heading>
        <flux:separator />
        <flux:text>{{ __('Belum ada mahasiswa peserta KKN pada periode ini.') }}</flux:text>
    </flux:card>
</div>

{{-- Rancangan Dokumen Per Tim --}}
<flux:card>
    <flux:heading size="lg">{{ __('Rancangan Dokumen Per Tim') }}</flux:heading>
    <flux:separator />
    <flux:text>{{ __('Rancangan dokumen tiap tim KKN beserta status lifecycle akan ditampilkan di sini.') }}</flux:text>
</flux:card>
