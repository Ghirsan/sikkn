<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="clock" color="amber" :label="__('Menunggu Review')" :value="$stats['pending']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
        <x-stat-card icon="arrow-path" color="red" :label="__('Revisi')" :value="$stats['revision']" />
        <x-stat-card icon="document-text" color="blue" :label="__('Total')" :value="$stats['total']" />
    </div>

    {{-- Filter --}}
    <flux:select wire:model.live="filterStatus" size="sm" placeholder="{{ __('Semua Status') }}" class="w-48">
        <flux:select.option value="">{{ __('Semua Status') }}</flux:select.option>
        <flux:select.option value="submitted">{{ __('Menunggu Review') }}</flux:select.option>
        <flux:select.option value="approved">{{ __('Disetujui') }}</flux:select.option>
        <flux:select.option value="needs_revision">{{ __('Revisi') }}</flux:select.option>
        <flux:select.option value="draft">{{ __('Draft') }}</flux:select.option>
    </flux:select>

    {{-- Programs --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Program Kerja Mahasiswa') }}</flux:heading>
    </div>

    <flux:card>

        @if($programs->isEmpty())
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
                    @foreach($programs as $program)
                        <flux:table.row :key="$program->id">
                            <flux:table.cell>
                                <span class="font-medium">{{ $program->student->name }}</span>
                                <div class="text-xs text-neutral-500">{{ $program->group->name }}</div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="font-medium">{{ $program->title }}</div>
                                <flux:badge size="sm" color="zinc" class="mt-1">{{ $program->type->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$program->status->color()" inset="top bottom">{{ $program->status->label() }}</flux:badge>
                                @if($program->revision_note)
                                    <div class="mt-2 text-xs text-red-600 dark:text-red-400">
                                        <strong>{{ __('Catatan:') }}</strong> {{ $program->revision_note }}
                                    </div>
                                @endif

                                @if($revisingProgramId === $program->id)
                                    <flux:card class="mt-3 border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20">
                                        <flux:textarea wire:model="revisionNote" label="{{ __('Catatan Revisi') }}" placeholder="{{ __('Jelaskan apa yang perlu diperbaiki...') }}" rows="3" />
                                        @error('revisionNote') <flux:text class="mt-1 text-xs text-red-500">{{ $message }}</flux:text> @enderror
                                        <div class="mt-3 flex gap-2">
                                            <flux:button wire:click="submitRevision" size="sm" variant="filled">{{ __('Kirim') }}</flux:button>
                                            <flux:button wire:click="$set('revisingProgramId', 0)" size="sm" variant="ghost">{{ __('Batal') }}</flux:button>
                                        </div>
                                    </flux:card>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($program->status->value === 'submitted')
                                    <div class="flex flex-col gap-2">
                                        <flux:button wire:click="approve({{ $program->id }})" size="sm" variant="filled" color="green" icon="check" inset="top bottom">{{ __('Setujui') }}</flux:button>
                                        <flux:button wire:click="startRevision({{ $program->id }})" size="sm" variant="filled" color="amber" icon="arrow-path" inset="top bottom">{{ __('Revisi') }}</flux:button>
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
