<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Review')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="arrow-path" color="red" :label="__('Revisi')" :value="$stats['revision']" />
        <x-stat-card icon="document-text" color="blue" :label="__('Total')" :value="$stats['total']" />
    </div>


    {{-- Filter Tema Multidisiplin --}}
    <div class="flex justify-end">
        @if($allGroups->count() > 1)
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-40 sm:w-48">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        @endif
    </div>

    {{-- Tema Multidisiplin Management --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <flux:icon.puzzle-piece variant="mini" class="text-purple-500" />
                <flux:heading size="lg">{{ __('Tema Multidisiplin') }}</flux:heading>
            </div>
            @if($multidisiplinThemes->isNotEmpty())
                <flux:badge color="purple" size="sm">{{ $multidisiplinThemes->count() }} {{ __('tema') }}</flux:badge>
            @endif
        </div>

        <flux:separator />

        @if(!$canManageThemes)
            {{-- Prompt to select a group first --}}
            <div class="py-6 text-center">
                <flux:icon.arrow-up-circle variant="outline" class="mx-auto mb-2 size-8 text-zinc-400" />
                <flux:text class="text-zinc-500">{{ __('Pilih kelompok terlebih dahulu untuk mengelola tema multidisiplin.') }}</flux:text>
            </div>
        @else
            @if($multidisiplinThemes->isEmpty())
                <x-empty-state
                    icon="puzzle-piece"
                    :heading="__('Belum Ada Tema')"
                    :description="__('Tambahkan tema multidisiplin agar mahasiswa dapat memilih dan bergabung.')"
                />
            @else
                <div class="space-y-2">
                    @foreach($multidisiplinThemes as $theme)
                        <div class="flex items-center justify-between rounded-lg bg-neutral-50 px-4 py-3 dark:bg-zinc-700/50" wire:key="theme-{{ $theme->id }}">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <flux:badge color="purple" size="sm" class="shrink-0">{{ $theme->sequence }}</flux:badge>

                                @if($editingThemeId === $theme->id)
                                    <form wire:submit="saveEditTheme" class="flex items-center gap-2 flex-1">
                                        <flux:input wire:model="editingThemeTitle" size="sm" class="flex-1" autofocus />
                                        <flux:button type="submit" size="sm" variant="filled" icon="check">{{ __('Simpan') }}</flux:button>
                                        <flux:button wire:click="cancelEditTheme" size="sm" variant="ghost" icon="x-mark" />
                                    </form>
                                @else
                                    <flux:text variant="strong" class="truncate">{{ $theme->title }}</flux:text>
                                @endif
                            </div>

                            @if($editingThemeId !== $theme->id)
                                <div class="flex items-center gap-2 shrink-0 ml-3">
                                    @if($theme->participants_count > 0)
                                        <flux:badge size="sm" color="green">{{ $theme->participants_count }} {{ __('mahasiswa') }}</flux:badge>
                                    @endif
                                    <flux:button wire:click="startEditTheme({{ $theme->id }})" size="sm" variant="ghost" icon="pencil-square" />
                                    @if($theme->participants_count > 0)
                                        <flux:button size="sm" variant="ghost" icon="trash" disabled tooltip="{{ __('Tidak dapat dihapus karena sudah ada mahasiswa yang bergabung') }}" />
                                    @else
                                        <flux:button wire:click="deleteTheme({{ $theme->id }})" wire:confirm="{{ __('Hapus tema ini?') }}" size="sm" variant="ghost" icon="trash" class="text-red-500 hover:text-red-700" />
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Add new theme form --}}
            <form wire:submit="addTheme" class="mt-4 flex items-start gap-2">
                <div class="flex-1">
                    <flux:input wire:model="newThemeTitle" size="sm" placeholder="{{ __('Masukkan judul tema multidisiplin baru...') }}" />
                    @error('newThemeTitle') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                </div>
                <flux:button type="submit" size="sm" variant="filled" icon="plus">{{ __('Tambah') }}</flux:button>
            </form>
        @endif
    </flux:card>

    {{-- Programs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full max-w-sm">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" size="sm" placeholder="{{ __('Cari mahasiswa, NIM, atau judul program...') }}" clearable />
        </div>
        <div class="flex flex-wrap gap-4">

            <flux:select wire:model.live="filterType" size="sm" class="w-40 sm:w-48">
                <option value="">{{ __('Semua Jenis') }}</option>
                @foreach(\App\Enums\ProgramType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus" size="sm" class="w-40 sm:w-48">
                <option value="">{{ __('Semua Status') }}</option>
                <option value="submitted">{{ __('Menunggu Review') }}</option>
                <option value="approved">{{ __('Disetujui') }}</option>
                <option value="needs_revision">{{ __('Revisi') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
            </flux:select>
        </div>
    </div>

    <flux:card>

        @if($participants->isEmpty())
            <x-empty-state icon="light-bulb" :heading="__('Tidak Ada Program')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa & Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Status Rencana') }}</flux:table.column>
                    <flux:table.column>{{ __('Status Laporan') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($participants as $participant)
                        <flux:table.row :key="$participant->id">
                            <flux:table.cell>
                                <span class="font-medium">{{ $participant->student?->name ?? __('Kelompok') }}</span>
                                <div class="text-xs text-neutral-500">{{ $participant->program->group->name }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ $participant->program->title }}</div>
                                <flux:badge size="sm" color="zinc" class="mt-1">{{ $participant->program->type->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$participant->status->color()" inset="top bottom">{{ $participant->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$participant->lpk_status->color()" inset="top bottom">{{ $participant->lpk_status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button wire:click="inspect({{ $participant->id }})" size="sm" variant="ghost" icon="eye">{{ __('Periksa') }}</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Inspect Program Modal --}}
    <flux:modal name="inspect-program" @close="closeInspect" class="md:w-2xl">
        @if($this->inspectingParticipant)
            @php $p = $this->inspectingParticipant; @endphp
            <div class="space-y-6">
                {{-- Header --}}
                <div>
                    <flux:heading size="lg">{{ $p->program->title }}</flux:heading>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <flux:badge size="sm" color="zinc">{{ $p->program->type->label() }}</flux:badge>
                        <flux:badge size="sm" :color="$p->status->color()">{{ $p->status->label() }}</flux:badge>
                        @if($p->participant_code)
                            <flux:badge size="sm" color="blue">{{ $p->participant_code }}</flux:badge>
                        @endif
                    </div>
                </div>

                <flux:separator />

                {{-- Detail --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Mahasiswa') }}</flux:text>
                        <flux:text>
                            {{ $p->student?->name ?? '-' }}
                            @if($p->student?->nim)
                                <span class="text-zinc-500">({{ $p->student->nim }})</span>
                            @endif
                        </flux:text>
                    </div>
                    <div>
                        <flux:text variant="strong" class="mb-1">{{ __('Kelompok') }}</flux:text>
                        <flux:text>{{ $p->program->group->name }} ({{ $p->program->group->village }})</flux:text>
                    </div>
                    @if($p->execution_date)
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Tanggal Pelaksanaan') }}</flux:text>
                            <flux:text>{{ $p->execution_date->translatedFormat('d M Y') }}</flux:text>
                        </div>
                    @endif
                    @if($p->problem_potential)
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Potensi / Permasalahan') }}</flux:text>
                            <flux:text>{{ $p->problem_potential }}</flux:text>
                        </div>
                    @endif
                    @if($p->location)
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Lokasi / Narsum') }}</flux:text>
                            <flux:text>{{ $p->location }}</flux:text>
                        </div>
                    @endif
                    @if($p->method)
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Metode Pelaksanaan') }}</flux:text>
                            <flux:text>{{ $p->method }}</flux:text>
                        </div>
                    @endif
                    @if($p->target_audience)
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Kelompok Sasaran') }}</flux:text>
                            <flux:text>{{ $p->target_audience }}</flux:text>
                        </div>
                    @endif
                    @if($p->output_target)
                        <div class="sm:col-span-2">
                            <flux:text variant="strong" class="mb-1">{{ __('Luaran (Output)') }}</flux:text>
                            <flux:text>{{ $p->output_target }}</flux:text>
                        </div>
                    @endif
                    @if($p->sdg_category)
                        <div class="sm:col-span-2">
                            <flux:text variant="strong" class="mb-1">{{ __('Kategori SDGs') }}</flux:text>
                            <flux:badge size="sm" color="lime" inset="top bottom">{{ $p->sdg_category->label() }}</flux:badge>
                        </div>
                    @endif
                </div>

                <flux:separator variant="subtle" />
                
                <div>
                    <flux:heading size="md" class="mb-2">{{ __('Peran Mahasiswa') }}</flux:heading>
                    @if($p->role_in_program)
                        <flux:text variant="strong" class="mb-1">{{ $p->role_in_program }}</flux:text>
                    @endif
                    @if($p->responsibility)
                        <flux:text>{{ $p->responsibility }}</flux:text>
                    @endif
                    
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Status Rencana') }}</flux:text>
                            <flux:badge size="sm" :color="$p->status->color()" class="mt-1">{{ $p->status->label() }}</flux:badge>
                        </div>
                        <div>
                            <flux:text variant="strong" class="mb-1">{{ __('Status Laporan') }}</flux:text>
                            <flux:badge size="sm" :color="$p->lpk_status?->color() ?? 'zinc'" class="mt-1">{{ $p->lpk_status?->label() ?? 'Belum Ada' }}</flux:badge>
                        </div>
                    </div>
                </div>

                @if($p->lpk_status && $p->lpk_status !== \App\Enums\ProgramStatus::Draft)
                    <flux:separator variant="subtle" />
                    <div>
                        <flux:heading size="md" class="mb-2">{{ __('Laporan Pelaksanaan') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-4">
                            @if($p->achievement)
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Hasil yang Dicapai') }}</flux:text>
                                <flux:text>{{ $p->achievement }}</flux:text>
                            </div>
                            @endif
                            @if($p->obstacle)
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Hambatan') }}</flux:text>
                                <flux:text>{{ $p->obstacle }}</flux:text>
                            </div>
                            @endif
                            @if($p->solution)
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Solusi') }}</flux:text>
                                <flux:text>{{ $p->solution }}</flux:text>
                            </div>
                            @endif

                            {{-- Luaran Program --}}
                            @if($p->outputs->isNotEmpty())
                            <div>
                                <flux:text variant="strong" class="mb-2">{{ __('Luaran Program') }}</flux:text>
                                <div class="space-y-2">
                                    @foreach($p->outputs as $output)
                                        <x-file-preview 
                                            :title="$output->name" 
                                            :subtitle="$output->type === 'link' ? __('Tautan (URL)') : __('File dokumen')"
                                        >
                                            <x-slot:icon>
                                                <div class="size-9 rounded-md bg-accent/5 dark:bg-accent/10 flex items-center justify-center">
                                                    <flux:icon icon="{{ $output->type === 'link' ? 'link' : 'document-check' }}" variant="mini" class="text-accent" />
                                                </div>
                                            </x-slot:icon>
                                            <x-slot:action>
                                                <a href="{{ $output->file_path ? asset('storage/' . $output->file_path) : $output->url }}" target="_blank" rel="noopener noreferrer" class="relative items-center font-medium justify-center gap-2 whitespace-nowrap h-8 text-sm rounded-md w-8 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-400 hover:text-accent dark:text-zinc-500 dark:hover:text-accent cursor-pointer" aria-label="{{ __('Buka luaran') }}">
                                                    <flux:icon icon="arrow-top-right-on-square" variant="mini" class="size-4" />
                                                </a>
                                            </x-slot:action>
                                        </x-file-preview>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Foto Dokumentasi --}}
                            @if($p->documentation_image_path)
                            <div class="mt-2">
                                <flux:text variant="strong" class="mb-2">{{ __('Foto Dokumentasi') }}</flux:text>
                                <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                    <img src="{{ asset('storage/' . $p->documentation_image_path) }}" alt="{{ $p->documentation_caption ?? 'Dokumentasi Program' }}" class="w-full h-auto object-cover max-h-96">
                                    @if($p->documentation_caption)
                                    <div class="bg-zinc-50 dark:bg-zinc-800 p-3 border-t border-zinc-200 dark:border-zinc-700">
                                        <flux:text class="text-sm italic text-zinc-600 dark:text-zinc-400 text-center">{{ $p->documentation_caption }}</flux:text>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Catatan revisi sebelumnya --}}
                @if($p->revision_note)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <flux:text class="text-xs font-medium uppercase tracking-wider text-red-600 dark:text-red-400">{{ __('Catatan Revisi') }}</flux:text>
                        <flux:text class="mt-1 text-red-700 dark:text-red-300">{{ $p->revision_note }}</flux:text>
                    </div>
                @endif

                {{-- Inline revision form --}}
                @if($showRevisionForm)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                        <flux:textarea wire:model="revisionNote" label="{{ __('Catatan Revisi') }}" placeholder="{{ __('Jelaskan apa yang perlu diperbaiki...') }}" rows="3" />
                        @error('revisionNote') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                        <div class="mt-3 flex gap-2">
                            <flux:button wire:click="submitRevision" size="sm" variant="primary">{{ __('Kirim Revisi') }}</flux:button>
                            <flux:button wire:click="$set('showRevisionForm', false)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                        </div>
                    </div>
                @endif

                {{-- Footer actions --}}
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                    @if($p->status->value === 'submitted' && !$showRevisionForm)
                        <flux:button wire:click="startRevision" variant="filled" color="amber" icon="arrow-path">{{ __('Revisi Rencana') }}</flux:button>
                        <flux:button wire:click="approve({{ $p->id }})" variant="primary" icon="check">{{ __('Setujui Rencana') }}</flux:button>
                    @elseif($p->status->value === 'approved' && $p->lpk_status->value === 'submitted' && !$showRevisionForm)
                        <flux:button wire:click="startRevision" variant="filled" color="amber" icon="arrow-path">{{ __('Revisi Laporan') }}</flux:button>
                        <flux:button wire:click="approve({{ $p->id }})" variant="primary" icon="check">{{ __('Setujui Laporan') }}</flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>

</div>
