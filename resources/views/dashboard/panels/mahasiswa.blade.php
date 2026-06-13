{{-- Mahasiswa Dashboard Panel --}}
{{-- Mahasiswa mengisi dokumen LRK dan LPK (program multidisiplin & pengabdian masyarakat) --}}
{{-- Logbook dan Pembimbingan diisi secara individu, disupervisi DPL --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <x-stat-card icon="light-bulb" color="blue" :label="__('Program Kerja')" value="0" />
    <x-stat-card icon="document-text" color="amber" :label="__('Dokumen Tim')" value="0" />
    <x-stat-card icon="book-open" color="green" :label="__('Logbook')" value="0" />
    <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Pembimbingan')" value="0" />
</div>

{{-- Two-Column Layout: Document Lifecycle + Quick Actions --}}
<div class="grid gap-4 md:grid-cols-2">
    {{-- Document Lifecycle Status --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Status Dokumen Kelompok') }}</flux:heading>

        <div class="space-y-3">
            {{-- LRK Status --}}
            <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <flux:icon.document-text variant="mini" class="text-blue-500" />
                    <div>
                        <flux:text variant="strong">{{ __('LRK') }}</flux:text>
                        <flux:text class="text-xs">{{ __('Laporan Rencana Kegiatan') }}</flux:text>
                    </div>
                </div>
                <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
            </div>

            {{-- LPK Status --}}
            <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <flux:icon.document-text variant="mini" class="text-green-500" />
                    <div>
                        <flux:text variant="strong">{{ __('LPK') }}</flux:text>
                        <flux:text class="text-xs">{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
                    </div>
                </div>
                <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
            </div>
        </div>
    </flux:card>

    {{-- Quick Actions --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button href="{{ route('programs.index') }}" wire:navigate variant="filled" icon="plus" class="justify-start">{{ __('Usulkan Program Kerja') }}</flux:button>
            <flux:button href="{{ route('logbook.index') }}" wire:navigate variant="ghost" icon="book-open" class="justify-start">{{ __('Isi Logbook') }}</flux:button>
            <flux:button href="{{ route('mentoring-logs.index') }}" wire:navigate variant="ghost" icon="clipboard-document-list" class="justify-start">{{ __('Isi Buku Pembimbingan') }}</flux:button>
            <flux:button href="{{ route('groups.index') }}" wire:navigate variant="ghost" icon="user-group" class="justify-start">{{ __('Lihat Kelompok Saya') }}</flux:button>
        </div>
    </flux:card>
</div>

{{-- Program Kerja Table --}}
<flux:card>
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Saya') }}</flux:heading>
        <flux:button href="{{ route('programs.index') }}" wire:navigate variant="filled" size="sm" icon="plus">{{ __('Usulkan Program') }}</flux:button>
    </div>

    <flux:separator />

    <x-empty-state icon="light-bulb" :heading="__('Belum ada program kerja')" :description="__('Usulkan program multidisiplin atau pengabdian masyarakat untuk kelompok KKN Anda.')" />
</flux:card>
