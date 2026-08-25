@props([
    'outputs' => [],
    'heading' => __('Luaran Program'),
    'description' => __('Tambahkan luaran berupa file (misal: modul, katalog) atau tautan/link (misal: video youtube, website).'),
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input 
                            wire:model="outputs.{{ $index }}.name" 
                            label="{{ __('Judul/Nama Luaran') }}" 
                            placeholder="{{ __('Contoh: Buku Saku, Katalog, Video, dll.') }}" 
                        />
                        <flux:select wire:model.live="outputs.{{ $index }}.type" label="{{ __('Jenis Luaran') }}">
                            <flux:select.option value="file">{{ __('Upload File') }}</flux:select.option>
                            <flux:select.option value="link">{{ __('Tautan (Link/URL)') }}</flux:select.option>
                        </flux:select>
                    </div>

                    @if($outputs[$index]['type'] === 'file')
                        {{-- File Upload Dropzone --}}
                        <div>
                            <flux:label class="mb-3">{{ __('Upload File Luaran') }}</flux:label>

                            <div x-data="{ dragging: false, uploading: false }"
                                 x-on:livewire-upload-start="uploading = true"
                                 x-on:livewire-upload-finish="uploading = false"
                                 x-on:livewire-upload-error="uploading = false"
                                 x-on:dragover.prevent="dragging = true"
                                 x-on:dragleave.prevent="dragging = false"
                                 x-on:drop.prevent="dragging = false; if($event.dataTransfer.files.length) { $refs.fileInput{{ $index }}.files = $event.dataTransfer.files; $refs.fileInput{{ $index }}.dispatchEvent(new Event('change', { 'bubbles': true })); }"
                                 class="group w-full py-4 px-6 flex items-center gap-4 rounded-lg border-dashed border-2 transition-colors cursor-pointer"
                                 :class="{ 'bg-zinc-100 border-zinc-300 dark:bg-white/15 dark:border-white/20': dragging, 'border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/10': !dragging }"
                                 @click="$refs.fileInput{{ $index }}.click()">

                                <input x-ref="fileInput{{ $index }}" type="file" wire:model="outputs.{{ $index }}.file" class="sr-only" />

                                <div class="relative shrink-0">
                                    <flux:icon.cloud-arrow-up x-show="!uploading" variant="solid" class="shrink-0 size-6 text-zinc-400 dark:text-white/60 transition group-hover:text-zinc-800 dark:group-hover:text-white" />
                                    <flux:icon.arrow-path x-show="uploading" variant="solid" x-cloak class="shrink-0 size-6 animate-spin absolute inset-0 text-zinc-800 dark:text-white transition" />
                                </div>

                                <div class="flex flex-col gap-0.5">
                                    <div class="text-sm font-medium text-zinc-800 dark:text-white group-hover:underline">
                                        {{ __('Seret file ke sini atau klik untuk memilih') }}
                                    </div>
                                    <div class="text-zinc-500 dark:text-white/60 text-xs">
                                        {{ __('PDF, DOC, PPT, XLS, ZIP, dll. Maksimal 10MB.') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Uploaded File Preview --}}
                        @if(!empty($outputs[$index]['file']) && is_object($outputs[$index]['file']))
                            <div class="overflow-hidden flex items-center shadow-xs bg-white dark:bg-white/10 min-h-10 text-base sm:text-sm rounded-lg w-full border border-zinc-200 border-b-zinc-300/80 dark:border-white/10">
                                <div class="p-2.5 shrink-0">
                                    <div class="size-9 rounded-md bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                        <flux:icon.document variant="mini" class="text-blue-500 dark:text-blue-400" />
                                    </div>
                                </div>
                                <div class="flex-1 overflow-hidden py-2 me-3 flex flex-col justify-center gap-0.5">
                                    <div class="text-sm font-medium text-zinc-700 dark:text-white/80 whitespace-nowrap overflow-hidden text-ellipsis">{{ $outputs[$index]['file']->getClientOriginalName() }}</div>
                                    <div class="text-xs text-zinc-500">{{ __('File baru') }} · {{ round($outputs[$index]['file']->getSize() / 1024) }} KB</div>
                                </div>
                                <div class="p-1.5 shrink-0">
                                    <button type="button" wire:click="$set('outputs.{{ $index }}.file', null)" class="relative items-center font-medium justify-center gap-2 whitespace-nowrap h-8 text-sm rounded-md w-8 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-400 hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400 cursor-pointer" aria-label="{{ __('Hapus file') }}">
                                        <flux:icon.x-mark variant="mini" class="size-4" />
                                    </button>
                                </div>
                            </div>
                        @elseif(!empty($outputs[$index]['file_path']))
                            <div class="overflow-hidden flex items-center shadow-xs bg-white dark:bg-white/10 min-h-10 text-base sm:text-sm rounded-lg w-full border border-zinc-200 border-b-zinc-300/80 dark:border-white/10">
                                <div class="p-2.5 shrink-0">
                                    <div class="size-9 rounded-md bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                                        <flux:icon.document-check variant="mini" class="text-emerald-500 dark:text-emerald-400" />
                                    </div>
                                </div>
                                <div class="flex-1 overflow-hidden py-2 me-3 flex flex-col justify-center gap-0.5">
                                    <div class="text-sm font-medium text-zinc-700 dark:text-white/80 whitespace-nowrap overflow-hidden text-ellipsis">{{ basename($outputs[$index]['file_path']) }}</div>
                                    <div class="text-xs text-zinc-500">{{ __('File tersimpan') }}</div>
                                </div>
                                <div class="p-1.5 shrink-0">
                                    <a href="{{ Storage::url($outputs[$index]['file_path']) }}" target="_blank" class="relative items-center font-medium justify-center gap-2 whitespace-nowrap h-8 text-sm rounded-md w-8 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-400 hover:text-blue-500 dark:text-zinc-500 dark:hover:text-blue-400 cursor-pointer" aria-label="{{ __('Lihat file') }}">
                                        <flux:icon.arrow-top-right-on-square variant="mini" class="size-4" />
                                    </a>
                                </div>
                            </div>
                        @endif

                        <flux:error name="outputs.{{ $index }}.file" />
                    @else
                        {{-- Link Input --}}
                        <flux:field>
                            <flux:label>{{ __('Tautan Luaran (URL)') }}</flux:label>
                            <flux:input.group>
                                <flux:input.group.prefix>https://</flux:input.group.prefix>
                                <flux:input wire:model="outputs.{{ $index }}.url" placeholder="{{ __('example.com') }}" />
                            </flux:input.group>
                            <flux:error name="outputs.{{ $index }}.url" />
                        </flux:field>
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
