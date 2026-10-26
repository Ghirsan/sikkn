<div class="space-y-6">
    {{-- Faculty & Period Header Banner --}}
    <flux:card class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-rose-50 p-2 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                <flux:icon.building-office-2 class="size-6" />
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:heading size="lg">{{ $faculty?->name ?? __('Fakultas') }}</flux:heading>
                    @if($faculty?->short_name)
                        <flux:badge color="zinc" size="sm">{{ $faculty->short_name }}</flux:badge>
                    @endif
                </div>
                @if($period)
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ __('Periode:') }} {{ $period->displayName }}
                    </flux:text>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:badge color="rose" size="md">
                {{ __('Monitoring Fakultas') }}
            </flux:badge>
        </div>
    </flux:card>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Mahasiswa KKN --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Mahasiswa KKN') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalStudents }}</flux:heading>
                </div>
                <div class="rounded-lg bg-rose-50 p-2 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400">
                    <flux:icon.academic-cap class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:text class="text-zinc-500">{{ __('Mahasiswa fakultas') }}</flux:text>
                <flux:button href="{{ route('fakultas.students.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Daftar') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Card 2: Program Studi --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Program Studi') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalStudyPrograms }}</flux:heading>
                </div>
                <div class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                    <flux:icon.building-library class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:text class="text-zinc-500">{{ __('Prodi aktif') }}</flux:text>
                <flux:button href="{{ route('fakultas.students.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Per Prodi') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Card 3: Dosen KKN (DPL) --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Dosen KKN (DPL)') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalDpls }}</flux:heading>
                </div>
                <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <flux:icon.user-circle class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:text class="text-zinc-500">{{ __('DPL fakultas') }}</flux:text>
                <flux:button href="{{ route('fakultas.dpls.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Daftar') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Card 4: Program Kerja --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Program Kerja') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalPrograms }}</flux:heading>
                </div>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <flux:icon.light-bulb class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:text class="text-zinc-500">{{ __('Diusulkan mahasiswa') }}</flux:text>
                <flux:button href="{{ route('fakultas.programs.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Program') }}
                </flux:button>
            </div>
        </flux:card>
    </div>

    {{-- Quick Action Shortcuts --}}
    <flux:card class="p-4">
        <flux:heading size="base" class="mb-3">{{ __('Aksi Cepat Fakultas') }}</flux:heading>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <flux:button href="{{ route('fakultas.students.index') }}" wire:navigate variant="filled" icon="academic-cap" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Mahasiswa Per Prodi') }}
            </flux:button>
            <flux:button href="{{ route('fakultas.dpls.index') }}" wire:navigate variant="ghost" icon="user-circle" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Dosen KKN (DPL)') }}
            </flux:button>
            <flux:button href="{{ route('fakultas.programs.index') }}" wire:navigate variant="ghost" icon="light-bulb" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Program Kerja Mahasiswa') }}
            </flux:button>
            <flux:button href="{{ route('fakultas.documents.index') }}" wire:navigate variant="ghost" icon="document-text" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Dokumen Tim KKN') }}
            </flux:button>
            <flux:button href="{{ route('fakultas.logbooks.index') }}" wire:navigate variant="ghost" icon="book-open" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Logbook Mahasiswa') }}
            </flux:button>
            <flux:button href="{{ route('fakultas.mentoring.index') }}" wire:navigate variant="ghost" icon="clipboard-document-list" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Buku Pembimbingan') }}
            </flux:button>
            <flux:button href="{{ route('fakultas.grades.index') }}" wire:navigate variant="ghost" icon="clipboard-document-check" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Penilaian Mahasiswa') }}
            </flux:button>
        </div>
    </flux:card>
</div>
