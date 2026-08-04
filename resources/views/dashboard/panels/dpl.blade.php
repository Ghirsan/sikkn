{{-- DPL Dashboard Panel --}}
{{-- DPL: Melihat daftar mahasiswa bimbingan, approve/revisi program, --}}
{{-- melihat dokumen lengkap tim, form penilaian setelah periode selesai --}}

@php
    $user = auth()->user();
    $group = $user->group?->load(['students', 'period', 'dpls', 'programs.student', 'mentoringLogs']);

    // Student stats
    $students = $group?->students ?? collect();
    $totalStudents = $students->count();

    // Program stats
    $participants = $group 
        ? \App\Models\ProgramParticipant::whereHas('program', fn($q) => $q->where('group_id', $group->id))->with('program')->get() 
        : collect();
    $pendingPrograms = $participants->where('status', \App\Enums\ProgramStatus::Submitted);
    $approvedPrograms = $participants->where('status', \App\Enums\ProgramStatus::Approved);
    $revisionPrograms = $participants->where('status', \App\Enums\ProgramStatus::NeedsRevision);

    // Daily log stats
    $studentIds = $students->pluck('id');
    $dailyLogs = $studentIds->isNotEmpty()
        ? \App\Models\DailyLog::whereIn('student_id', $studentIds)->get()
        : collect();
    $pendingLogs = $dailyLogs->where('status', \App\Enums\LogStatus::Pending);
    $approvedLogs = $dailyLogs->where('status', \App\Enums\LogStatus::Approved);

    // Mentoring log stats
    $mentoringLogs = $group?->mentoringLogs ?? collect();
    $pendingMentoring = $mentoringLogs->where('status', \App\Enums\LogStatus::Pending);
    $reviewedMentoring = $mentoringLogs->where('status', \App\Enums\LogStatus::Approved);

    // Document readiness
    $totalParticipants = $participants->count();
    $approvedCount = $approvedPrograms->count();
    $allApproved = $totalParticipants > 0 && $approvedCount === $totalParticipants;
@endphp

{{-- Stat Cards --}}
<div class="grid auto-rows-min gap-4 md:grid-cols-4">
    <x-stat-card icon="academic-cap" color="green" :label="__('Mahasiswa')" :value="$totalStudents" />
    <x-stat-card icon="light-bulb" color="amber" :label="__('Program Review')" :value="$pendingPrograms->count()" />
    <x-stat-card icon="book-open" color="blue" :label="__('Logbook Pending')" :value="$pendingLogs->count()" />
    <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Mentoring Pending')" :value="$pendingMentoring->count()" />
</div>

{{-- Two-Column Layout --}}
<div class="grid gap-4 md:grid-cols-2">
    {{-- Program Perlu Review --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Program Menunggu Review') }}</flux:heading>
            <flux:badge color="amber" size="sm">{{ $pendingPrograms->count() }}</flux:badge>
        </div>

        <flux:separator />

        @if($pendingPrograms->isEmpty())
            <flux:text class="py-4 text-center">{{ __('Tidak ada program yang menunggu review saat ini.') }}</flux:text>
        @else
            <div class="space-y-3">
                @foreach($pendingPrograms->take(5) as $participant)
                    <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                        <div>
                            <flux:text variant="strong">{{ $participant->program->title }}</flux:text>
                            <div class="flex items-center gap-2 mt-1">
                                <flux:text class="text-xs">{{ $participant->student?->name ?? __('Kelompok') }}</flux:text>
                                <flux:badge size="sm" color="zinc">{{ $participant->program->type->label() }}</flux:badge>
                            </div>
                        </div>
                        <flux:badge size="sm" color="amber">{{ __('Diajukan') }}</flux:badge>
                    </div>
                @endforeach
                @if($pendingPrograms->count() > 5)
                    <flux:text class="text-center text-xs">{{ __('dan :count lainnya...', ['count' => $pendingPrograms->count() - 5]) }}</flux:text>
                @endif
            </div>
        @endif

        @if($pendingPrograms->isNotEmpty())
            <div class="mt-4">
                <flux:button href="{{ route('dpl.programs.index') }}" wire:navigate variant="filled" size="sm" icon="arrow-right" class="w-full justify-center">{{ __('Review Semua Program') }}</flux:button>
            </div>
        @endif
    </flux:card>

    {{-- Quick Actions --}}
    <flux:card>
        <flux:heading size="lg" class="mb-4">{{ __('Aksi Cepat') }}</flux:heading>
        <div class="grid gap-2">
            <flux:button href="{{ route('dpl.programs.index') }}" wire:navigate variant="filled" icon="light-bulb" class="justify-start">{{ __('Review Program Mahasiswa') }}</flux:button>
            <flux:button href="{{ route('dpl.logbook.index') }}" wire:navigate variant="ghost" icon="book-open" class="justify-start">
                {{ __('Logbook Mahasiswa') }}
                @if($pendingLogs->isNotEmpty())
                    <flux:badge size="sm" color="amber" class="ml-auto">{{ $pendingLogs->count() }}</flux:badge>
                @endif
            </flux:button>
            <flux:button href="{{ route('dpl.mentoring.index') }}" wire:navigate variant="ghost" icon="clipboard-document-list" class="justify-start">
                {{ __('Buku Pembimbingan') }}
                @if($pendingMentoring->isNotEmpty())
                    <flux:badge size="sm" color="amber" class="ml-auto">{{ $pendingMentoring->count() }}</flux:badge>
                @endif
            </flux:button>
            <flux:button href="{{ route('dpl.documents.index') }}" wire:navigate variant="ghost" icon="document-text" class="justify-start">{{ __('Lihat Dokumen Tim') }}</flux:button>
            <flux:button href="{{ route('dpl.groups.index') }}" wire:navigate variant="ghost" icon="user-group" class="justify-start">{{ __('Daftar Mahasiswa Bimbingan') }}</flux:button>
            <flux:button href="{{ route('dpl.grades.index') }}" wire:navigate variant="ghost" icon="clipboard-document-check" class="justify-start">{{ __('Penilaian Mahasiswa') }}</flux:button>
        </div>
    </flux:card>
</div>

{{-- Kelompok Bimbingan --}}
<flux:card>
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Kelompok Bimbingan') }}</flux:heading>
        @if($group)
            <flux:badge color="zinc">{{ $totalStudents }} {{ __('mahasiswa') }}</flux:badge>
        @endif
    </div>

    <flux:separator />

    @if($group)
        <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:text variant="strong">{{ $group->name }}</flux:text>
                <flux:text class="text-sm">{{ $group->location }}</flux:text>
            </div>
            @if($group->period)
                <flux:badge color="zinc" size="sm">{{ $group->period->name ?? __('Periode Aktif') }}</flux:badge>
            @endif
        </div>

        @if($students->isNotEmpty())
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama') }}</flux:table.column>
                    <flux:table.column>{{ __('NIM') }}</flux:table.column>
                    <flux:table.column>{{ __('Prodi') }}</flux:table.column>
                    <flux:table.column>{{ __('Logbook') }}</flux:table.column>
                    <flux:table.column>{{ __('Mentoring') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($students as $student)
                        @php
                            $studentDailyCount = $dailyLogs->where('student_id', $student->id)->count();
                            $studentDailyPending = $dailyLogs->where('student_id', $student->id)->where('status', \App\Enums\LogStatus::Pending)->count();
                            $studentMentoringCount = $mentoringLogs->where('student_id', $student->id)->count();
                            $studentMentoringPending = $mentoringLogs->where('student_id', $student->id)->where('status', \App\Enums\LogStatus::Pending)->count();
                        @endphp
                        <flux:table.row>
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar :name="$student->name" :initials="$student->initials()" size="sm" />
                                <span class="font-medium">{{ $student->name }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $student->nim }}</flux:table.cell>
                            <flux:table.cell>{{ $student->prodi }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:text class="text-sm">{{ $studentDailyCount }}</flux:text>
                                    @if($studentDailyPending > 0)
                                        <flux:badge size="sm" color="amber">{{ $studentDailyPending }} {{ __('pending') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:text class="text-sm">{{ $studentMentoringCount }}</flux:text>
                                    @if($studentMentoringPending > 0)
                                        <flux:badge size="sm" color="amber">{{ $studentMentoringPending }} {{ __('pending') }}</flux:badge>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <flux:text class="py-4 text-center">{{ __('Belum ada mahasiswa dalam kelompok ini.') }}</flux:text>
        @endif
    @else
        <x-empty-state icon="user-group" :heading="__('Belum Ada Kelompok')" :description="__('Belum ada kelompok bimbingan yang ditugaskan. Hubungi P2KKN untuk informasi penugasan.')" />
    @endif
</flux:card>

{{-- Status Dokumen Tim --}}
<flux:card>
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Status Dokumen Tim') }}</flux:heading>
        @if($group)
            <flux:badge :color="$allApproved ? 'green' : 'zinc'" size="sm">{{ $allApproved ? __('Siap PDF') : $approvedCount.'/'.$totalParticipants.' '.__('approved') }}</flux:badge>
        @endif
    </div>

    <flux:separator />

    @if($group)
        <div class="grid gap-4 md:grid-cols-2">
            {{-- LRK --}}
            <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <flux:icon.document-text variant="mini" class="text-blue-500" />
                    <div>
                        <flux:text variant="strong">{{ __('LRK') }}</flux:text>
                        <flux:text class="text-xs">{{ __('Laporan Rencana Kegiatan') }}</flux:text>
                    </div>
                </div>
                <flux:badge :color="$allApproved ? 'green' : 'zinc'" size="sm">{{ $allApproved ? __('Siap Cetak') : __('Belum Siap') }}</flux:badge>
            </div>

            {{-- LPK --}}
            <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50">
                <div class="flex items-center gap-3">
                    <flux:icon.document-check variant="mini" class="text-green-500" />
                    <div>
                        <flux:text variant="strong">{{ __('LPK') }}</flux:text>
                        <flux:text class="text-xs">{{ __('Laporan Pelaksanaan Kegiatan') }}</flux:text>
                    </div>
                </div>
                <flux:badge color="zinc" size="sm">{{ __('Belum Dibuat') }}</flux:badge>
            </div>
        </div>

        @if($approvedPrograms->isNotEmpty())
            <flux:separator />
            <div>
                <flux:text variant="strong" class="mb-2 text-sm">{{ __('Program Disetujui:') }}</flux:text>
                @foreach($approvedPrograms as $participant)
                    <flux:text class="text-sm">• {{ $participant->program->title }} ({{ $participant->student?->name ?? __('Kelompok') }})</flux:text>
                @endforeach
            </div>
        @endif

        @if($revisionPrograms->isNotEmpty())
            <flux:separator />
            <div>
                <flux:text variant="strong" class="mb-2 text-sm text-red-600 dark:text-red-400">{{ __('Program Perlu Revisi:') }}</flux:text>
                @foreach($revisionPrograms as $participant)
                    <flux:text class="text-sm text-red-600 dark:text-red-400">• {{ $participant->program->title }} — {{ $participant->revision_note }}</flux:text>
                @endforeach
            </div>
        @endif
    @else
        <flux:text class="py-4 text-center">{{ __('Dokumen lengkap tim (LRK/LPK) akan tampil di sini setelah Anda ditugaskan ke kelompok.') }}</flux:text>
    @endif
</flux:card>

{{-- Ringkasan Aktivitas Pembimbingan --}}
<div class="grid gap-4 md:grid-cols-2">
    {{-- Logbook Summary --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Ringkasan Logbook') }}</flux:heading>
            <flux:button href="{{ route('dpl.logbook.index') }}" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">{{ __('Lihat Semua') }}</flux:button>
        </div>
        <flux:separator />
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Menunggu Persetujuan') }}</flux:text>
                <flux:badge :color="$pendingLogs->count() > 0 ? 'amber' : 'green'" size="sm">{{ $pendingLogs->count() }}</flux:badge>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Disetujui') }}</flux:text>
                <flux:text variant="strong">{{ $approvedLogs->count() }}</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Total Entri') }}</flux:text>
                <flux:text variant="strong">{{ $dailyLogs->count() }}</flux:text>
            </div>
        </div>
    </flux:card>

    {{-- Mentoring Summary --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Ringkasan Pembimbingan') }}</flux:heading>
            <flux:button href="{{ route('dpl.mentoring.index') }}" wire:navigate variant="ghost" size="sm" icon-trailing="arrow-right">{{ __('Lihat Semua') }}</flux:button>
        </div>
        <flux:separator />
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Menunggu Feedback') }}</flux:text>
                <flux:badge :color="$pendingMentoring->count() > 0 ? 'amber' : 'green'" size="sm">{{ $pendingMentoring->count() }}</flux:badge>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Sudah Difeedback') }}</flux:text>
                <flux:text variant="strong">{{ $reviewedMentoring->count() }}</flux:text>
            </div>
            <div class="flex items-center justify-between">
                <flux:text>{{ __('Total Sesi') }}</flux:text>
                <flux:text variant="strong">{{ $mentoringLogs->count() }}</flux:text>
            </div>
        </div>
    </flux:card>
</div>
