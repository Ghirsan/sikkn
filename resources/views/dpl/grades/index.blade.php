<x-layouts::app :title="__('Penilaian Mahasiswa')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Penilaian Mahasiswa') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Formulir penilaian akhir individu mahasiswa KKN bimbingan Anda.') }}</flux:text>
            </div>
        </div>

        <flux:separator />

        {{-- Period Constraint Notice --}}
        <div class="rounded-xl border-2 border-dashed border-amber-300 bg-amber-50/50 p-6 dark:border-amber-700 dark:bg-amber-900/10">
            <div class="flex items-start gap-3">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="exclamation-triangle" class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:heading size="lg">{{ __('Penilaian Belum Dibuka') }}</flux:heading>
                    <flux:text class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
                        {{ __('Formulir penilaian akan terbuka secara otomatis setelah masa pelaksanaan KKN tim bimbingan Anda berakhir. Jadwal tiap tim per periode dapat berbeda.') }}
                    </flux:text>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <flux:icon name="academic-cap" class="size-5 text-blue-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Total Mahasiswa') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                        <flux:icon name="check-circle" class="size-5 text-green-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Sudah Dinilai') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
            <div class="rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                        <flux:icon name="clock" class="size-5 text-amber-500" />
                    </div>
                    <div>
                        <flux:text class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('Belum Dinilai') }}</flux:text>
                        <flux:text class="text-2xl font-bold">0</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Students Grading Table --}}
        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Daftar Penilaian Mahasiswa') }}</flux:heading>
            </div>
            <div class="px-6 py-12 text-center">
                <flux:icon name="clipboard-document-check" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Penilaian Belum Tersedia') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('Daftar mahasiswa untuk dinilai akan muncul setelah periode KKN berakhir.') }}
                </flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
