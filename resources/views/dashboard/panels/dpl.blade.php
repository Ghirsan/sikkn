{{-- DPL Dashboard Panel --}}
{{-- DPL: Melihat daftar mahasiswa bimbingan, approve/revisi program, --}}
{{-- melihat dokumen lengkap tim, form penilaian setelah periode selesai --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <x-stat-card icon="academic-cap" color="green" :label="__('Mahasiswa')" value="0" />
    <x-stat-card icon="light-bulb" color="amber" :label="__('Program Review')" value="0" />
    <x-stat-card icon="document-text" color="blue" :label="__('Dokumen Tim')" value="0" />
    <x-stat-card icon="clipboard-document-check" color="purple" :label="__('Penilaian')" value="0" />
</div>

{{-- Two-Column Layout --}}
<div class="grid gap-4 md:grid-cols-2">
    {{-- Program Perlu Review --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Program Menunggu Review') }}</flux:heading>
            <flux:badge color="amber" size="sm">0</flux:badge>
        </div>

        <flux:separator />

        <flux:text>{{ __('Tidak ada program yang menunggu review saat ini.') }}</flux:text>
    </flux:card>

    {{-- Quick Actions --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button variant="filled" icon="light-bulb" class="justify-start">{{ __('Review Program Mahasiswa') }}</flux:button>
            <flux:button variant="ghost" icon="document-text" class="justify-start">{{ __('Lihat Dokumen Tim') }}</flux:button>
            <flux:button variant="ghost" icon="user-group" class="justify-start">{{ __('Daftar Mahasiswa Bimbingan') }}</flux:button>
            <flux:button variant="ghost" icon="clipboard-document-check" class="justify-start">{{ __('Penilaian Mahasiswa') }}</flux:button>
        </div>
    </flux:card>
</div>

{{-- Kelompok Bimbingan --}}
<flux:card>
    <flux:heading size="lg">{{ __('Kelompok Bimbingan') }}</flux:heading>

    <flux:separator />

    <flux:text>{{ __('Belum ada kelompok bimbingan yang ditugaskan. Hubungi P2KKN untuk informasi penugasan.') }}</flux:text>
</flux:card>

{{-- Dokumen Tim Overview --}}
<flux:card>
    <flux:heading size="lg">{{ __('Status Dokumen Tim') }}</flux:heading>

    <flux:separator />

    <flux:text>{{ __('Dokumen lengkap tim (LRK/LPK) akan tampil di sini setelah mahasiswa mengisi form program kerja.') }}</flux:text>
</flux:card>
