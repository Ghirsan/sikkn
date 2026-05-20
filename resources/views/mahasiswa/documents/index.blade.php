<x-layouts::app :title="__('Dokumen Tim')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dokumen Tim (LRK/LPK)') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Kompilasi dokumen program kerja seluruh anggota tim KKN Anda.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Document Cards --}}
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <flux:icon name="document-text" class="size-5 text-blue-500" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('LRK') }}</flux:heading>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Laporan Rencana Kegiatan') }}</flux:text>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                    <flux:text class="text-sm">{{ __('Status') }}</flux:text>
                    <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
                </div>
                <flux:text class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Dokumen LRK tim akan tersedia setelah seluruh program kerja anggota disetujui DPL.') }}
                </flux:text>
            </div>

            <div class="rounded-xl border border-neutral-200 p-6 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <flux:icon name="document-text" class="size-5 text-green-500" />
                    </div>
                    <div>
                        <flux:heading size="lg">{{ __('LPK') }}</flux:heading>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                    <flux:text class="text-sm">{{ __('Status') }}</flux:text>
                    <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
                </div>
                <flux:text class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Dokumen LPK tim akan tersedia setelah pelaksanaan kegiatan KKN.') }}
                </flux:text>
            </div>
        </div>

        {{-- PDF Generation Notice --}}
        <div class="rounded-xl border-2 border-dashed border-blue-300 bg-blue-50/50 p-6 dark:border-blue-700 dark:bg-blue-900/10">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon name="information-circle" class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('PDF Final') }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                        {{ __('Tombol "Cetak PDF Final" akan aktif secara otomatis setelah seluruh program kerja anggota tim Anda mendapat persetujuan kolektif dari DPL.') }}
                    </flux:text>
                </div>
            </div>
        </div>

        {{-- Team Programs Overview --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Program Kerja Tim') }}</flux:heading>
            </div>
            <div class="px-6 py-12 text-center">
                <flux:icon name="document-text" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Program') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Program kerja yang telah disetujui akan ditampilkan di sini.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
