<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
        <x-stat-card icon="clipboard-document-list" color="purple" :label="__('Total Sesi')" :value="$stats['total']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Sudah Difeedback')" :value="$stats['reviewed']" />
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Feedback')" :value="$stats['pending']" />
    </div>

    {{-- Mentoring Logs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Catatan Pembimbingan') }}</flux:heading>
        <div class="flex items-center gap-3">
            @if($logs->isNotEmpty())
                <flux:button variant="ghost" size="sm" icon="printer" :href="route('mentoring-logs.pdf', $student)" target="_blank">
                    {{ __('Cetak Buku') }}
                </flux:button>
            @endif
            <flux:button variant="filled" size="sm" icon="plus" href="{{ route('mentoring-logs.form') }}" wire:navigate>
                {{ __('Tambah Catatan') }}
            </flux:button>
        </div>
    </div>

    @if($logs->isEmpty())
        <flux:card>
            <x-empty-state icon="clipboard-document-list" :heading="__('Belum Ada Catatan')" :description="__('Mulai catat aktivitas pembimbingan Anda dengan DPL.')" />
        </flux:card>
    @else
        <div class="flex flex-col gap-4">
            @foreach($logs as $log)
                <flux:card class="flex flex-col gap-4">
                    {{-- Top bar --}}
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-zinc-800/10 dark:border-white/10 pb-4">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-3">
                                <flux:badge size="sm" color="zinc" class="font-medium whitespace-nowrap">
                                    {{ $log->date->translatedFormat('d M Y') }}
                                </flux:badge>
                                <flux:text variant="strong" class="text-sm">
                                    {{ $log->topic }}
                                </flux:text>
                            </div>
                            @if($log->program)
                                <div class="text-xs text-zinc-500 flex items-center gap-1 mt-1">
                                    <flux:icon.folder variant="micro" />
                                    <span>[{{ $log->program->type }}] {{ $log->program->title }}</span>
                                </div>
                            @endif
                        </div>
                        <flux:badge size="sm" :color="$log->status->color()" inset="top bottom">{{ $log->status->label() }}</flux:badge>
                    </div>

                    {{-- Body --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Uraian & Hambatan') }}</flux:text>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400 bg-zinc-50 dark:bg-white/5 rounded-lg p-3 whitespace-pre-wrap">{{ $log->discussion_summary }}</div>
                        </div>
                        <div class="flex flex-col gap-2">
                            <flux:text variant="strong" class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Solusi / Saran DPL') }}</flux:text>
                            @if($log->dpl_feedback)
                                <flux:callout variant="success" icon="check-circle" class="h-full">
                                    <div class="whitespace-pre-wrap">{{ $log->dpl_feedback }}</div>
                                </flux:callout>
                            @else
                                <div class="text-sm text-zinc-400 dark:text-zinc-500 italic bg-zinc-50 dark:bg-white/5 rounded-lg p-3 h-full flex items-center">{{ __('Belum ada feedback dari DPL.') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Bottom info & Actions --}}
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-4 border-t border-zinc-800/10 dark:border-white/10">
                        <div class="flex flex-wrap gap-2">
                            @if($log->target_group)
                                <flux:badge size="sm" color="zinc" icon="user-group">{{ __('Sasaran: ') }} {{ $log->target_group }}</flux:badge>
                            @endif
                            @if($log->student_count)
                                <flux:badge size="sm" color="zinc" icon="users">{{ __('Jml: ') }} {{ $log->student_count }} {{ __('Mahasiswa') }}</flux:badge>
                            @endif
                            @if($log->output)
                                <flux:badge size="sm" color="zinc" icon="document-text">{{ __('Luaran: ') }} {{ $log->output }}</flux:badge>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <flux:button variant="ghost" size="sm" icon="eye" wire:click="viewLog({{ $log->id }})">{{ __('Lihat') }}</flux:button>
                            @if($log->status === \App\Enums\LogStatus::Pending)
                                <flux:button variant="ghost" size="sm" icon="pencil-square" href="{{ route('mentoring-logs.form', ['logId' => $log->id]) }}" wire:navigate>{{ __('Edit') }}</flux:button>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif

    {{-- View Modal --}}
    <flux:modal name="mentoring-view-modal" class="md:w-3/4 lg:w-[60rem]">
        @if($viewLogData)
            <div class="space-y-6 p-2">
                <div class="text-center space-y-1">
                    <flux:heading size="xl" class="font-bold uppercase tracking-wide">{{ __('Buku Pembimbingan') }}</flux:heading>
                    <flux:heading size="lg" class="font-bold uppercase tracking-wide">{{ __('Kuliah Kerja Nyata') }}</flux:heading>
                    <flux:heading size="lg" class="font-bold uppercase tracking-wide">{{ __('Universitas Diponegoro') }}</flux:heading>
                </div>

                <div class="border border-zinc-200 dark:border-zinc-700 p-4 mt-6 text-sm grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <div class="flex"><span class="w-24 font-semibold">Nama</span><span>: {{ $student->name }}</span></div>
                        <div class="flex"><span class="w-24 font-semibold">NIM</span><span>: {{ $student->nim }}</span></div>
                        <div class="flex"><span class="w-24 font-semibold">Prodi</span><span>: {{ $student->prodi }}</span></div>
                        <div class="flex"><span class="w-24 font-semibold">Fakultas</span><span>: {{ $student->fakultas }}</span></div>
                    </div>
                    <div class="space-y-1">
                        <div class="flex"><span class="w-24 font-semibold">Desa</span><span>: {{ $group->village }}</span></div>
                        <div class="flex"><span class="w-24 font-semibold">Kecamatan</span><span>: {{ $group->district }}</span></div>
                        <div class="flex"><span class="w-24 font-semibold">Kabupaten</span><span>: {{ $group->regency }}</span></div>
                        <div class="flex"><span class="w-24 font-semibold">Provinsi</span><span>: {{ $group->province }}</span></div>
                    </div>
                </div>

                <div class="space-y-1 text-sm mt-4">
                    <div class="flex"><span class="w-32 font-semibold">Jenis Program</span><span>: {{ $viewLogData->program ? $viewLogData->program->type : '-' }}</span></div>
                    <div class="flex"><span class="w-32 font-semibold">Judul Program</span><span>: {{ $viewLogData->program ? $viewLogData->program->title : 'Umum / Tidak terikat program spesifik' }}</span></div>
                </div>

                <div class="overflow-x-auto border border-zinc-200 dark:border-zinc-700 mt-4">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-100 dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
                            <tr class="text-left">
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold w-12 text-center">No</th>
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold w-24">Tanggal</th>
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold w-32">Kegiatan</th>
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold">Uraian & Hambatan</th>
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold">Solusi / Saran</th>
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold w-24 text-center">Kelompok Sasaran</th>
                                <th class="p-2 border-r border-zinc-200 dark:border-zinc-700 font-semibold w-20 text-center">Jml Mahasiswa</th>
                                <th class="p-2 font-semibold w-28 text-center">Luaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 text-center align-top">1</td>
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 align-top">{{ $viewLogData->date->translatedFormat('d/m/Y') }}</td>
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 align-top">{{ $viewLogData->topic }}</td>
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 align-top whitespace-pre-wrap">{{ $viewLogData->discussion_summary }}</td>
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 align-top whitespace-pre-wrap">{{ $viewLogData->dpl_feedback ?? '-' }}</td>
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 text-center align-top">{{ $viewLogData->target_group ?? '-' }}</td>
                                <td class="p-2 border-r border-zinc-200 dark:border-zinc-700 text-center align-top">{{ $viewLogData->student_count ?? '-' }}</td>
                                <td class="p-2 text-center align-top">{{ $viewLogData->output ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-between items-end mt-8 px-8 text-sm">
                    <div class="text-center">
                        <div class="mb-16">Dosen KKN</div>
                        <div class="font-semibold underline">{{ $group->leadDpl->name ?? '-' }}</div>
                        <div>NIP. {{ $group->leadDpl->nip ?? '-' }}</div>
                    </div>
                    <div class="text-center">
                        <div class="mb-2">{{ $group->regency ?? 'Semarang' }}, {{ $viewLogData->date->translatedFormat('d F Y') }}</div>
                        <div class="mb-16">Pelaksana</div>
                        <div class="font-semibold underline">{{ $student->name }}</div>
                        <div>NIM. {{ $student->nim }}</div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 mt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
