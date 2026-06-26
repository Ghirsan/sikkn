<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- ═══ 1. HERO: PROGRESS & KESIAPAN ═══ --}}
    <flux:card class="!p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.document-text variant="mini" class="text-blue-500" />
                    <flux:heading size="lg">{{ __('Kesiapan LRK') }}</flux:heading>
                    <flux:badge :color="$allApproved ? 'green' : 'zinc'" size="sm">
                        {{ $allApproved ? __('Siap Cetak') : __('Belum Siap') }}
                    </flux:badge>
                </div>
                <flux:text class="text-sm">
                    {{ $approvedCount }}/{{ $totalParticipants }} {{ __('rencana program disetujui') }}
                    @if($isLeader)
                        · {{ $filledReportFields }}/{{ $totalReportFields }} {{ __('bagian laporan terisi') }}
                    @endif
                </flux:text>
                {{-- Progress Bar --}}
                <div class="mt-3 w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all duration-500 {{ $allApproved ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($isLeader)
                    <flux:button href="{{ route('lrk.form') }}" wire:navigate variant="filled" size="sm" icon="pencil-square">
                        {{ __('Isi Laporan LRK') }}
                    </flux:button>
                @endif
                @if($allApproved && $group)
                    <flux:button href="{{ route('lrk.pdf', $group) }}" target="_blank" icon="document-arrow-down" variant="primary" size="sm">
                        {{ __('Cetak PDF') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    @if(!$allApproved)
        <flux:callout variant="warning" icon="information-circle">
            <flux:callout.heading>{{ __('PDF LRK Belum Siap') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Tombol cetak akan aktif setelah seluruh rencana program kerja disetujui DPL.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- ═══ 2. MONITORING STATUS ANGGOTA TIM ═══ --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('Status Anggota Tim') }}</flux:heading>
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Jumlah Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Progress') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($memberSummary as $member)
                        <flux:table.row :key="$member->student->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $member->student->name }}</span>
                                    @if($group && $group->student_leader_id === $member->student->id)
                                        <flux:badge size="sm" color="blue" inset="top bottom">{{ __('Ketua') }}</flux:badge>
                                    @endif
                                </div>
                                <div class="text-xs text-zinc-500">{{ $member->student->nim }} · {{ $member->student->prodi }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <span class="text-sm">{{ $member->total }} {{ __('program') }}</span>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($member->total > 0)
                                    <div class="w-20 bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full bg-green-500" style="width: {{ $member->total > 0 ? round(($member->approved / $member->total) * 100) : 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-zinc-500 mt-0.5">{{ $member->approved }}/{{ $member->total }}</span>
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($member->overallStatus === 'approved')
                                    <flux:badge size="sm" color="green" inset="top bottom">{{ __('Selesai') }}</flux:badge>
                                @elseif($member->overallStatus === 'incomplete')
                                    <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Program Belum Lengkap') }}</flux:badge>
                                @elseif($member->overallStatus === 'revision')
                                    <flux:badge size="sm" color="red" inset="top bottom">{{ __('Ada Revisi') }}</flux:badge>
                                @elseif($member->overallStatus === 'submitted')
                                    <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Menunggu Review') }}</flux:badge>
                                @elseif($member->overallStatus === 'draft')
                                    <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Draft') }}</flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Belum Ada Program') }}</flux:badge>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    {{-- ═══ 3. DAFTAR RENCANA PROGRAM KELOMPOK ═══ --}}
    <div>
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-4">
            <flux:heading size="lg">{{ __('Rincian Rencana Program Kelompok') }}</flux:heading>
            
            <div class="flex flex-col sm:flex-row items-center gap-2 w-full xl:w-auto">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Cari judul atau kode...') }}" class="w-full sm:w-48" />
                
                <flux:select wire:model.live="filterType" size="sm" class="w-full sm:w-40">
                    <flux:select.option value="">Semua Jenis Program</flux:select.option>
                    <flux:select.option value="multidisiplin">Multidisiplin</flux:select.option>
                    <flux:select.option value="sosial_kemasyarakatan">Sosial Kemasyarakatan</flux:select.option>
                    <flux:select.option value="lainnya">Lainnya</flux:select.option>
                </flux:select>
                
                <flux:select wire:model.live="filterStatus" size="sm" class="w-full sm:w-40">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    @foreach(\App\Enums\ProgramStatus::cases() as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <flux:card>
            @if($paginatedParticipants->isEmpty())
                <x-empty-state icon="document-text" :heading="__('Belum Ada Program')" :description="__('Belum ada data program yang sesuai dengan filter.')" />
            @else
                <flux:table :paginate="$paginatedParticipants">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Kode') }}</flux:table.column>
                        <flux:table.column>{{ __('Program') }}</flux:table.column>
                        <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                        <flux:table.column>{{ __('Penanggung Jawab') }}</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'execution_date'" :direction="$sortDirection" wire:click="sort('execution_date')">{{ __('Jadwal') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($paginatedParticipants as $participant)
                            <flux:table.row :key="$participant->id">
                                <flux:table.cell>
                                    <span class="text-sm font-semibold text-neutral-600 dark:text-neutral-400">{{ $participant->participant_code }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <span class="font-medium text-zinc-900 dark:text-white">{{ $participant->program->title ?: __('(Belum Diisi)') }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="zinc" inset="top bottom">{{ $participant->program->type->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <span class="text-sm">{{ $participant->student->name }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($participant->execution_date)
                                        <span class="text-sm">{{ $participant->execution_date->format('d M Y') }}</span>
                                    @else
                                        <span class="text-sm text-zinc-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$participant->status->color()" inset="top bottom">{{ $participant->status->label() }}</flux:badge>
                                    @if($participant->revision_note)
                                        <div class="mt-1 text-xs text-red-600">{{ $participant->revision_note }}</div>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button wire:click="viewProgram({{ $participant->program->id }}, {{ $participant->id }})" variant="ghost" size="sm" icon="eye">{{ __('Lihat') }}</flux:button>
                                        @if($participant->student_id === auth()->id() && $participant->isEditable())
                                            <flux:button href="{{ route('programs.form', ['action' => 'edit', 'programId' => $participant->program->id, 'participantId' => $participant->id]) }}" wire:navigate variant="ghost" size="sm" icon="pencil-square">{{ __('Edit') }}</flux:button>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>
    </div>

    {{-- ═══ VIEW PROGRAM MODAL ═══ --}}
    <flux:modal name="view-program" class="md:w-3/4 lg:w-1/2">
        <div class="space-y-6">
            @if($this->selectedProgram)
                <div>
                    <flux:heading size="lg">{{ $this->selectedProgram->title ?: __('(Belum ada judul)') }}</flux:heading>
                    <flux:text class="mt-1">{{ $this->selectedProgram->type->label() }}</flux:text>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Kode Program') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant?->participant_code ?? $this->selectedProgram->getProgramCodeFor($this->selectedParticipant?->student_id) }}</flux:text>
                    </div>
                    @if($this->selectedParticipant && $this->selectedParticipant->execution_date)
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Tanggal Pelaksanaan') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->execution_date->format('d M Y') }}</flux:text>
                    </div>
                    @endif
                    @if($this->selectedParticipant && $this->selectedParticipant->problem_potential)
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Potensi / Permasalahan') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->problem_potential }}</flux:text>
                    </div>
                    @endif
                    @if($this->selectedParticipant && $this->selectedParticipant->location)
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Lokasi / Narsum') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->location }}</flux:text>
                    </div>
                    @endif
                    @if($this->selectedParticipant && $this->selectedParticipant->method)
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Metode Pelaksanaan') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->method }}</flux:text>
                    </div>
                    @endif
                    @if($this->selectedParticipant && $this->selectedParticipant->target_audience)
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Kelompok Sasaran') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->target_audience }}</flux:text>
                    </div>
                    @endif
                    @if($this->selectedParticipant && $this->selectedParticipant->output_target)
                    <div class="sm:col-span-2">
                        <flux:text variant="strong" class="mb-1">{{ __('Luaran (Output)') }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->output_target }}</flux:text>
                    </div>
                    @endif
                </div>

                @if($this->selectedParticipant)
                    <flux:separator variant="subtle" />
                    <div>
                        <flux:heading size="md" class="mb-2">{{ __('Peran Mahasiswa') }}</flux:heading>
                        <div class="flex items-center gap-2 mb-1">
                            <flux:text variant="strong">{{ $this->selectedParticipant->student->name }}</flux:text>
                        </div>
                        @if($this->selectedParticipant->role_in_program)
                            <flux:text class="text-sm">{{ __('Peran') }}: {{ $this->selectedParticipant->role_in_program }}</flux:text>
                        @endif
                        @if($this->selectedParticipant->responsibility)
                            <flux:text class="text-sm mt-1">{{ $this->selectedParticipant->responsibility }}</flux:text>
                        @endif

                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Status Rencana') }}</flux:text>
                                <flux:badge size="sm" :color="$this->selectedParticipant->status->color()" class="mt-1">{{ $this->selectedParticipant->status->label() }}</flux:badge>
                                @if($this->selectedParticipant->revision_note)
                                    <div class="mt-1 text-xs text-red-600">{{ __('Revisi') }}: {{ $this->selectedParticipant->revision_note }}</div>
                                @endif
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Status Laporan') }}</flux:text>
                                <flux:badge size="sm" :color="$this->selectedParticipant->lpk_status->color()" class="mt-1">{{ $this->selectedParticipant->lpk_status->label() }}</flux:badge>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

</div>
