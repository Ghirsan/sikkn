<div class="flex h-full w-full flex-1 flex-col gap-8">

    <div class="w-full">
        <div class="block">
            
            {{-- Program Multidisiplin --}}
            <div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
                    <div class="flex-1">
                        <flux:heading size="lg">{{ __('Program Multidisiplin') }}</flux:heading>
                        <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Isi detail Potensi, Usulan Program, Metode, dan Luaran untuk program multidisiplin kelompok Anda.') }}</flux:text>
                    </div>
                    <svg x-show="open" style="display: none;" class="shrink-0 size-5 text-zinc-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="!open" class="shrink-0 size-5 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse style="display: none;">
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
                                    @if(!$myRole || $myRole->status === \App\Enums\ProgramStatus::Draft || $myRole->status === \App\Enums\ProgramStatus::NeedsRevision)
                                        <flux:button wire:click="openForm({{ $program->id }}, {{ $myRole->id ?? 'null' }})" x-on:click="$flux.modal('program-modal').show()" variant="ghost" size="sm" icon="pencil-square">{{ __('Isi Detail') }}</flux:button>
                                        @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Draft)
                                            <flux:button wire:click="submitLrk({{ $myRole->id }})" variant="ghost" size="sm" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:button>
                                        @endif
                                    @elseif($myRole->status === \App\Enums\ProgramStatus::Approved && ($myRole->lpk_status === \App\Enums\ProgramStatus::Draft || $myRole->lpk_status === \App\Enums\ProgramStatus::NeedsRevision))
                                        <flux:button wire:click="isiLpk({{ $myRole->id }})" x-on:click="$flux.modal('program-modal').show()" variant="filled" size="sm" icon="clipboard-document-check">{{ __('Lapor LPK') }}</flux:button>
                                    @endif
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
            <div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
                    <div class="flex-1">
                        <flux:heading size="lg">{{ __('Program Sosial Kemasyarakatan') }}</flux:heading>
                        <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Buat program sosial kemasyarakatan Anda (Saintek/Soshum). Maksimal 1 program.') }}</flux:text>
                    </div>
                    <svg x-show="open" style="display: none;" class="shrink-0 size-5 text-zinc-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="!open" class="shrink-0 size-5 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse style="display: none;">
                    <div class="pt-4 flex flex-col gap-4">
                        @if($sosmasPrograms->isEmpty())
                            <div class="flex justify-end">
                                <flux:button wire:click="createSosmas" x-on:click="$flux.modal('program-modal').show()" variant="filled" size="sm" icon="plus">
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
                                    @if(!$myRole || $myRole->status === \App\Enums\ProgramStatus::Draft || $myRole->status === \App\Enums\ProgramStatus::NeedsRevision)
                                        <flux:button wire:click="openForm({{ $program->id }}, {{ $myRole->id ?? 'null' }})" x-on:click="$flux.modal('program-modal').show()" variant="ghost" size="sm" icon="pencil-square">{{ __('Edit Detail') }}</flux:button>
                                        @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Draft)
                                            <flux:button wire:click="submitLrk({{ $myRole->id }})" variant="ghost" size="sm" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:button>
                                            <flux:button wire:click="confirmDelete({{ $myRole->id }})" icon="trash" variant="danger" size="sm">{{ __('Hapus') }}</flux:button>
                                        @endif
                                    @elseif($myRole->status === \App\Enums\ProgramStatus::Approved && ($myRole->lpk_status === \App\Enums\ProgramStatus::Draft || $myRole->lpk_status === \App\Enums\ProgramStatus::NeedsRevision))
                                        <flux:button wire:click="isiLpk({{ $myRole->id }})" x-on:click="$flux.modal('program-modal').show()" variant="filled" size="sm" icon="clipboard-document-check">{{ __('Lapor LPK') }}</flux:button>
                                    @endif
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
            <div class="block pt-4 first:pt-0 pb-4 last:pb-0 border-b last:border-b-0 border-zinc-800/10 dark:border-white/10" x-data="{ open: false }">
                <button type="button" @click="open = !open" class="group flex items-center w-full text-start text-sm font-medium justify-between [&>svg]:ms-6 text-zinc-800 dark:text-white cursor-pointer">
                    <div class="flex-1">
                        <flux:heading size="lg">{{ __('Program Lainnya (Bebas)') }}</flux:heading>
                        <flux:text class="text-sm mt-1 text-zinc-500 dark:text-zinc-400">{{ __('Daftar program tambahan Anda.') }}</flux:text>
                    </div>
                    <svg x-show="open" style="display: none;" class="shrink-0 size-5 text-zinc-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.47 6.47a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 1 1-1.06 1.06L10 8.06l-3.72 3.72a.75.75 0 0 1-1.06-1.06l4.25-4.25Z" clip-rule="evenodd"></path>
                    </svg>
                    <svg x-show="!open" class="shrink-0 size-5 text-zinc-300 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse style="display: none;">
                    <div class="pt-4 flex flex-col gap-4">
                        <div class="flex justify-end">
                            <flux:button wire:click="createLainnya" x-on:click="$flux.modal('program-modal').show()" variant="filled" size="sm" icon="plus">
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
                                    @if(!$myRole || $myRole->status === \App\Enums\ProgramStatus::Draft || $myRole->status === \App\Enums\ProgramStatus::NeedsRevision)
                                        <flux:button wire:click="openForm({{ $program->id }}, {{ $myRole->id ?? 'null' }})" x-on:click="$flux.modal('program-modal').show()" variant="ghost" size="sm" icon="pencil-square">{{ __('Edit Detail') }}</flux:button>
                                        @if($myRole && $myRole->status === \App\Enums\ProgramStatus::Draft)
                                            <flux:button wire:click="submitLrk({{ $myRole->id }})" variant="ghost" size="sm" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:button>
                                            @if($program->student_id === Auth::id())
                                                <flux:button wire:click="confirmDelete({{ $myRole->id }})" icon="trash" variant="danger" size="sm">{{ __('Hapus') }}</flux:button>
                                            @endif
                                        @endif
                                    @elseif($myRole->status === \App\Enums\ProgramStatus::Approved && ($myRole->lpk_status === \App\Enums\ProgramStatus::Draft || $myRole->lpk_status === \App\Enums\ProgramStatus::NeedsRevision))
                                        <flux:button wire:click="isiLpk({{ $myRole->id }})" x-on:click="$flux.modal('program-modal').show()" variant="filled" size="sm" icon="clipboard-document-check">{{ __('Lapor LPK') }}</flux:button>
                                    @endif
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

    {{-- Dynamic Modal Form --}}
    <flux:modal name="program-modal" class="md:w-3/4 lg:w-1/2">
        <form wire:submit="saveForm" class="flex flex-col gap-4">
            
            @if($formMode === 'edit_program')
                <flux:heading size="lg">{{ $programId ? __('Edit Program (Multidisiplin)') : __('Buat Program Baru') }}</flux:heading>
                
                <flux:input wire:model="title" label="{{ __('Usulan Program') }}" placeholder="{{ __('Contoh: Penyuluhan Kesehatan Masyarakat') }}" />
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:textarea wire:model="problem_potential" label="{{ __('Potensi / Permasalahan') }}" placeholder="{{ __('Sebutkan potensi atau masalah') }}" rows="2" />
                    <flux:textarea wire:model="location" label="{{ __('Lokasi / Narsum') }}" placeholder="{{ __('Sebutkan lokasi atau narasumber') }}" rows="2" />
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:textarea wire:model="method" label="{{ __('Metode Pelaksanaan') }}" placeholder="{{ __('Metode yang digunakan') }}" rows="2" />
                    <flux:textarea wire:model="target_audience" label="{{ __('Kelompok Sasaran') }}" placeholder="{{ __('Siapa kelompok sasarannya?') }}" rows="2" />
                </div>
                
                <flux:textarea wire:model="output_target" label="{{ __('Luaran') }}" placeholder="{{ __('Luaran / Output yang diharapkan') }}" rows="2" />
            
            @elseif($formMode === 'create_individual')
                @if($type === \App\Enums\ProgramType::SosialKemasyarakatan->value)
                    <flux:heading size="lg">{{ $programId ? __('Edit Program Sosmas') : __('Buat Program Sosmas') }}</flux:heading>
                    <flux:input wire:model="title" label="{{ __('Nama Program Sosial Kemasyarakatan') }}" placeholder="{{ __('Contoh: Bakti Sosial Soshum') }}" />
                @else
                    <flux:heading size="lg">{{ $programId ? __('Edit Program Lainnya') : __('Buat Program Lainnya') }}</flux:heading>
                    <flux:input wire:model="title" label="{{ __('Nama Program Lainnya') }}" placeholder="{{ __('Contoh: Lomba 17 Agustus') }}" />
                @endif
                
                <flux:input wire:model="role_in_program" label="{{ __('Peran Anda') }}" placeholder="{{ __('Contoh: Koordinator Lapangan, Pemateri, dll.') }}" class="mt-2" />
                <flux:textarea wire:model="responsibility" label="{{ __('Deskripsi Tugas dan Tanggung Jawab') }}" rows="3" />

            @elseif($formMode === 'edit_peran')
                <flux:heading size="lg">{{ __('Isi Peran (Video Profile)') }}</flux:heading>
                <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-2">
                    <flux:text variant="strong">{{ $title }}</flux:text>
                    <flux:text class="text-xs">{{ __('Silakan isi peran spesifik Anda di dalam program ini.') }}</flux:text>
                </div>
                
                <flux:input wire:model="role_in_program" label="{{ __('Peran Anda') }}" placeholder="{{ __('Contoh: Koordinator Lapangan, Pemateri, dll.') }}" />
                <flux:textarea wire:model="responsibility" label="{{ __('Deskripsi Tugas dan Tanggung Jawab') }}" rows="3" />

            @elseif($formMode === 'lpk')
                <flux:heading size="lg">{{ __('Lapor Pelaksanaan (LPK)') }}</flux:heading>
                <div class="p-3 bg-neutral-100 dark:bg-zinc-800 rounded-lg mb-2">
                    <flux:text variant="strong">{{ $title }}</flux:text>
                </div>

                <flux:textarea wire:model="achievement" label="{{ __('Hasil yang Dicapai') }}" placeholder="{{ __('Ceritakan jalannya program dan hasil nyata yang didapat...') }}" rows="3" />
                <flux:textarea wire:model="obstacle" label="{{ __('Hambatan') }}" placeholder="{{ __('Kendala apa saja yang terjadi di lapangan?') }}" rows="2" />
                <flux:textarea wire:model="solution" label="{{ __('Solusi') }}" placeholder="{{ __('Bagaimana cara mengatasi kendala tersebut?') }}" rows="2" />

            @endif

            <div class="mt-4 flex justify-end gap-2">
                @if($formMode === 'lpk')
                    <flux:button type="button" wire:click="saveLpk" variant="filled">{{ __('Simpan LPK') }}</flux:button>
                @else
                    <flux:button type="submit" variant="filled">{{ __('Simpan LRK') }}</flux:button>
                @endif
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('program-modal').close()">{{ __('Batal') }}</flux:button>
            </div>
        </form>
    </flux:modal>

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
</div>
