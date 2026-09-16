<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Review')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="arrow-path" color="red" :label="__('Revisi')" :value="$stats['revision']" />
        <x-stat-card icon="document-text" color="blue" :label="__('Total')" :value="$stats['total']" />
    </div>

    {{-- Filters --}}
    <div class="flex gap-4">
        @if($allGroups->count() > 1)
            <flux:select wire:model.live="selectedGroupId" size="sm" class="w-64">
                <option value="">{{ __('Semua Kelompok') }}</option>
                @foreach($allGroups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->village }})</option>
                @endforeach
            </flux:select>
        @endif

        <flux:select wire:model.live="filterStatus" size="sm" class="w-48">
            <option value="">{{ __('Semua Status') }}</option>
            <option value="submitted">{{ __('Menunggu Review') }}</option>
            <option value="approved">{{ __('Disetujui') }}</option>
            <option value="needs_revision">{{ __('Revisi') }}</option>
            <option value="draft">{{ __('Draft') }}</option>
        </flux:select>
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

    <flux:card>

        @if($participants->isEmpty())
            <x-empty-state icon="light-bulb" :heading="__('Tidak Ada Program')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Mahasiswa & Kelompok') }}</flux:table.column>
                    <flux:table.column>{{ __('Program') }}</flux:table.column>
                    <flux:table.column>{{ __('Status & Catatan') }}</flux:table.column>
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
                                @if($participant->revision_note)
                                    <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                                        <strong>{{ __('Catatan:') }}</strong> {{ $participant->revision_note }}
                                    </div>
                                @endif

                                @if($revisingParticipantId === $participant->id)
                                    <flux:card class="mt-3 border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
                                        <flux:textarea wire:model="revisionNote" label="{{ __('Catatan Revisi') }}" placeholder="{{ __('Jelaskan apa yang perlu diperbaiki...') }}" rows="3" />
                                        @error('revisionNote') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                                        <div class="mt-3 flex gap-2">
                                            <flux:button wire:click="submitRevision" size="sm" variant="filled">{{ __('Kirim') }}</flux:button>
                                            <flux:button wire:click="$set('revisingParticipantId', 0)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                                        </div>
                                    </flux:card>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($participant->status->value === 'submitted')
                                    <div class="flex flex-col gap-2">
                                        <flux:button wire:click="approve({{ $participant->id }})" size="sm" variant="filled" color="green" icon="check" inset="top bottom">{{ __('Setujui') }}</flux:button>
                                        <flux:button wire:click="startRevision({{ $participant->id }})" size="sm" variant="filled" color="amber" icon="arrow-path" inset="top bottom">{{ __('Revisi') }}</flux:button>
                                    </div>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
