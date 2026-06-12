<div class="flex h-full w-full flex-1 flex-col gap-6">
    {{-- Status Stats --}}
    <div class="grid auto-rows-min gap-4 md:grid-cols-4">
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-neutral-100 dark:bg-zinc-700">
                    <flux:icon name="pencil-square" class="size-5 text-neutral-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Draft') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['draft'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/30">
                    <flux:icon name="clock" class="size-5 text-amber-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Diajukan') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['submitted'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30">
                    <flux:icon name="arrow-path" class="size-5 text-red-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Revisi') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['needs_revision'] }}</flux:text>
                </div>
            </div>
        </flux:card>
        <flux:card>
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                    <flux:icon name="check-circle" class="size-5 text-green-500" />
                </div>
                <div>
                    <flux:text class="text-sm text-neutral-500">{{ __('Disetujui') }}</flux:text>
                    <flux:text class="text-2xl font-bold">{{ $stats['approved'] }}</flux:text>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Programs List --}}
    <flux:card class="!p-0">
        <div class="border-b border-neutral-200 px-6 py-4 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Daftar Program Kerja') }}</flux:heading>
                <flux:select wire:model.live="filterType" size="sm" placeholder="{{ __('Semua Jenis') }}" class="w-48">
                    <flux:select.option value="">{{ __('Semua Jenis') }}</flux:select.option>
                    @foreach($programTypes as $type)
                        <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if($programs->isEmpty())
            <div class="px-6 py-12 text-center">
                <flux:icon name="light-bulb" class="mx-auto size-12 text-neutral-300 dark:text-neutral-600" />
                <flux:heading size="lg" class="mt-4">{{ __('Belum Ada Program Kerja') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-neutral-500">
                    {{ __('Mulai dengan mengajukan program kerja KKN Anda.') }}
                </flux:text>
            </div>
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
                                    <div class="mt-2 rounded-lg border border-red-200 bg-red-50 p-2 text-xs text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
                                        <strong>{{ __('Revisi:') }}</strong> {{ $program->revision_note }}
                                    </div>
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
