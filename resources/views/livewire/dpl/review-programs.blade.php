<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Review')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="arrow-path" color="red" :label="__('Revisi')" :value="$stats['revision']" />
        <x-stat-card icon="document-text" color="blue" :label="__('Total')" :value="$stats['total']" />
    </div>


    
    {{-- Tema Multidisiplin --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Tema Multidisiplin') }}</flux:heading>
    </div>
    
    {{-- Filter Tema --}}
    <div class="flex justify-end">
        @if($allGroups->count() > 1)
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        @endif
    </div>

    {{-- Tema Multidisiplin Management --}}
    <flux:card>
        <div class="flex items-center justify-between mb-2">
            @if($multidisiplinThemes->isNotEmpty())
            <flux:badge color="purple" size="sm">{{ $multidisiplinThemes->count() }} {{ __('tema') }}</flux:badge>
            @endif
        </div>
        
        @if(!$canManageThemes)
        {{-- Prompt to select a group first --}}
            <div class="py-6 text-center">
                <x-empty-state icon="arrow-up-circle" :heading="__('Belum Ada Kelompok Terpilih')" :description="__('Pilih kelompok terlebih dahulu untuk mengelola tema multidisiplin.')" />
            </div>
        @else
            @if($multidisiplinThemes->isEmpty())
                <x-empty-state
                    icon="puzzle-piece"
                    :heading="__('Belum Ada Tema')"
                    :description="__('Tambahkan tema multidisiplin agar mahasiswa dapat memilih dan bergabung.')"
                />
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('No.') }}</flux:table.column>
                        <flux:table.column>{{ __('Tema') }}</flux:table.column>
                        <flux:table.column>{{ __('Peserta') }}</flux:table.column>
                        <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($multidisiplinThemes as $theme)
                            <flux:table.row :key="$theme->id">
                                <flux:table.cell>
                                    <flux:badge color="purple" size="sm">{{ $theme->sequence }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($editingThemeId === $theme->id)
                                        <form id="edit-theme-{{ $theme->id }}" wire:submit="saveEditTheme" class="min-w-64">
                                            <flux:input wire:model="editingThemeTitle" size="sm" autofocus />
                                        </form>
                                    @else
                                        <flux:text variant="strong">{{ $theme->title }}</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($theme->participants_count > 0)
                                        <flux:badge size="sm" color="green">{{ $theme->participants_count }} {{ __('mahasiswa') }}</flux:badge>
                                    @else
                                        <flux:text class="text-zinc-400">-</flux:text>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($editingThemeId === $theme->id)
                                        <div class="flex items-center gap-2">
                                            <flux:button type="submit" form="edit-theme-{{ $theme->id }}" size="sm" variant="filled" icon="check">{{ __('Simpan') }}</flux:button>
                                            <flux:button wire:click="cancelEditTheme" size="sm" variant="ghost" icon="x-mark">{{ __('Batal') }}</flux:button>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2">
                                            <flux:button wire:click="startEditTheme({{ $theme->id }})" size="sm" variant="ghost" icon="pencil-square">{{ __('Edit') }}</flux:button>
                                            <flux:button wire:click="confirmDeleteTheme({{ $theme->id }})"
                                                size="sm"
                                                variant="danger"
                                                icon="trash"
                                                class="{{ $theme->participants_count > 0 ? 'cursor-pointer opacity-75' : '' }}"
                                                aria-disabled="{{ $theme->participants_count > 0 ? 'true' : 'false' }}">{{ __('Hapus') }}
                                            </flux:button>
                                        </div>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
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

    {{-- Delete Modal Confirmation --}}
    <flux:modal name="delete-theme" class="md:w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Hapus Tema Multidisiplin') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Apakah Anda yakin ingin menghapus tema ":title"?', ['title' => $deletingThemeTitle]) }}</flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button wire:click="cancelDeleteTheme" variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button wire:click="confirmDelete" variant="danger">{{ __('Ya, Hapus') }}</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Programs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full max-w-sm">
            <x-search-bar placeholder="{{ __('Cari mahasiswa, NIM, atau judul program...') }}" clearable class="w-full sm:w-80" />
        </div>
        <div class="flex flex-wrap gap-4">

            <flux:select wire:model.live="filterType" size="sm" class="w-full sm:w-48">
                <option value="">{{ __('Semua Jenis') }}</option>
                @foreach(\App\Enums\ProgramType::cases() as $type)
                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="filterStatus" size="sm" class="w-full sm:w-48">
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
            <flux:table :paginate="$participants">
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa & Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Status Rencana') }}</flux:table.column>
                    <flux:table.column>{{ __('Status Pelaksanaan') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($participants as $participant)
                        <flux:table.row :key="$participant->id">
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $participant->student?->name ?? __('Kelompok') }}</flux:text>
                                <flux:text variant="subtle" class="text-xs">{{ $participant->program->group->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $participant->program->title }}</flux:text>
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
    <flux:modal name="inspect-program" scroll="body" @close="closeInspect" class="max-w-2xl w-full">
        @if($this->inspectingParticipant)
            <div class="flex flex-col gap-6">
                <div class="flex items-start gap-3">
                    <flux:avatar
                        :name="$this->inspectingParticipant->student?->name ?? __('Mahasiswa')"
                        :initials="$this->inspectingParticipant->student?->initials() ?? '?'"
                        size="sm"
                        class="mt-0.5"
                    />
                    <div class="flex min-w-0 flex-col gap-1.5">
                        <flux:heading size="lg">{{ $this->inspectingParticipant->student?->name ?? __('Mahasiswa') }}</flux:heading>
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:text variant="subtle" class="text-sm">
                            {{ $this->inspectingParticipant->student?->nim ?? __('Belum diisi') }}
                            </flux:text>
                            <flux:separator vertical variant="subtle" class="h-4" />
                            <flux:text variant="subtle" class="text-sm">{{ $this->inspectingParticipant->program->group->name }}</flux:text>
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="space-y-1.5">
                                <flux:heading size="sm">{{ __('Status Rencana') }}</flux:heading>
                                <flux:badge size="sm" :color="$this->inspectingParticipant->status->color()">
                                    {{ $this->inspectingParticipant->status->label() }}
                                </flux:badge>
                            </div>
                            <div class="space-y-1.5">
                                <flux:heading size="sm">{{ __('Status Pelaksanaan') }}</flux:heading>
                                <flux:badge size="sm" :color="$this->inspectingParticipant->lpk_status?->color() ?? 'zinc'">
                                    {{ $this->inspectingParticipant->lpk_status?->label() ?? __('Belum Ada') }}
                                </flux:badge>
                            </div>
                        </div>
                    </div>
                </div>

                <flux:separator />

                <div class="space-y-6">
                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Identitas Program') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Program') }}</flux:text>
                                <flux:text>{{ $this->inspectingParticipant->program->title ?: __('(Belum ada judul)') }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Jenis Program') }}</flux:text>
                                <flux:text>{{ $this->inspectingParticipant->program->type->label() }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Kode Program') }}</flux:text>
                                <flux:text>{{ $this->inspectingParticipant->participant_code ?: __('Belum diisi') }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Tanggal Pelaksanaan') }}</flux:text>
                                <flux:text>{{ $this->inspectingParticipant->execution_date?->translatedFormat('d M Y') ?? __('Belum diisi') }}</flux:text>
                            </div>
                            <div>
                                <flux:text variant="strong" class="mb-1">{{ __('Kelompok') }}</flux:text>
                                <flux:text>{{ $this->inspectingParticipant->program->group->name }} ({{ $this->inspectingParticipant->program->group->village ?: __('Belum diisi') }})</flux:text>
                            </div>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Rancangan Program') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            @if($this->inspectingParticipant->program->type === \App\Enums\ProgramType::Multidisiplin)
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ __('Usulan Program (Spesifik)') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->participant_title ?: __('Belum diisi') }}</flux:text>
                                </div>
                            @endif
                            @if($this->inspectingParticipant->program->type === \App\Enums\ProgramType::Multidisiplin && !$this->inspectingParticipant->program->isVideoProfile())
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Potensi / Permasalahan') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->problem_potential ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Lokasi / Narsum') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->location ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Metode Pelaksanaan') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->method ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Kelompok Sasaran') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->target_audience ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ __('Luaran (Output)') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->output_target ?: __('Belum diisi') }}</flux:text>
                                </div>
                            @endif
                            @if($this->inspectingParticipant->program->type !== \App\Enums\ProgramType::Multidisiplin)
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Peran Anda') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->role_in_program ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Deskripsi Tugas dan Tanggung Jawab') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->responsibility ?: __('Belum diisi') }}</flux:text>
                                </div>
                            @endif
                            <div class="sm:col-span-2">
                                <flux:text variant="strong" class="mb-1">{{ __('Kategori SDGs') }}</flux:text>
                                @if($this->inspectingParticipant->sdg_category)
                                    <flux:badge size="sm" color="lime" inset="top bottom">{{ $this->inspectingParticipant->sdg_category->label() }}</flux:badge>
                                @else
                                    <flux:text>{{ __('Belum diisi') }}</flux:text>
                                @endif
                            </div>
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Pelaksanaan Program') }}</flux:heading>
                        <div class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
                            @if($this->inspectingParticipant->program->type === \App\Enums\ProgramType::Multidisiplin && !$this->inspectingParticipant->program->isVideoProfile())
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ __('Pelaksanaan Kegiatan') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->execution_description ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ __('Ketercapaian') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->achievement ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Hambatan') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->obstacle ?: __('Belum diisi') }}</flux:text>
                                </div>
                                <div>
                                    <flux:text variant="strong" class="mb-1">{{ __('Tindak Lanjut') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->solution ?: __('Belum diisi') }}</flux:text>
                                </div>
                            @else
                                <div class="sm:col-span-2">
                                    <flux:text variant="strong" class="mb-1">{{ __('Hasil') }}</flux:text>
                                    <flux:text>{{ $this->inspectingParticipant->achievement ?: __('Belum diisi') }}</flux:text>
                                </div>
                            @endif
                        </div>
                    </section>

                    <flux:separator variant="subtle" />

                    <section class="space-y-3">
                        <flux:heading size="sm">{{ __('Luaran Program') }}</flux:heading>
                        @if($this->inspectingParticipant->outputs->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($this->inspectingParticipant->outputs as $output)
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
                        @else
                            <flux:text>{{ __('Belum diisi') }}</flux:text>
                        @endif

                        @if($this->inspectingParticipant->documentation_image_path)
                            <flux:heading size="sm">{{ __('Foto Dokumentasi') }}</flux:heading>
                            <div>
                                <flux:card variant="soft" class="overflow-hidden p-0">
                                <img
                                    src="{{ $this->inspectingParticipant->documentationImageUrl() }}"
                                    alt="{{ $this->inspectingParticipant->documentation_caption ?? 'Dokumentasi Program' }}"
                                    class="mx-auto block max-h-96 max-w-full object-contain"
                                    x-on:click="$flux.modal('image-preview-view').show()"
                                    />
                                    
                                <x-image-preview-modal name="image-preview-view" url="{{ $this->inspectingParticipant->documentationImageUrl() }}" />
                                <flux:separator variant="subtle" />
                                <div class="p-3">
                                    <flux:text variant="subtle" class="text-center text-sm italic">{{ $this->inspectingParticipant->documentation_caption ?: __('Belum diisi') }}</flux:text>
                                </div>
                                </flux:card>
                            </div>
                        @else
                            <flux:text>{{ __('Belum diisi') }}</flux:text>
                        @endif
                    </section>

                    @if($this->inspectingParticipant->revision_note)
                        <flux:separator variant="subtle" />
                        <section class="space-y-2">
                            <flux:heading size="sm">{{ __('Catatan Revisi') }}</flux:heading>
                            <flux:text>{{ $this->inspectingParticipant->revision_note }}</flux:text>
                        </section>
                    @endif

                    @if($showRevisionForm)
                        <flux:separator variant="subtle" />
                        <section class="space-y-3">
                            <flux:textarea wire:model="revisionNote" label="{{ __('Catatan Revisi') }}" placeholder="{{ __('Jelaskan apa yang perlu diperbaiki...') }}" rows="3" />
                            @error('revisionNote')
                                <flux:text class="text-xs text-red-500">{{ $message }}</flux:text>
                            @enderror
                            <div class="flex justify-end gap-2">
                                <flux:button wire:click="$set('showRevisionForm', false)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                                <flux:button wire:click="submitRevision" size="sm" variant="primary">{{ __('Kirim Revisi') }}</flux:button>
                            </div>
                        </section>
                    @endif
                </div>

                <div class="flex justify-end">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Tutup') }}</flux:button>
                    </flux:modal.close>
                    @if($this->inspectingParticipant->status->value === 'submitted' && !$showRevisionForm)
                        <flux:button wire:click="startRevision" variant="filled" color="amber" icon="arrow-path">{{ __('Revisi Rencana') }}</flux:button>
                        <flux:button wire:click="approve({{ $this->inspectingParticipant->id }})" variant="primary" icon="check">{{ __('Setujui Rencana') }}</flux:button>
                    @elseif($this->inspectingParticipant->status->value === 'approved' && $this->inspectingParticipant->lpk_status?->value === 'submitted' && !$showRevisionForm)
                        <flux:button wire:click="startRevision" variant="filled" color="amber" icon="arrow-path">{{ __('Revisi Laporan') }}</flux:button>
                        <flux:button wire:click="approve({{ $this->inspectingParticipant->id }})" variant="primary" icon="check">{{ __('Setujui Laporan') }}</flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>

</div>
