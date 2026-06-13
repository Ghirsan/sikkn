<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Status Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="pencil-square" color="neutral" :label="__('Draft')" :value="$stats['draft']" />
        <x-stat-card icon="clock" color="amber" :label="__('Diajukan')" :value="$stats['submitted']" />
        <x-stat-card icon="arrow-path" color="red" :label="__('Revisi')" :value="$stats['needs_revision']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
    </div>

    {{-- Programs List --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">{{ __('Daftar Program Kerja') }}</flux:heading>
        <div class="flex items-center gap-3">
            <flux:select wire:model.live="filterType" size="sm" placeholder="{{ __('Semua Jenis') }}" class="w-48">
                <flux:select.option value="">{{ __('Semua Jenis') }}</flux:select.option>
                @foreach($programTypes as $type)
                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:button wire:click="create" x-data x-on:click="$flux.modal('program-modal').show()" variant="filled" size="sm" icon="plus">
                {{ __('Ajukan Program') }}
            </flux:button>
        </div>
    </div>

    <flux:card>
        @if($programs->isEmpty())
            <x-empty-state icon="light-bulb" :heading="__('Belum Ada Program Kerja')" :description="__('Mulai dengan mengajukan program kerja KKN Anda.')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Program Kerja') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Terakhir Diperbarui') }}</flux:table.column>
                    <flux:table.column>{{ __('Aksi') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($programs as $program)
                        <flux:table.row :key="$program->id">
                            <flux:table.cell>
                                <span class="font-medium text-neutral-900 dark:text-white">{{ $program->title }}</span>
                                @if($program->revision_note)
                                    <flux:callout variant="danger" icon="arrow-path" class="mt-2" :heading="__('Revisi: ') . $program->revision_note" />
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="zinc" inset="top bottom">{{ $program->type->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="$program->status->color()" inset="top bottom">{{ $program->status->label() }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-neutral-500">
                                {{ $program->updated_at->diffForHumans() }}
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($program->status->value === 'draft' || $program->status->value === 'needs_revision')
                                    <flux:dropdown>
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                                        <flux:menu>
                                            <flux:menu.item wire:click="edit({{ $program->id }})" x-on:click="$flux.modal('program-modal').show()" icon="pencil">{{ __('Edit') }}</flux:menu.item>
                                            @if($program->status->value === 'draft')
                                                <flux:menu.item wire:click="submitProgram({{ $program->id }})" icon="paper-airplane" class="text-green-600">{{ __('Ajukan') }}</flux:menu.item>
                                                <flux:menu.item wire:click="delete({{ $program->id }})" icon="trash" variant="danger" wire:confirm="{{ __('Yakin ingin menghapus program ini?') }}">{{ __('Hapus') }}</flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>

    {{-- Modal Form --}}
    <flux:modal name="program-modal" class="md:w-3/4 lg:w-1/2">
        <form wire:submit="save" class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $programId ? __('Edit Program') : __('Usulkan Program Baru') }}</flux:heading>
            
            <flux:input wire:model="title" label="{{ __('Judul Program') }}" placeholder="{{ __('Contoh: Penyuluhan Kesehatan Masyarakat') }}" />
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:select wire:model="type" label="{{ __('Jenis Program') }}">
                    @foreach(\App\Enums\ProgramType::cases() as $type)
                        <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="theme" label="{{ __('Tema (Opsional)') }}" placeholder="{{ __('Contoh: Kesehatan Lingkungan') }}" />
            </div>

            <flux:textarea wire:model="problem_potential" label="{{ __('Potensi / Masalah') }}" placeholder="{{ __('Deskripsikan potensi desa atau masalah yang ingin diselesaikan...') }}" rows="3" />
            <flux:textarea wire:model="target" label="{{ __('Target Keluaran') }}" placeholder="{{ __('Apa hasil yang diharapkan dari program ini?') }}" rows="3" />

            <div class="mt-2 flex justify-end gap-2">
                <flux:button type="button" variant="ghost" x-on:click="$flux.modal('program-modal').close()">{{ __('Batal') }}</flux:button>
                <flux:button type="submit" variant="filled">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
