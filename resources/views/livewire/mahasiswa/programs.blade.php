<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Status Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <x-stat-card icon="pencil-square" color="neutral" :label="__('Draft')" :value="$stats['draft']" />
        <x-stat-card icon="clock" color="amber" :label="__('Diajukan')" :value="$stats['submitted']" />
        <x-stat-card icon="arrow-path" color="red" :label="__('Revisi')" :value="$stats['needs_revision']" />
        <x-stat-card icon="check-circle" color="green" :label="__('Disetujui')" :value="$stats['approved']" />
    </div>

    {{-- Programs List --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <flux:heading size="lg">{{ __('Daftar Program Kerja') }}</flux:heading>
            <flux:select wire:model.live="filterType" size="sm" placeholder="{{ __('Semua Jenis') }}" class="w-48">
                <flux:select.option value="">{{ __('Semua Jenis') }}</flux:select.option>
                @foreach($programTypes as $type)
                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <flux:separator />

        @if($programs->isEmpty())
            <x-empty-state icon="light-bulb" :heading="__('Belum Ada Program Kerja')" :description="__('Mulai dengan mengajukan program kerja KKN Anda.')" />
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Program Kerja') }}</flux:table.column>
                    <flux:table.column>{{ __('Jenis') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Terakhir Diperbarui') }}</flux:table.column>
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
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
