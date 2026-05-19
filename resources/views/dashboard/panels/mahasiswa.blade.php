{{-- Mahasiswa Dashboard Panel --}}
{{-- Mahasiswa mengisi dokumen LRK dan LPK (program multidisiplin & pengabdian masyarakat) --}}
{{-- Logbook dan Pembimbingan diisi secara individu, disupervisi DPL --}}

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    {{-- Program Kerja --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <flux:icon name="light-bulb" class="size-5 text-blue-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Program Kerja') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('Multidisiplin & Pengabdian') }}</flux:text>
    </div>

    {{-- Status Dokumen Tim --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                <flux:icon name="document-text" class="size-5 text-amber-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Dokumen Tim') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('LRK & LPK kelompok') }}</flux:text>
    </div>

    {{-- Logbook Harian --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                <flux:icon name="book-open" class="size-5 text-green-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Logbook') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('Catatan harian individu') }}</flux:text>
    </div>

    {{-- Buku Pembimbingan --}}
    <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
        <div class="flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                <flux:icon name="clipboard-document-list" class="size-5 text-purple-500" />
            </div>
            <div>
                <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Pembimbingan') }}</flux:text>
                <flux:text class="text-2xl font-bold">0</flux:text>
            </div>
        </div>
        <flux:text class="mt-2 text-xs text-neutral-400 dark:text-neutral-500">{{ __('Catatan bimbingan DPL') }}</flux:text>
    </div>
</div>

{{-- Two-Column Layout: Document Lifecycle + Quick Actions --}}
<div class="grid gap-4 md:grid-cols-2">
    {{-- Document Lifecycle Status --}}
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Status Dokumen Kelompok') }}</flux:heading>

        <div class="space-y-3">
            {{-- LRK Status --}}
            <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <flux:icon name="document-text" class="size-5 text-blue-500" />
                    <div>
                        <flux:text class="font-medium">{{ __('LRK') }}</flux:text>
                        <flux:text class="text-xs text-neutral-400">{{ __('Laporan Rencana Kegiatan') }}</flux:text>
                    </div>
                </div>
                <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
            </div>

            {{-- LPK Status --}}
            <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <flux:icon name="document-text" class="size-5 text-green-500" />
                    <div>
                        <flux:text class="font-medium">{{ __('LPK') }}</flux:text>
                        <flux:text class="text-xs text-neutral-400">{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
                    </div>
                </div>
                <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button variant="filled" icon="plus" class="justify-start">{{ __('Usulkan Program Kerja') }}</flux:button>
            <flux:button variant="ghost" icon="book-open" class="justify-start">{{ __('Isi Logbook Harian') }}</flux:button>
            <flux:button variant="ghost" icon="clipboard-document-list" class="justify-start">{{ __('Isi Buku Pembimbingan') }}</flux:button>
            <flux:button variant="ghost" icon="user-group" class="justify-start">{{ __('Lihat Kelompok Saya') }}</flux:button>
        </div>
    </div>
</div>

{{-- Program Kerja Table --}}
<div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Saya') }}</flux:heading>
        <flux:button variant="filled" size="sm" icon="plus">{{ __('Usulkan Program') }}</flux:button>
    </div>

    <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">
        {{ __('Belum ada program kerja. Usulkan program multidisiplin atau pengabdian masyarakat untuk kelompok KKN Anda.') }}
    </flux:text>
</div>
