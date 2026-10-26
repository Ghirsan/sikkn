<div class="space-y-6">
    {{-- Active Period Overview Banner --}}
    <flux:card class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4">
        <div class="flex items-center gap-3">
            <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                <flux:icon.calendar class="size-6" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="lg">{{ $period ? $period->displayName : __('Tidak Ada Periode Aktif') }}</flux:heading>
                    @if($period)
                        <flux:badge :color="$period->status->color()" size="sm">
                            {{ $period->status->label() }}
                        </flux:badge>
                    @endif
                </div>
                @if($period && $period->start_date && $period->end_date)
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        {{ $period->start_date->translatedFormat('d F Y') }} - {{ $period->end_date->translatedFormat('d F Y') }}
                    </flux:text>
                @endif
            </div>
        </div>

        <flux:button href="{{ route('admin.periods.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px] w-full sm:w-auto">
            {{ __('Kelola Periode') }}
        </flux:button>
    </flux:card>

    {{-- Primary KPI Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Periode --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Periode KKN') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1 truncate max-w-[170px]">{{ $period ? $period->name : '-' }}</flux:heading>
                </div>
                <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <flux:icon.calendar class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:badge size="sm" :color="$period ? 'amber' : 'zinc'">{{ $period ? $period->semester->value . ' ' . $period->year : __('Nonaktif') }}</flux:badge>
                <flux:button href="{{ route('admin.periods.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Kelola') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Card 2: Peserta KKN --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Peserta KKN') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalStudents }}</flux:heading>
                </div>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <flux:icon.academic-cap class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:text class="text-zinc-500">{{ __('Mahasiswa aktif') }}</flux:text>
                <flux:button href="{{ route('admin.students.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Daftar') }}
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
                <flux:text class="text-zinc-500">{{ __('Dosen pembimbing') }}</flux:text>
                <flux:button href="{{ route('admin.dpl.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Daftar') }}
                </flux:button>
            </div>
        </flux:card>

        {{-- Card 4: Kelompok KKN --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Kelompok KKN') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalGroups }}</flux:heading>
                </div>
                <div class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                    <flux:icon.user-group class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:text class="text-zinc-500">{{ __('Kelompok terdaftar') }}</flux:text>
                <flux:button href="{{ route('admin.groups.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                    {{ __('Kelola') }}
                </flux:button>
            </div>
        </flux:card>
    </div>

    {{-- Overview Cards: Dokumen Lifecycle & Program Kerja --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Dokumen Lifecycle --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Status Dokumen Kelompok') }}</flux:heading>
                    <flux:text class="text-xs">{{ __('Penguncian dokumen resmi tim KKN periode ini.') }}</flux:text>
                </div>
                <flux:button href="{{ route('admin.documents.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                    {{ __('Rancangan Dokumen') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />

            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                    <div>
                        <flux:text variant="strong">{{ __('LRK Terkunci (Final)') }}</flux:text>
                        <flux:text class="text-xs text-zinc-500 block">{{ __('Laporan Rencana Kegiatan siap unduh') }}</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">{{ $lrkLockedGroups }} / {{ $totalGroups }}</flux:heading>
                        <flux:badge :color="$totalGroups > 0 && $lrkLockedGroups === $totalGroups ? 'green' : 'zinc'" size="sm">
                            {{ $totalGroups > 0 ? round(($lrkLockedGroups / $totalGroups) * 100) : 0 }}%
                        </flux:badge>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                    <div>
                        <flux:text variant="strong">{{ __('LPK Terkunci (Final)') }}</flux:text>
                        <flux:text class="text-xs text-zinc-500 block">{{ __('Laporan Pelaksanaan Kegiatan siap unduh') }}</flux:text>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">{{ $lpkLockedGroups }} / {{ $totalGroups }}</flux:heading>
                        <flux:badge :color="$totalGroups > 0 && $lpkLockedGroups === $totalGroups ? 'green' : 'zinc'" size="sm">
                            {{ $totalGroups > 0 ? round(($lpkLockedGroups / $totalGroups) * 100) : 0 }}%
                        </flux:badge>
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- Program Kerja Overview --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Aktivitas Program Kerja') }}</flux:heading>
                    <flux:text class="text-xs">{{ __('Akumulasi program yang diajukan oleh kelompok.') }}</flux:text>
                </div>
                <flux:button href="{{ route('admin.groups.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                    {{ __('Daftar Kelompok') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />

            <div class="flex flex-col justify-center items-center py-6 text-center space-y-2">
                <flux:icon.light-bulb class="size-12 text-blue-500/80" />
                <flux:heading size="xl">{{ $totalPrograms }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">
                    {{ __('Total Program Kerja Diusulkan pada Periode Ini') }}
                </flux:text>
                <div class="pt-2">
                    <flux:button href="{{ route('admin.documents.index') }}" wire:navigate variant="filled" size="sm" class="min-h-[44px]">
                        {{ __('Periksa Progres Dokumen Kelompok') }}
                    </flux:button>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Quick Admin Actions Grid --}}
    <flux:card class="p-4">
        <flux:heading size="base" class="mb-3">{{ __('Aksi Cepat Admin P2KKN') }}</flux:heading>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <flux:button href="{{ route('admin.periods.index') }}" wire:navigate variant="filled" icon="calendar" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Kelola Periode KKN') }}
            </flux:button>
            <flux:button href="{{ route('admin.groups.index') }}" wire:navigate variant="ghost" icon="user-group" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Kelola Kelompok') }}
            </flux:button>
            <flux:button href="{{ route('admin.students.index') }}" wire:navigate variant="ghost" icon="academic-cap" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Daftar Peserta KKN') }}
            </flux:button>
            <flux:button href="{{ route('admin.dpl.index') }}" wire:navigate variant="ghost" icon="user-circle" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Daftar Dosen KKN') }}
            </flux:button>
            <flux:button href="{{ route('admin.documents.index') }}" wire:navigate variant="ghost" icon="document-text" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Rancangan Dokumen') }}
            </flux:button>
            <flux:button href="{{ route('admin.faculties.index') }}" wire:navigate variant="ghost" icon="building-office-2" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Data Fakultas') }}
            </flux:button>
            <flux:button href="{{ route('admin.study-programs.index') }}" wire:navigate variant="ghost" icon="building-library" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Program Studi') }}
            </flux:button>
            <flux:button href="{{ route('admin.users.index') }}" wire:navigate variant="ghost" icon="users" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Pengguna Sistem') }}
            </flux:button>
        </div>
    </flux:card>
</div>
