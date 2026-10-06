@props([
    'outputs' => [],
    'heading' => __('Luaran Program'),
    'description' => __('Tambahkan luaran berupa tautan PDF, video, gambar, atau jenis lainnya.'),
    'addLabel' => __('Tambah Luaran'),
    'addAction' => 'addOutput',
    'removeAction' => 'removeOutput',
])

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
        <div>
            <flux:heading size="lg" class="mb-1">{{ $heading }}</flux:heading>
            <flux:text class="text-sm text-zinc-500">{{ $description }}</flux:text>
        </div>
        <div class="flex justify-end items-center shrink-0">
            <flux:button wire:click="{{ $addAction }}" size="sm" icon="plus" variant="filled">{{ $addLabel }}</flux:button>
        </div>
    </div>

    {{-- Output Items --}}
    <div class="flex flex-col gap-4">
        @forelse($outputs as $index => $output)
            <div class="rounded-xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs overflow-hidden">
                {{-- Item Header --}}
                <div class="flex items-center justify-between gap-3 px-4 py-3 bg-zinc-50 dark:bg-white/5 border-b border-zinc-200 dark:border-white/10">
                    <div class="flex items-center gap-2 min-w-0">
                        <flux:icon.document-text variant="mini" class="shrink-0 text-zinc-400 dark:text-zinc-500" />
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300 truncate">
                            {{ __('Luaran') }} #{{ $index + 1 }}
                        </flux:text>
                        @if(!empty($output['name']))
                            <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 truncate hidden sm:block">— {{ $output['name'] }}</flux:text>
                        @endif
                    </div>
                    <flux:button wire:click="{{ $removeAction }}({{ $index }})" variant="ghost" size="sm" icon="x-mark" class="shrink-0 text-zinc-400 hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400" />
                </div>

                {{-- Item Body --}}
                <div class="p-4 flex flex-col gap-4">
                    <flux:field>
                        <flux:label>{{ __('Tautan Luaran (URL)') }}</flux:label>
                        <div x-data x-on:input.debounce.800ms="$wire.call('inferOutputType', {{ $index }})">
                            <flux:input wire:model="outputs.{{ $index }}.url" placeholder="{{ __('https://contoh.com/luaran') }}" />
                        </div>
                        <flux:error name="outputs.{{ $index }}.url" />
                    </flux:field>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input
                            wire:model="outputs.{{ $index }}.name"
                            label="{{ __('Judul/Nama Luaran') }}"
                            placeholder="{{ __('Contoh: Buku Saku, Katalog, Video, dll.') }}"
                        />
                        <flux:select wire:model.live="outputs.{{ $index }}.type" label="{{ __('Jenis Luaran') }}">
                            <flux:select.option value="pdf">{{ __('PDF') }}</flux:select.option>
                            <flux:select.option value="video">{{ __('Video') }}</flux:select.option>
                            <flux:select.option value="image">{{ __('Gambar') }}</flux:select.option>
                            <flux:select.option value="lainnya">{{ __('Lainnya') }}</flux:select.option>
                        </flux:select>
                    </div>

                    @if(!empty($output['url']) && ($output['url_valid'] ?? false))
                        <x-program-output-metadata-card
                            :name="$output['name']"
                            :type="$output['type']"
                            :url="$output['url']"
                            :metadata="$output['metadata'] ?? []"
                        />
                    @endif
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="rounded-xl border border-dashed border-zinc-300 dark:border-white/10 bg-zinc-50/50 dark:bg-white/[0.02] p-8 flex flex-col items-center gap-3 text-center">
                <div class="size-10 rounded-full bg-zinc-100 dark:bg-white/10 flex items-center justify-center">
                    <flux:icon.archive-box variant="outline" class="size-5 text-zinc-400 dark:text-zinc-500" />
                </div>
                <div>
                    <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Belum Ada Luaran') }}</flux:text>
                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">{{ __('Tekan tombol "Tambah Luaran" untuk menambahkan.') }}</flux:text>
                </div>
            </div>
        @endforelse
    </div>

    <flux:error name="outputs" class="mt-2" />
</div>
