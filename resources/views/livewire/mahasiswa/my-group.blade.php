<div class="flex h-full w-full flex-1 flex-col gap-6">
    @if($group)
        {{-- Stat Cards --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <x-stat-card icon="user-group" color="blue" :label="__('Anggota')" :value="$stats['memberCount']" />
            <x-stat-card icon="academic-cap" color="purple" :label="__('DPL')" :value="$stats['dplCount']" />
            <x-stat-card icon="light-bulb" color="amber" :label="__('Program Kerja')" :value="$stats['programCount']" />
            <x-stat-card icon="document-text" color="green" :label="__('Tipe KKN')" :value="$group->type->value" />
        </div>

        {{-- Profil Kelompok & Lokasi --}}
        <div class="grid gap-4 md:grid-cols-2">
            {{-- Profil Kelompok --}}
            <flux:card>
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.identification variant="mini" class="text-blue-500" />
                    <flux:heading size="lg">{{ __('Profil Kelompok') }}</flux:heading>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Nama Kelompok') }}</flux:text>
                        <flux:text variant="strong">{{ $group->name }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Tipe') }}</flux:text>
                        <flux:badge size="sm" :color="$group->type === \App\Enums\GroupType::Tematik ? 'purple' : 'blue'">{{ $group->type->value }}</flux:badge>
                    </div>
                    @if($period)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Periode') }}</flux:text>
                            <flux:text variant="strong">{{ $period->semester }} {{ $period->year }}</flux:text>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Pelaksanaan') }}</flux:text>
                            <flux:text variant="strong">{{ \Carbon\Carbon::parse($period->start_date)->translatedFormat('d M Y') }} – {{ \Carbon\Carbon::parse($period->end_date)->translatedFormat('d M Y') }}</flux:text>
                        </div>
                    @endif
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Status LRK') }}</flux:text>
                        <flux:badge size="sm" :color="$group->is_lrk_locked ? 'green' : 'amber'">
                            <flux:icon :name="$group->is_lrk_locked ? 'lock-closed' : 'lock-open'" variant="micro" class="mr-1" />
                            {{ $stats['lrkStatus'] }}
                        </flux:badge>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Status LPK') }}</flux:text>
                        <flux:badge size="sm" :color="$group->is_lpk_locked ? 'green' : 'amber'">
                            <flux:icon :name="$group->is_lpk_locked ? 'lock-closed' : 'lock-open'" variant="micro" class="mr-1" />
                            {{ $stats['lpkStatus'] }}
                        </flux:badge>
                    </div>
                </div>
            </flux:card>

            {{-- Lokasi Penugasan --}}
            <flux:card>
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon.map-pin variant="mini" class="text-red-500" />
                    <flux:heading size="lg">{{ __('Lokasi Penugasan') }}</flux:heading>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Desa / Kelurahan') }}</flux:text>
                        <flux:text variant="strong">{{ $group->village ?? '-' }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Kecamatan') }}</flux:text>
                        <flux:text variant="strong">{{ $group->district ?? '-' }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Kabupaten / Kota') }}</flux:text>
                        <flux:text variant="strong">{{ $group->regency ?? '-' }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Provinsi') }}</flux:text>
                        <flux:text variant="strong">{{ $group->province ?? '-' }}</flux:text>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Kepala Desa') }}</flux:text>
                        <flux:text variant="strong">{{ $group->village_head ?? '-' }}</flux:text>
                    </div>
                    @if($group->partner_name)
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('Mitra Kerja') }}</flux:text>
                            <flux:text variant="strong">{{ $group->partner_name }}</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- Dosen Pembimbing Lapangan (DPL) --}}
        <flux:card>
            <div class="flex items-center gap-3 mb-4">
                <flux:icon.academic-cap variant="mini" class="text-purple-500" />
                <flux:heading size="lg">{{ __('Dosen Pembimbing Lapangan (DPL)') }}</flux:heading>
            </div>

            @if($dpls->isNotEmpty())
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($dpls as $dpl)
                        <div class="flex items-start gap-4 rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-4">
                            <div class="flex size-10 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/30 shrink-0">
                                <flux:icon.user variant="mini" class="text-purple-600 dark:text-purple-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <flux:text variant="strong" class="truncate">{{ $dpl->name }}</flux:text>
                                    @if($group->lead_dpl_id === $dpl->id)
                                        <flux:badge size="sm" color="purple">{{ __('Lead DPL') }}</flux:badge>
                                    @endif
                                </div>
                                @if($dpl->nip)
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">NIP. {{ $dpl->nip }}</flux:text>
                                @endif
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $dpl->prodi }} · {{ $dpl->fakultas }}</flux:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="academic-cap" :heading="__('Belum Ada DPL')" :description="__('DPL belum ditugaskan untuk kelompok ini.')" />
            @endif
        </flux:card>

        {{-- Daftar Anggota Kelompok --}}
        <flux:card>
            <div class="flex items-center gap-3 mb-4">
                <flux:icon.users variant="mini" class="text-blue-500" />
                <flux:heading size="lg">{{ __('Anggota Kelompok') }}</flux:heading>
                <flux:badge size="sm" color="zinc">{{ $members->count() }} {{ __('orang') }}</flux:badge>
            </div>

            @if($members->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-800/10 dark:border-white/10 text-left">
                                <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400 w-12">{{ __('No') }}</th>
                                <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Nama') }}</th>
                                <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('NIM') }}</th>
                                <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Program Studi') }}</th>
                                <th class="pb-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Fakultas') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/10 dark:divide-white/10">
                            @foreach($members as $index => $member)
                                <tr @class(['bg-blue-50/50 dark:bg-blue-900/10' => $member->id === $currentUser->id])>
                                    <td class="py-3 text-zinc-500 dark:text-zinc-400">{{ $index + 1 }}</td>
                                    <td class="py-3">
                                        <div class="flex items-center gap-2">
                                            <flux:text variant="strong" class="text-zinc-800 dark:text-zinc-200">{{ $member->name }}</flux:text>
                                            @if($group->student_leader_id === $member->id)
                                                <flux:badge size="sm" color="amber">{{ __('Ketua') }}</flux:badge>
                                            @endif
                                            @if($member->id === $currentUser->id)
                                                <flux:badge size="sm" color="blue">{{ __('Anda') }}</flux:badge>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 text-zinc-600 dark:text-zinc-400 font-mono text-xs">{{ $member->nim ?? '-' }}</td>
                                    <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ $member->prodi ?? '-' }}</td>
                                    <td class="py-3 text-zinc-600 dark:text-zinc-400">{{ $member->fakultas ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-empty-state icon="users" :heading="__('Belum Ada Anggota')" :description="__('Kelompok ini belum memiliki anggota.')" />
            @endif
        </flux:card>

        {{-- Ringkasan Program Kerja --}}
        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <flux:icon.light-bulb variant="mini" class="text-amber-500" />
                    <flux:heading size="lg">{{ __('Program Kerja Kelompok') }}</flux:heading>
                    <flux:badge size="sm" color="zinc">{{ $programs->count() }} {{ __('program') }}</flux:badge>
                </div>
                <flux:button variant="ghost" size="sm" icon="arrow-right" href="{{ route('programs.index') }}" wire:navigate>
                    {{ __('Lihat Semua') }}
                </flux:button>
            </div>

            @if($programs->isNotEmpty())
                <div class="space-y-0">
                    @foreach($programsByType as $type => $typePrograms)
                        @php
                            $programType = \App\Enums\ProgramType::tryFrom($type);
                            $typeColor = match($type) {
                                'multidisiplin' => 'purple',
                                'sosial_kemasyarakatan' => 'blue',
                                default => 'zinc',
                            };
                            $typeIcon = match($type) {
                                'multidisiplin' => 'puzzle-piece',
                                'sosial_kemasyarakatan' => 'heart',
                                default => 'squares-2x2',
                            };
                        @endphp

                        <x-accordion
                            :heading="$programType?->label() ?? ucfirst($type)"
                            :description="$typePrograms->count() . ' program'"
                            :defaultOpen="true"
                        >
                            <div class="flex flex-col gap-2">
                                @foreach($typePrograms as $seqIndex => $program)
                                    <div class="flex items-start gap-3 rounded-lg bg-zinc-50 dark:bg-white/5 px-4 py-3">
                                        <flux:badge size="sm" :color="$typeColor" class="shrink-0 mt-0.5">
                                            {{ $program->sequence ?? ($seqIndex + 1) }}
                                        </flux:badge>
                                        <div class="flex-1 min-w-0">
                                            <flux:text variant="strong" class="text-sm">{{ $program->title }}</flux:text>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-accordion>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="light-bulb" :heading="__('Belum Ada Program Kerja')" :description="__('Kelompok ini belum memiliki program kerja.')" />
            @endif
        </flux:card>
    @else
        {{-- No Group Assigned --}}
        <flux:card>
            <x-empty-state
                icon="user-group"
                :heading="__('Belum Tergabung dalam Kelompok')"
                :description="__('Anda belum ditugaskan ke kelompok KKN manapun. Hubungi administrator untuk informasi lebih lanjut.')"
            />
        </flux:card>
    @endif
</div>
