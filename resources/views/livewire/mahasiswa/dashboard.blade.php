<div class="space-y-6">
    @if(!$group)
        {{-- Banner Mahasiswa Belum Masuk Kelompok --}}
        <flux:card class="border-amber-200 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <flux:icon.exclamation-triangle class="size-6 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                    <div>
                        <flux:heading size="base" class="text-amber-900 dark:text-amber-200">
                            {{ __('Belum Dialokasikan ke Kelompok KKN') }}
                        </flux:heading>
                        <flux:text class="text-amber-800/90 dark:text-amber-300/80 mt-1">
                            {{ __('Anda saat ini belum terdaftar di kelompok KKN aktif. Silakan hubungi koordinator P2KKN atau pantau pengumuman pembagian kelompok.') }}
                        </flux:text>
                    </div>
                </div>
                <flux:button href="{{ route('profile.edit') }}" wire:navigate variant="filled" size="sm" class="min-h-[44px] w-full sm:w-auto shrink-0">
                    {{ __('Periksa Profil & Data NIM') }}
                </flux:button>
            </div>
        </flux:card>
    @else
        {{-- Group & Period Information Card --}}
        <flux:card class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="xl">{{ $group->name }}</flux:heading>
                        @if($user->isStudentLeader())
                            <flux:badge color="amber" size="sm" icon="star">
                                {{ __('Kordes / Ketua Kelompok') }}
                            </flux:badge>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        <span class="inline-flex items-center gap-1">
                            <flux:icon.map-pin variant="mini" class="size-4" />
                            {{ $group->village }}, {{ $group->district }}, {{ $group->regency }}
                        </span>
                        @if($group->groupType)
                            <span class="text-zinc-300 dark:text-zinc-600">•</span>
                            <flux:badge size="sm" color="zinc">{{ $group->groupType->name }}</flux:badge>
                        @endif
                    </div>
                </div>

                {{-- Period Countdown Badge --}}
                @if($period)
                    <div class="flex flex-col items-start sm:items-end gap-1">
                        <flux:badge color="blue" size="md">
                            {{ $period->displayName }}
                        </flux:badge>
                        @if($periodStatusLabel)
                            <flux:text class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                {{ $periodStatusLabel }}
                                @if($period->start_date && $period->end_date)
                                    ({{ $period->start_date->translatedFormat('d M') }} - {{ $period->end_date->translatedFormat('d M Y') }})
                                @endif
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <flux:separator variant="subtle" />

            {{-- DPL Contact List --}}
            <div>
                <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                    {{ __('Dosen Pembimbing Lapangan (DPL)') }}
                </flux:text>
                @if($dpls->isEmpty())
                    <flux:text class="text-sm italic text-zinc-400">{{ __('Belum ada DPL yang ditetapkan untuk kelompok ini.') }}</flux:text>
                @else
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($dpls as $dpl)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-2.5 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/40">
                                <div class="min-w-0 pr-2">
                                    <div class="flex items-center gap-1.5">
                                        <flux:text variant="strong" class="truncate text-sm">{{ $dpl->name }}</flux:text>
                                        @if($group->lead_dpl_id === $dpl->id)
                                            <flux:badge color="purple" size="sm" class="text-[10px] px-1.5 py-0">{{ __('Koordinator') }}</flux:badge>
                                        @endif
                                    </div>
                                    @if($dpl->nip)
                                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">NIP: {{ $dpl->nip }}</flux:text>
                                    @endif
                                </div>
                                @if($dpl->whatsappUrl())
                                    <flux:button href="{{ $dpl->whatsappUrl() }}" target="_blank" rel="noopener noreferrer" variant="ghost" size="sm" icon="chat-bubble-left-ellipsis" class="min-h-[38px] shrink-0 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/30" title="{{ __('Hubungi via WhatsApp') }}">
                                        {{ __('WhatsApp') }}
                                    </flux:button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </flux:card>
    @endif

    {{-- Actionable Revision Alert --}}
    @if($programsNeedsRevision->isNotEmpty())
        <flux:card class="border-amber-300 bg-amber-50/70 dark:border-amber-800 dark:bg-amber-950/30">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-2">
                    <flux:icon.exclamation-circle class="size-5 text-amber-600 dark:text-amber-400" />
                    <flux:heading size="base" class="text-amber-900 dark:text-amber-200">
                        {{ __('Perhatian: Terdapat :count Program Kerja Memerlukan Revisi', ['count' => $programsNeedsRevision->count()]) }}
                    </flux:heading>
                </div>
                <div class="space-y-2">
                    @foreach($programsNeedsRevision as $participant)
                        <div class="rounded-lg bg-white/80 p-3 text-sm dark:bg-zinc-900/60 border border-amber-200/60 dark:border-amber-900/40">
                            <div class="flex items-center justify-between">
                                <flux:text variant="strong" class="text-zinc-900 dark:text-zinc-100">
                                    {{ $participant->participant_title ?: $participant->program->title }}
                                </flux:text>
                                <flux:badge color="amber" size="sm">{{ __('Perlu Revisi') }}</flux:badge>
                            </div>
                            @if($participant->revision_note)
                                <div class="mt-1.5 text-xs text-amber-950 dark:text-amber-200/90 bg-amber-100/60 dark:bg-amber-900/30 rounded p-2">
                                    <span class="font-semibold">{{ __('Catatan DPL:') }}</span> {{ $participant->revision_note }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="pt-1">
                    <flux:button href="{{ route('programs.index') }}" wire:navigate variant="filled" size="sm" class="min-h-[44px]">
                        {{ __('Buka Halaman Usulan Program') }}
                    </flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- KPI Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Program Kerja --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Program Kerja') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $programsCount }}</flux:heading>
                </div>
                <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400">
                    <flux:icon.light-bulb class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs">
                <flux:badge size="sm" color="green">{{ $programsApproved }} {{ __('Disetujui') }}</flux:badge>
                @if($programsSubmitted > 0)
                    <flux:badge size="sm" color="amber">{{ $programsSubmitted }} {{ __('Diajukan') }}</flux:badge>
                @endif
                @if($programsDraft > 0)
                    <flux:badge size="sm" color="zinc">{{ $programsDraft }} {{ __('Draft') }}</flux:badge>
                @endif
            </div>
        </flux:card>

        {{-- Card 2: Dokumen Tim (LRK & LPK) --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Status Dokumen') }}
                    </flux:text>
                    <div class="mt-1 flex items-center gap-2">
                        <flux:text variant="strong" class="text-base">{{ __('LRK:') }}</flux:text>
                        @if($group?->is_lrk_locked)
                            <flux:badge color="green" size="sm">{{ __('Terkunci') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Terbuka') }}</flux:badge>
                        @endif
                    </div>
                </div>
                <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400">
                    <flux:icon.document-text class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <flux:text variant="strong">{{ __('LPK:') }}</flux:text>
                    @if($group?->is_lpk_locked)
                        <flux:badge color="green" size="sm">{{ __('Terkunci') }}</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">{{ __('Terbuka') }}</flux:badge>
                    @endif
                </div>
                @if($group)
                    <flux:button href="{{ route('lrk.index') }}" wire:navigate variant="ghost" size="sm" class="text-xs px-2 py-1">
                        {{ __('Detail') }}
                    </flux:button>
                @endif
            </div>
        </flux:card>

        {{-- Card 3: Logbook Harian --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Logbook Terisi') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalDailyLogs }} {{ __('Hari') }}</flux:heading>
                </div>
                <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400">
                    <flux:icon.book-open class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:badge size="sm" color="green">{{ $approvedDailyLogs }} {{ __('Disetujui') }}</flux:badge>
                <flux:text class="font-medium text-zinc-600 dark:text-zinc-400">{{ $totalHoursFormatted }}</flux:text>
            </div>
        </flux:card>

        {{-- Card 4: Mentoring Logs --}}
        <flux:card class="flex flex-col justify-between p-4">
            <div class="flex items-start justify-between">
                <div>
                    <flux:text class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        {{ __('Buku Bimbingan') }}
                    </flux:text>
                    <flux:heading size="xl" class="mt-1">{{ $totalMentoring }} {{ __('Sesi') }}</flux:heading>
                </div>
                <div class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400">
                    <flux:icon.clipboard-document-list class="size-5" />
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between text-xs">
                <flux:badge size="sm" color="purple">{{ $approvedMentoring }} {{ __('Disetujui') }}</flux:badge>
                @if($pendingMentoring > 0)
                    <flux:badge size="sm" color="amber">{{ $pendingMentoring }} {{ __('Menunggu') }}</flux:badge>
                @endif
            </div>
        </flux:card>
    </div>

    {{-- Quick Action Buttons --}}
    <flux:card class="p-4">
        <flux:heading size="base" class="mb-3">{{ __('Aksi Cepat Mahasiswa') }}</flux:heading>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <flux:button href="{{ route('programs.index') }}" wire:navigate variant="filled" icon="plus" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Usulkan Program Kerja') }}
            </flux:button>
            <flux:button href="{{ route('logbook.index') }}" wire:navigate variant="ghost" icon="pencil-square" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Isi Logbook Hari Ini') }}
            </flux:button>
            <flux:button href="{{ route('mentoring-logs.index') }}" wire:navigate variant="ghost" icon="chat-bubble-left-right" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Catat Bimbingan DPL') }}
            </flux:button>
            <flux:button href="{{ route('groups.index') }}" wire:navigate variant="ghost" icon="user-group" class="min-h-[44px] justify-center sm:justify-start">
                {{ __('Lihat Kelompok Saya') }}
            </flux:button>
        </div>
    </flux:card>

    {{-- Two-Column Recent Overview --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Recent Daily Logs --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Aktivitas Logbook Terbaru') }}</flux:heading>
                    <flux:text class="text-xs">{{ __('Entri log harian terakhir yang Anda laporkan.') }}</flux:text>
                </div>
                <flux:button href="{{ route('logbook.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                    {{ __('Semua Logbook') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />

            @if($recentDailyLogs->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon.book-open class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="mt-2 text-sm text-zinc-500">{{ __('Belum ada logbook yang diisi.') }}</flux:text>
                    <div class="mt-3">
                        <flux:button href="{{ route('logbook.form') }}" wire:navigate variant="filled" size="sm" icon="plus" class="min-h-[44px]">
                            {{ __('Mulai Mengisi Logbook') }}
                        </flux:button>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentDailyLogs as $log)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                            <div class="min-w-0 pr-3">
                                <div class="flex items-center gap-2">
                                    <flux:text variant="strong" class="text-zinc-900 dark:text-zinc-100">
                                        {{ $log->date->translatedFormat('l, d F Y') }}
                                    </flux:text>
                                    <flux:badge size="sm" color="zinc">{{ $log->total_hours_formatted }}</flux:badge>
                                </div>
                                <flux:text class="mt-1 line-clamp-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $log->important_notes ?: ($log->activities->first()?->activity_description ?: __('Tidak ada catatan khusus.')) }}
                                </flux:text>
                            </div>
                            <div class="shrink-0">
                                @if($log->status === \App\Enums\LogStatus::Approved)
                                    <flux:badge color="green" size="sm">{{ __('Disetujui') }}</flux:badge>
                                @elseif($log->status === \App\Enums\LogStatus::Pending)
                                    <flux:badge color="amber" size="sm">{{ __('Menunggu') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Draf') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>

        {{-- Recent Programs --}}
        <flux:card class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Program Kerja Terdaftar') }}</flux:heading>
                    <flux:text class="text-xs">{{ __('Program kerja yang Anda ikuti atau usulkan.') }}</flux:text>
                </div>
                <flux:button href="{{ route('programs.index') }}" wire:navigate variant="ghost" size="sm" class="min-h-[38px]">
                    {{ __('Semua Program') }}
                </flux:button>
            </div>

            <flux:separator variant="subtle" />

            @if($recentPrograms->isEmpty())
                <div class="py-8 text-center">
                    <flux:icon.light-bulb class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="mt-2 text-sm text-zinc-500">{{ __('Belum ada program kerja yang didaftarkan.') }}</flux:text>
                    <div class="mt-3">
                        <flux:button href="{{ route('programs.index') }}" wire:navigate variant="filled" size="sm" icon="plus" class="min-h-[44px]">
                            {{ __('Usulkan Program Kerja') }}
                        </flux:button>
                    </div>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($recentPrograms as $participant)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-200/80 p-3 text-sm dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/40">
                            <div class="min-w-0 pr-3">
                                <div class="flex items-center gap-2">
                                    <flux:text variant="strong" class="truncate text-zinc-900 dark:text-zinc-100">
                                        {{ $participant->participant_title ?: $participant->program->title }}
                                    </flux:text>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                    <span>{{ $participant->participant_code ?: $participant->program->program_code }}</span>
                                    <span>•</span>
                                    <span>{{ $participant->program->programType?->name ?: __('Keilmuan') }}</span>
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if($participant->status === \App\Enums\ProgramStatus::Approved)
                                    <flux:badge color="green" size="sm">{{ __('Disetujui') }}</flux:badge>
                                @elseif($participant->status === \App\Enums\ProgramStatus::NeedsRevision)
                                    <flux:badge color="amber" size="sm">{{ __('Revisi') }}</flux:badge>
                                @elseif($participant->status === \App\Enums\ProgramStatus::Submitted)
                                    <flux:badge color="blue" size="sm">{{ __('Diajukan') }}</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">{{ __('Draft') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </flux:card>
    </div>
</div>
