<div class="flex h-full w-full flex-1 flex-col gap-8">

    <div class="w-full">
        <div class="block">
            
            {{-- Program Multidisiplin --}}
            <div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: true }">
                <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
                    <div class="flex-1">
                        <flux:heading size="lg">{{ __('Program Multidisiplin') }}</flux:heading>
                        <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Isi detail Potensi, Usulan Program, Metode, dan Luaran untuk program multidisiplin kelompok Anda.') }}</flux:text>
                    </div>
                    <flux:icon.chevron-up variant="mini" x-show="open" class="shrink-0 text-zinc-800 dark:text-white" />
                    <flux:icon.chevron-down variant="mini" x-show="!open" style="display: none;" class="shrink-0 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" />
                </button>
                <div x-show="open" x-collapse>
                    <div class="pt-4">
                        <flux:card>
            @if($multidisiplinPrograms->isEmpty())
                <x-empty-state icon="light-bulb" :heading="__('Belum Ada Program')" :description="__('DPL belum menetapkan kuota program multidisiplin.')" />
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Usulan Program') }}</flux:table.column>
                        <flux:table.column>{{ __('Status Rencana (LRK)') }}</flux:table.column>
                        <flux:table.column>{{ __('Status Laporan (LPK)') }}</flux:table.column>
                        <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($multidisiplinPrograms as $program)
                            @php $myRole = $program->participants->first(); @endphp
                            <flux:table.row :key="$program->id">
                                <flux:table.cell>
                                    <span class="font-medium text-neutral-900 dark:text-white">{{ $program->title ?: '(Belum Diisi)' }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole)
                                        <flux:badge size="sm" :color="$myRole->status->color()" inset="top bottom">{{ $myRole->status->label() }}</flux:badge>
                                        @if($myRole->revision_note)
                                            <div class="mt-1 text-xs text-red-600">Revisi: {{ $myRole->revision_note }}</div>
                                        @endif
                                    @else
                                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Belum Diisi') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Approved)
                                        <flux:badge size="sm" :color="$myRole->lpk_status->color()" inset="top bottom">{{ $myRole->lpk_status->label() }}</flux:badge>
                                    @else
                                        <span class="text-sm text-neutral-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button wire:click="viewProgram({{ $program->id }}, {{ $myRole->id ?? 'null' }})" variant="ghost" size="sm" icon="eye">{{ __('Lihat') }}</flux:button>
                                        @if(!$myRole || $myRole->status === \App\Enums\ProgramStatus::Draft || $myRole->status === \App\Enums\ProgramStatus::NeedsRevision)
                                            <flux:button href="{{ route('programs.form', ['action' => 'edit', 'programId' => $program->id, 'participantId' => $myRole->id ?? null]) }}" wire:navigate variant="ghost" size="sm" icon="pencil-square">{{ __('Isi Detail') }}</flux:button>
                                            @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Draft)
                                                <flux:button wire:click="confirmSubmitLrk({{ $myRole->id }})" variant="ghost" size="sm" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:button>
                                            @endif
                                        @elseif($myRole->status === \App\Enums\ProgramStatus::Approved && ($myRole->lpk_status === \App\Enums\ProgramStatus::Draft || $myRole->lpk_status === \App\Enums\ProgramStatus::NeedsRevision))
                                            <flux:button href="{{ route('programs.form', ['action' => 'lpk', 'participantId' => $myRole->id]) }}" wire:navigate variant="filled" size="sm" icon="clipboard-document-check">{{ __('Lapor LPK') }}</flux:button>
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
                </div>
            </div>

            {{-- Program Sosial Kemasyarakatan --}}
            <div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: true }">
                <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
                    <div class="flex-1">
                        <flux:heading size="lg">{{ __('Program Sosial Kemasyarakatan') }}</flux:heading>
                        <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Buat program sosial kemasyarakatan Anda (Saintek/Soshum). Maksimal 1 program.') }}</flux:text>
                    </div>
                    <flux:icon.chevron-up variant="mini" x-show="open" class="shrink-0 text-zinc-800 dark:text-white" />
                    <flux:icon.chevron-down variant="mini" x-show="!open" style="display: none;" class="shrink-0 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" />
                </button>
                <div x-show="open" x-collapse>
                    <div class="pt-4 flex flex-col gap-4">
                        @if($sosmasPrograms->isEmpty())
                            <div class="flex justify-end">
                                <flux:button href="{{ route('programs.form', ['action' => 'create', 'type' => \App\Enums\ProgramType::SosialKemasyarakatan->value]) }}" wire:navigate variant="filled" size="sm" icon="plus">
                                    {{ __('Tambah Program') }}
                                </flux:button>
                            </div>
                        @endif
                        <flux:card>
            @if($sosmasPrograms->isEmpty())
                <x-empty-state icon="users" :heading="__('Belum Ada Program')" :description="__('Silakan buat program Sosial Kemasyarakatan Anda.')" />
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Nama Program') }}</flux:table.column>
                        <flux:table.column>{{ __('Peran Saya') }}</flux:table.column>
                        <flux:table.column>{{ __('Status Rencana (LRK)') }}</flux:table.column>
                        <flux:table.column>{{ __('Status Laporan (LPK)') }}</flux:table.column>
                        <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($sosmasPrograms as $program)
                            @php $myRole = $program->participants->first(); @endphp
                            <flux:table.row :key="$program->id">
                                <flux:table.cell>
                                    <span class="font-medium text-neutral-900 dark:text-white">{{ $program->title }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole && $myRole->role_in_program)
                                        <span class="text-sm">{{ $myRole->role_in_program }}</span>
                                    @else
                                        <span class="text-sm text-neutral-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole)
                                        <flux:badge size="sm" :color="$myRole->status->color()" inset="top bottom">{{ $myRole->status->label() }}</flux:badge>
                                        @if($myRole->revision_note)
                                            <div class="mt-1 text-xs text-red-600">Revisi: {{ $myRole->revision_note }}</div>
                                        @endif
                                    @else
                                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Belum Diisi') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Approved)
                                        <flux:badge size="sm" :color="$myRole->lpk_status->color()" inset="top bottom">{{ $myRole->lpk_status->label() }}</flux:badge>
                                    @else
                                        <span class="text-sm text-neutral-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button wire:click="viewProgram({{ $program->id }}, {{ $myRole->id ?? 'null' }})" variant="ghost" size="sm" icon="eye">{{ __('Lihat') }}</flux:button>
                                        @if(!$myRole || $myRole->status === \App\Enums\ProgramStatus::Draft || $myRole->status === \App\Enums\ProgramStatus::NeedsRevision)
                                            <flux:button href="{{ route('programs.form', ['action' => 'edit', 'programId' => $program->id, 'participantId' => $myRole->id ?? null]) }}" wire:navigate variant="ghost" size="sm" icon="pencil-square">{{ __('Edit Detail') }}</flux:button>
                                            @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Draft)
                                                <flux:button wire:click="confirmSubmitLrk({{ $myRole->id }})" variant="ghost" size="sm" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:button>
                                                <flux:button wire:click="confirmDelete({{ $myRole->id }})" icon="trash" variant="danger" size="sm">{{ __('Hapus') }}</flux:button>
                                            @endif
                                        @elseif($myRole->status === \App\Enums\ProgramStatus::Approved && ($myRole->lpk_status === \App\Enums\ProgramStatus::Draft || $myRole->lpk_status === \App\Enums\ProgramStatus::NeedsRevision))
                                            <flux:button href="{{ route('programs.form', ['action' => 'lpk', 'participantId' => $myRole->id]) }}" wire:navigate variant="filled" size="sm" icon="clipboard-document-check">{{ __('Lapor LPK') }}</flux:button>
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
                </div>
            </div>

            {{-- Program Lainnya --}}
            <div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: true }">
                <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
                    <div class="flex-1">
                        <flux:heading size="lg">{{ __('Program Lainnya (Bebas)') }}</flux:heading>
                        <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Daftar program tambahan Anda.') }}</flux:text>
                    </div>
                    <flux:icon.chevron-up variant="mini" x-show="open" class="shrink-0 text-zinc-800 dark:text-white" />
                    <flux:icon.chevron-down variant="mini" x-show="!open" style="display: none;" class="shrink-0 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" />
                </button>
                <div x-show="open" x-collapse>
                    <div class="pt-4 flex flex-col gap-4">
                        <div class="flex justify-end">
                            <flux:button href="{{ route('programs.form', ['action' => 'create', 'type' => \App\Enums\ProgramType::Lainnya->value]) }}" wire:navigate variant="filled" size="sm" icon="plus">
                                {{ __('Tambah Program') }}
                            </flux:button>
                        </div>
                        <flux:card>
            @if($lainnyaPrograms->isEmpty())
                <x-empty-state icon="document-plus" :heading="__('Belum Ada Program Tambahan')" :description="__('Silakan tambah program jika ada kegiatan di luar program wajib.')" />
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Nama Program') }}</flux:table.column>
                        <flux:table.column>{{ __('Status Rencana (LRK)') }}</flux:table.column>
                        <flux:table.column>{{ __('Status Laporan (LPK)') }}</flux:table.column>
                        <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($lainnyaPrograms as $program)
                            @php $myRole = $program->participants->first(); @endphp
                            <flux:table.row :key="$program->id">
                                <flux:table.cell>
                                    <span class="font-medium text-neutral-900 dark:text-white">{{ $program->title }}</span>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole)
                                        <flux:badge size="sm" :color="$myRole->status->color()" inset="top bottom">{{ $myRole->status->label() }}</flux:badge>
                                        @if($myRole->revision_note)
                                            <div class="mt-1 text-xs text-red-600">Revisi: {{ $myRole->revision_note }}</div>
                                        @endif
                                    @else
                                        <flux:badge size="sm" color="zinc" inset="top bottom">{{ __('Belum Diisi') }}</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Approved)
                                        <flux:badge size="sm" :color="$myRole->lpk_status->color()" inset="top bottom">{{ $myRole->lpk_status->label() }}</flux:badge>
                                    @else
                                        <span class="text-sm text-neutral-400">-</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <div class="flex items-center gap-1">
                                        <flux:button wire:click="viewProgram({{ $program->id }}, {{ $myRole->id ?? 'null' }})" variant="ghost" size="sm" icon="eye">{{ __('Lihat') }}</flux:button>
                                        @if(!$myRole || $myRole->status === \App\Enums\ProgramStatus::Draft || $myRole->status === \App\Enums\ProgramStatus::NeedsRevision)
                                            <flux:button href="{{ route('programs.form', ['action' => 'edit', 'programId' => $program->id, 'participantId' => $myRole->id ?? null]) }}" wire:navigate variant="ghost" size="sm" icon="pencil-square">{{ __('Edit Detail') }}</flux:button>
                                            @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Draft)
                                                <flux:button wire:click="confirmSubmitLrk({{ $myRole->id }})" variant="ghost" size="sm" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:button>
                                                @if($program->student_id === Auth::id())
                                                    <flux:button wire:click="confirmDelete({{ $myRole->id }})" icon="trash" variant="danger" size="sm">{{ __('Hapus') }}</flux:button>
                                                @endif
                                            @endif
                                        @elseif($myRole->status === \App\Enums\ProgramStatus::Approved && ($myRole->lpk_status === \App\Enums\ProgramStatus::Draft || $myRole->lpk_status === \App\Enums\ProgramStatus::NeedsRevision))
                                            <flux:button href="{{ route('programs.form', ['action' => 'lpk', 'participantId' => $myRole->id]) }}" wire:navigate variant="filled" size="sm" icon="clipboard-document-check">{{ __('Lapor LPK') }}</flux:button>
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
                </div>
            </div>

        </div>
    </div>



    {{-- Delete Confirmation Modal --}}
    <flux:modal name="delete-participant" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Program?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Anda akan menghapus data program ini.') }}<br>
                    {{ __('Tindakan ini tidak dapat dibatalkan.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="deleteParticipant" variant="danger">{{ __('Hapus Program') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Submit Confirmation Modal --}}
    <flux:modal name="submit-lrk" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Ajukan Program?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Anda akan mengajukan rencana kegiatan ini (LRK) ke DPL.') }}<br>
                    {{ __('Setelah diajukan, Anda tidak dapat mengubahnya hingga direview.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="submitLrk" variant="primary">{{ __('Ya, Ajukan') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- View Program Modal --}}
    <flux:modal name="view-program" class="md:w-3/4 lg:w-1/2">
        <div class="space-y-6">
            @if($this->selectedProgram)
                <div>
                    <flux:heading size="lg">{{ $this->selectedProgram->title ?: __('(Belum ada judul)') }}</flux:heading>
                    <flux:text class="mt-1">
                        {{ $this->selectedProgram->type->label() }}
                    </flux:text>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Potensi / Permasalahan') }}</flux:text>
                        <flux:text>{{ $this->selectedProgram->problem_potential ?: '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Lokasi / Narsum') }}</flux:text>
                        <flux:text>{{ $this->selectedProgram->location ?: '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Metode Pelaksanaan') }}</flux:text>
                        <flux:text>{{ $this->selectedProgram->method ?: '-' }}</flux:text>
                    </div>
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Kelompok Sasaran') }}</flux:text>
                        <flux:text>{{ $this->selectedProgram->target_audience ?: '-' }}</flux:text>
                    </div>
                    <div class="sm:col-span-2">
                        <flux:text variant="strong" class="mb-1">{{ __('Luaran (Output)') }}</flux:text>
                        <flux:text>{{ $this->selectedProgram->output_target ?: '-' }}</flux:text>
                    </div>
                </div>

                @if($this->selectedParticipant)
                    <flux:separator variant="subtle" />
                    <div>
                        <flux:heading size="md" class="mb-2">{{ __('Peran Anda') }}</flux:heading>
                        <flux:text variant="strong" class="mb-1">{{ $this->selectedParticipant->role_in_program ?: '-' }}</flux:text>
                        <flux:text>{{ $this->selectedParticipant->responsibility ?: '-' }}</flux:text>
                        
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Status Rencana') }}</flux:text>
                                <flux:badge size="sm" :color="$this->selectedParticipant->status->color()" class="mt-1">{{ $this->selectedParticipant->status->label() }}</flux:badge>
                                @if($this->selectedParticipant->revision_note)
                                    <div class="mt-1 text-xs text-red-600">Revisi: {{ $this->selectedParticipant->revision_note }}</div>
                                @endif
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Status Laporan') }}</flux:text>
                                <flux:badge size="sm" :color="$this->selectedParticipant->lpk_status->color()" class="mt-1">{{ $this->selectedParticipant->lpk_status->label() }}</flux:badge>
                            </div>
                        </div>
                    </div>
                    
                    @if($this->selectedParticipant->lpk_status !== \App\Enums\ProgramStatus::Draft)
                        <flux:separator variant="subtle" />
                        <div>
                            <flux:heading size="md" class="mb-2">{{ __('Laporan Pelaksanaan') }}</flux:heading>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Hasil yang Dicapai') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->achievement ?: '-' }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Hambatan') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->obstacle ?: '-' }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Solusi') }}</flux:text>
                                    <flux:text>{{ $this->selectedParticipant->solution ?: '-' }}</flux:text>
                                </div>
                            </div>
                        </div>
                    @endif
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
