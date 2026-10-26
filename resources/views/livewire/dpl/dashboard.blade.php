<div class="space-y-6">
    @if($dplGroups->isEmpty())
        {{-- Banner DPL Belum Memiliki Kelompok --}}
        <flux:card class="border-amber-200 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20">
            <div class="flex items-start gap-3">
                <flux:icon.exclamation-triangle class="size-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                <div>
                    <flux:heading size="base" class="text-amber-900 dark:text-amber-200">
                        {{ __('Belum Ada Kelompok Bimbingan Ditugaskan') }}
                    </flux:heading>
                    <flux:text class="text-amber-800/90 dark:text-amber-300/80 mt-1">
                        {{ __('Anda saat ini belum ditugaskan sebagai DPL pada kelompok KKN aktif. Silakan hubungi admin P2KKN untuk informasi penugasan DPL.') }}
                    </flux:text>
                </div>
            </div>
        </flux:card>
    @else
        {{-- Group Filter & Active Period Information --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                @if($period)
                    <flux:badge color="blue" size="md">
                        {{ $period->displayName }}
                    </flux:badge>
                @endif
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Total :count Kelompok Bimbingan', ['count' => $dplGroups->count()]) }}
                </flux:text>
            </div>

            @if($dplGroups->count() > 1)
                <div class="w-full sm:w-72">
                    <flux:select wire:model.live="selectedGroupId" size="sm" class="w-full">
                        <option value="">{{ __('Semua Kelompok Bimbingan (:count)', ['count' => $dplGroups->count()]) }}</option>
                        @foreach($dplGroups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                        @endforeach
                    </flux:select>
                </div>
            @endif
        </div>

        {{-- 4 Supervisory KPI Stat Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Card 1: Total Mahasiswa --}}
            <flux:card class="flex flex-col justify-between p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Mahasiswa Bimbingan') }}
                        </flux:text>
                        <flux:heading size="xl" class="mt-1">{{ $totalStudents }}</flux:heading>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                        <flux:icon.academic-cap class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs">
                    <flux:text class="text-zinc-500">{{ __('Dalam :count kelompok', ['count' => $activeGroups->count()]) }}</flux:text>
                    <flux:button href="{{ route('dpl.groups.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                        {{ __('Kelompok') }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- Card 2: Program Perlu Review --}}
            <flux:card class="flex flex-col justify-between p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Program Review') }}
                        </flux:text>
                        <flux:heading size="xl" class="mt-1">{{ $pendingProgramsCount }}</flux:heading>
                    </div>
                    <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                        <flux:icon.light-bulb class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs">
                    @if($pendingProgramsCount > 0)
                        <flux:badge color="amber" size="sm">{{ __('Perlu Tindakan') }}</flux:badge>
                    @else
                        <flux:badge color="green" size="sm">{{ __('Semua Selesai') }}</flux:badge>
                    @endif
                    <flux:button href="{{ route('dpl.programs.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                        {{ __('Review') }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- Card 3: Logbook Pending --}}
            <flux:card class="flex flex-col justify-between p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Logbook Pending') }}
                        </flux:text>
                        <flux:heading size="xl" class="mt-1">{{ $pendingLogsCount }}</flux:heading>
                    </div>
                    <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                        <flux:icon.book-open class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs">
                    @if($pendingLogsCount > 0)
                        <flux:badge color="blue" size="sm">{{ __('Menunggu Verifikasi') }}</flux:badge>
                    @else
                        <flux:badge color="green" size="sm">{{ __('Semua Terverifikasi') }}</flux:badge>
                    @endif
                    <flux:button href="{{ route('dpl.logbook.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                        {{ __('Verifikasi') }}
                    </flux:button>
                </div>
            </flux:card>

            {{-- Card 4: Mentoring Pending --}}
            <flux:card class="flex flex-col justify-between p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                            {{ __('Bimbingan Pending') }}
                        </flux:text>
                        <flux:heading size="xl" class="mt-1">{{ $pendingMentoringCount }}</flux:heading>
                    </div>
                    <div class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                        <flux:icon.clipboard-document-list class="size-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs">
                    @if($pendingMentoringCount > 0)
                        <flux:badge color="purple" size="sm">{{ __('Perlu Tanggapan') }}</flux:badge>
                    @else
                        <flux:badge color="green" size="sm">{{ __('Semua Ditanggapi') }}</flux:badge>
                    @endif
                    <flux:button href="{{ route('dpl.mentoring.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                        {{ __('Tanggapi') }}
                    </flux:button>
                </div>
            </flux:card>
        </div>

        {{-- Supervisory Action Queues: Programs & Daily Logs --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Queue 1: Programs Needing Review --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">{{ __('Program Menunggu Review') }}</flux:heading>
                        @if($pendingProgramsCount > 0)
                            <flux:badge color="amber" size="sm">{{ $pendingProgramsCount }}</flux:badge>
                        @endif
                    </div>
                    <flux:button href="{{ route('dpl.programs.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                        {{ __('Buka Halaman Review') }}
                    </flux:button>
                </div>

                <flux:separator variant="subtle" />

                @if($pendingPrograms->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon.check-circle class="mx-auto size-10 text-emerald-400 dark:text-emerald-500" />
                        <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Semua usulan program kerja telah ditinjau.') }}
                        </flux:text>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($pendingPrograms as $participant)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                                <div class="min-w-0 pr-3">
                                    <flux:text variant="strong" class="truncate text-zinc-900 dark:text-zinc-100">
                                        {{ $participant->participant_title ?: $participant->program->title }}
                                    </flux:text>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                        <span>{{ $participant->student?->name ?? __('Kelompok') }}</span>
                                        <span>•</span>
                                        <flux:badge size="sm" color="zinc">{{ $participant->program->programType?->name }}</flux:badge>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <flux:button href="{{ route('dpl.programs.index') }}" wire:navigate variant="filled" size="sm" class="min-h-[38px] text-xs">
                                        {{ __('Tinjau') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>

            {{-- Queue 2: Daily Logs Needing Verification --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <flux:heading size="lg">{{ __('Logbook Menunggu Verifikasi') }}</flux:heading>
                        @if($pendingLogsCount > 0)
                            <flux:badge color="blue" size="sm">{{ $pendingLogsCount }}</flux:badge>
                        @endif
                    </div>
                    <flux:button href="{{ route('dpl.logbook.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                        {{ __('Buka Halaman Logbook') }}
                    </flux:button>
                </div>

                <flux:separator variant="subtle" />

                @if($pendingLogs->isEmpty())
                    <div class="py-8 text-center">
                        <flux:icon.check-circle class="mx-auto size-10 text-emerald-400 dark:text-emerald-500" />
                        <flux:text class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('Semua logbook harian mahasiswa telah diverifikasi.') }}
                        </flux:text>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($pendingLogs as $log)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                                <div class="min-w-0 pr-3">
                                    <div class="flex items-center gap-2">
                                        <flux:text variant="strong" class="text-zinc-900 dark:text-zinc-100">
                                            {{ $log->student?->name }}
                                        </flux:text>
                                        <flux:badge size="sm" color="zinc">{{ $log->total_hours_formatted }}</flux:badge>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                        <span>{{ $log->date->translatedFormat('d M Y') }}</span>
                                        <span>•</span>
                                        <span class="truncate">{{ $log->important_notes ?: ($log->activities->first()?->activity_description ?: __('Aktivitas harian')) }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <flux:button href="{{ route('dpl.logbook.index') }}" wire:navigate variant="filled" size="sm" class="min-h-[38px] text-xs">
                                        {{ __('Verifikasi') }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- Group Readiness & Grading Status --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Group Readiness (LRK & LPK) --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Kesiapan Dokumen Kelompok') }}</flux:heading>
                        <flux:text class="text-xs">{{ __('Status penguncian LRK dan LPK pada kelompok bimbingan.') }}</flux:text>
                    </div>
                    <flux:button href="{{ route('dpl.documents.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                        {{ __('Dokumen Tim') }}
                    </flux:button>
                </div>

                <flux:separator variant="subtle" />

                <div class="space-y-3">
                    @foreach($activeGroups as $g)
                        <div class="rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <flux:text variant="strong">{{ $g->name }}</flux:text>
                                    <span class="text-xs text-zinc-500">({{ $g->village }})</span>
                                </div>
                                <flux:text class="text-xs text-zinc-500">
                                    {{ __(':count Mahasiswa', ['count' => $g->students->count()]) }}
                                </flux:text>
                            </div>
                            <div class="mt-2.5 flex items-center justify-between border-t border-zinc-200/50 pt-2 dark:border-zinc-700/50 text-xs">
                                <div class="flex items-center gap-2">
                                    <span>{{ __('LRK:') }}</span>
                                    @if($g->is_lrk_locked)
                                        <flux:badge color="green" size="sm">{{ __('Terkunci') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('Terbuka') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span>{{ __('LPK:') }}</span>
                                    @if($g->is_lpk_locked)
                                        <flux:badge color="green" size="sm">{{ __('Terkunci') }}</flux:badge>
                                    @else
                                        <flux:badge color="zinc" size="sm">{{ __('Terbuka') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="text-zinc-500">
                                    <span>{{ __('Kordes:') }}</span>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $g->studentLeader?->name ?? __('Belum ada') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>

            {{-- SIAP Grading Progress --}}
            <flux:card class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Progres Penilaian SIAP') }}</flux:heading>
                        <flux:text class="text-xs">{{ __('Kelengkapan penilaian akhir untuk mahasiswa bimbingan.') }}</flux:text>
                    </div>
                    <flux:button href="{{ route('dpl.grades.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                        {{ __('Input Nilai') }}
                    </flux:button>
                </div>

                <flux:separator variant="subtle" />

                <div class="space-y-4 py-2">
                    <div class="flex items-baseline justify-between">
                        <div>
                            <flux:heading size="xl">{{ $gradedStudentsCount }} / {{ $totalStudents }}</flux:heading>
                            <flux:text class="text-xs text-zinc-500">{{ __('Mahasiswa telah memiliki nilai akhir') }}</flux:text>
                        </div>
                        <flux:badge :color="$gradingPercentage === 100 ? 'green' : ($gradingPercentage > 0 ? 'amber' : 'zinc')" size="md">
                            {{ $gradingPercentage }}% {{ __('Selesai') }}
                        </flux:badge>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <div class="h-full rounded-full transition-all duration-300 {{ $gradingPercentage === 100 ? 'bg-emerald-500' : 'bg-blue-600' }}" style="width: {{ $gradingPercentage }}%"></div>
                    </div>

                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{ __('Penilaian mencakup komponen Partisipatif, Proyek, Tugas, Quiz, UTS, dan UAS sesuai bobot yang ditentukan.') }}
                    </flux:text>

                    <div class="pt-2">
                        <flux:button href="{{ route('dpl.grades.index') }}" wire:navigate variant="filled" size="sm" icon="pencil-square" class="min-h-[44px] w-full sm:w-auto">
                            {{ __('Buka Halaman Penilaian Mahasiswa') }}
                        </flux:button>
                    </div>
                </div>
            </flux:card>
        </div>

        {{-- Quick Navigation Shortcuts --}}
        <flux:card class="p-4">
            <flux:heading size="base" class="mb-3">{{ __('Aksi Cepat Dosen Pembimbing') }}</flux:heading>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-5">
                <flux:button href="{{ route('dpl.groups.index') }}" wire:navigate variant="filled" icon="user-group" class="min-h-[44px] justify-center sm:justify-start">
                    {{ __('Daftar Mahasiswa') }}
                </flux:button>
                <flux:button href="{{ route('dpl.programs.index') }}" wire:navigate variant="ghost" icon="light-bulb" class="min-h-[44px] justify-center sm:justify-start">
                    {{ __('Review Program') }}
                </flux:button>
                <flux:button href="{{ route('dpl.logbook.index') }}" wire:navigate variant="ghost" icon="book-open" class="min-h-[44px] justify-center sm:justify-start">
                    {{ __('Verifikasi Logbook') }}
                </flux:button>
                <flux:button href="{{ route('dpl.mentoring.index') }}" wire:navigate variant="ghost" icon="clipboard-document-list" class="min-h-[44px] justify-center sm:justify-start">
                    {{ __('Buku Bimbingan') }}
                </flux:button>
                <flux:button href="{{ route('dpl.grades.index') }}" wire:navigate variant="ghost" icon="academic-cap" class="min-h-[44px] justify-center sm:justify-start">
                    {{ __('Nilai SIAP') }}
                </flux:button>
            </div>
        </flux:card>
    @endif
</div>
