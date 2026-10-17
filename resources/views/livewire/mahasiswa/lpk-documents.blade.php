<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- ═══ 1. HERO: PROGRESS & KESIAPAN ═══ --}}
    <flux:card class="!p-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.document-check variant="mini" class="text-green-500" />
                    <flux:heading size="lg">{{ __('Kesiapan LPK') }}</flux:heading>
                    <flux:badge :color="$allLpkApproved ? 'green' : 'zinc'" size="sm">
                        {{ $allLpkApproved ? __('Siap Cetak') : __('Belum Siap') }}
                    </flux:badge>
                </div>
                <flux:text class="text-sm">
                    {{ $lpkApprovedCount }}/{{ $totalParticipants }} {{ __('laporan pelaksanaan disetujui') }}
                </flux:text>
                {{-- Progress Bar --}}
                <div class="mt-3 w-full">
                    <flux:progress value="{{ $progressPercent }}" color="green" />
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                @if($allLpkApproved && $group)
                    <flux:button href="{{ route('lpk.pdf', $group) }}" target="_blank" icon="document-arrow-down" variant="primary" size="sm" class="w-full sm:w-auto">
                        {{ __('Cetak PDF') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    @if(!$allLrkApproved)
        <flux:callout variant="danger" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('LRK Belum Selesai') }}</flux:callout.heading>
            <flux:callout.text>{{ __('LPK hanya bisa dicetak jika seluruh rencana program kerja (LRK) telah disetujui Dosen KKN terlebih dahulu.') }}</flux:callout.text>
        </flux:callout>
    @elseif(!$allLpkApproved)
        <flux:callout variant="warning" icon="information-circle">
            <flux:callout.heading>{{ __('PDF LPK Belum Siap') }}</flux:callout.heading>
            <flux:callout.text>{{ __('Tombol cetak akan aktif setelah seluruh laporan pelaksanaan program disetujui Dosen KKN.') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- ═══ 2. MONITORING STATUS ANGGOTA TIM ═══ --}}
    <div>
        <flux:heading size="lg" class="mb-4">{{ __('Status Pengisian Laporan Tim') }}</flux:heading>
        <flux:card>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Nama Mahasiswa') }}</flux:table.column>
                    <flux:table.column>{{ __('Jumlah Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Progress LPK') }}</flux:table.column>
                    <flux:table.column>{{ __('Status LPK') }}</flux:table.column>
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
                                    <div class="w-24">
                                        <flux:progress value="{{ $member->total > 0 ? round(($member->approved / $member->total) * 100) : 0 }}" color="green" />
                                    </div>
                                    <span class="text-xs text-zinc-500 mt-1 block">{{ $member->approved }}/{{ $member->total }}</span>
                                @else
                                    <span class="text-xs text-zinc-400">-</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($member->overallStatus === 'approved')
                                    <flux:badge size="sm" color="green" inset="top bottom">{{ __('Selesai') }}</flux:badge>
                                @elseif($member->overallStatus === 'revision')
                                    <flux:badge size="sm" color="red" inset="top bottom">{{ __('Ada Revisi') }}</flux:badge>
                                @elseif($member->overallStatus === 'submitted')
                                    <flux:badge size="sm" color="amber" inset="top bottom">{{ __('Menunggu Review') }}</flux:badge>
                                @elseif($member->overallStatus === 'draft')
                                    <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Belum Dilaporkan') }}</flux:badge>
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

    {{-- ═══ 3. DAFTAR PELAKSANAAN PROGRAM KELOMPOK ═══ --}}
    <div>
        {{-- Heading --}}
        <div class="mb-4">
            <flux:heading size="lg">{{ __('Rincian Pelaksanaan Program Kelompok (LPK)') }}</flux:heading>
        </div>

        {{-- Search bar (justify between) filters, buttons --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="w-full sm:w-72">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Cari judul atau kode...') }}" class="w-full" />
            </div>
            
            <div class="flex flex-col gap-2 w-full sm:flex-row sm:items-center sm:w-auto">
                <flux:select wire:model.live="filterType" size="sm" class="w-full sm:w-48">
                    <option value="">Semua Jenis Program</option>
                    <option value="multidisiplin">Multidisiplin</option>
                    <option value="sosial_kemasyarakatan">Sosial Kemasyarakatan</option>
                    <option value="lainnya">Lainnya</option>
                </flux:select>
                
                <flux:select wire:model.live="filterStatus" size="sm" class="w-full sm:w-48">
                    <option value="">Semua Status</option>
                    @foreach(\App\Enums\ProgramStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
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
                        <flux:table.column>{{ __('Penanggung Jawab') }}</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'execution_date'" :direction="$sortDirection" wire:click="sort('execution_date')">{{ __('Jadwal') }}</flux:table.column>
                        <flux:table.column>{{ __('Status LPK') }}</flux:table.column>
                        <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($paginatedParticipants as $participant)
                            <flux:table.row :key="$participant->id">
                                <flux:table.cell>
                                    <span class="text-sm font-semibold text-neutral-600 dark:text-neutral-400">{{ $participant->participant_code }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($participant->program->programType?->code === 'multidisiplin')
                                        @if($participant->program->isVideoProfile())
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $participant->program->title ?: __('(Tema Belum Diisi)') }}</span>
                                        @else
                                            <div class="text-xs text-zinc-500 mb-0.5">{{ $participant->program->title ?: __('(Tema Belum Diisi)') }}</div>
                                            <span class="font-medium text-zinc-900 dark:text-white">{{ $participant->participant_title ?: __('(Usulan Spesifik Belum Diisi)') }}</span>
                                        @endif
                                    @else
                                        <span class="font-medium text-zinc-900 dark:text-white">{{ $participant->program->title ?: __('(Belum Diisi)') }}</span>
                                    @endif
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
                                    <flux:badge size="sm" :color="$participant->lpk_status->color()" inset="top bottom">{{ $participant->lpk_status->label() }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button wire:click="viewProgram({{ $participant->id }})" variant="ghost" size="sm" icon="eye">{{ __('Lihat') }}</flux:button>
                                        @if($participant->student_id === auth()->id() && $participant->status === \App\Enums\ProgramStatus::Approved)
                                            <flux:button href="{{ route('programs.form', ['action' => 'lpk', 'participantId' => $participant->id]) }}" wire:navigate variant="filled" size="sm" icon="clipboard-document-check">{{ __('Isi Bukti') }}</flux:button>
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
    {{-- ═══ VIEW PROGRAM MODAL ═══ --}}
    <flux:modal name="view-program" scroll="body" class="max-w-2xl w-full">
        <div class="space-y-6">
            @if($this->selectedParticipant)
                @php $prog = $this->selectedParticipant->program; @endphp
                {{-- Header --}}
                <div>
                    <flux:heading size="lg">{{ $prog->title ?: __('(Belum ada judul)') }}</flux:heading>
                    <flux:text class="mt-1">{{ $prog->programType?->name }}</flux:text>
                </div>

                <flux:separator variant="subtle" />

                {{-- Sections --}}
                <div class="space-y-6">
                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Identitas Program') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Kode Program') }}</flux:text>
                                <flux:text>{{ $this->selectedParticipant->participant_code }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Tanggal Pelaksanaan') }}</flux:text>
                                <flux:text>{{ $this->selectedParticipant->execution_date?->format('d M Y') ?? __('Belum diisi') }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Mahasiswa Pelaksana') }}</flux:text>
                                <flux:text>{{ $this->selectedParticipant->student->name }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Status Laporan') }}</flux:text>
                                <flux:badge size="sm" :color="$this->selectedParticipant->lpk_status->color()" class="mt-1">{{ $this->selectedParticipant->lpk_status->label() }}</flux:badge>
                            </div>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Pelaksanaan Program') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            @if($this->selectedParticipant->execution_description)
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ __('Pelaksanaan Kegiatan') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->execution_description }}</flux:text>
                                </div>
                            @endif

                            @if($this->selectedParticipant->achievement)
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ $prog->programType?->code === 'multidisiplin' && $prog->sequence != 3 ? __('Ketercapaian') : __('Hasil') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->achievement }}</flux:text>
                                </div>
                            @endif

                            @if($this->selectedParticipant->obstacle)
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Hambatan') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->obstacle }}</flux:text>
                                </div>
                            @endif

                            @if($this->selectedParticipant->solution)
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Tindak Lanjut / Solusi') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->solution }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Luaran & Dokumentasi') }}</flux:heading>
                        <div>
                            <flux:text variant="strong" class="mb-2">{{ __('Luaran (Output)') }}</flux:text>
                            @if($this->selectedParticipant->outputs->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($this->selectedParticipant->outputs as $output)
                                        <x-program-output-metadata-card
                                            :name="$output->name"
                                            :type="$output->type->value"
                                            :url="$output->url"
                                            :metadata="[]"
                                        />
                                    @endforeach
                                </div>
                            @else
                                <flux:text>{{ __('Belum dilampirkan') }}</flux:text>
                            @endif
                        </div>

                        @if($this->selectedParticipant->documentation_image_path)
                            <div>
                                <flux:text variant="strong" class="mb-2">{{ __('Foto Dokumentasi') }}</flux:text>
                                <flux:card variant="soft" class="overflow-hidden p-0 max-w-md">
                                    <img src="{{ $this->selectedParticipant->documentationImageUrl() }}" alt="{{ $this->selectedParticipant->documentation_caption ?? 'Dokumentasi Program' }}" class="mx-auto block max-h-80 max-w-full object-contain" />
                                    @if($this->selectedParticipant->documentation_caption)
                                        <flux:separator variant="subtle" />
                                        <div class="p-3">
                                            <flux:text variant="subtle" class="text-center text-sm italic">{{ $this->selectedParticipant->documentation_caption }}</flux:text>
                                        </div>
                                    @endif
                                </flux:card>
                            </div>
                        @endif
                    </section>
                </div>
            @endif

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
                <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                    <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Tutup') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

</div>
