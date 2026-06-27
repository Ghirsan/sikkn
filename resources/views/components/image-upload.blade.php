@props([
    'modelName', // The name of the Livewire property, e.g. 'location_map_image'
    'file' => null, // The actual Livewire property object
    'existingPath' => null, // The path to the existing file in storage
    'label' => 'Unggah Gambar',
    'description' => 'Klik untuk memilih atau seret gambar ke sini',
    'formatText' => 'Format: JPG, PNG. Maksimal 2MB.',
    'modalName' => 'image-preview',
])

<div>
    @if($label)
        <flux:label class="mb-3">{{ $label }}</flux:label>
    @endif

    <div x-data="{ dragging: false, uploading: false }" 
         x-on:livewire-upload-start="uploading = true"
         x-on:livewire-upload-finish="uploading = false"
         x-on:livewire-upload-error="uploading = false"
         x-on:dragover.prevent="dragging = true" 
         x-on:dragleave.prevent="dragging = false"
         x-on:drop.prevent="dragging = false; if($event.dataTransfer.files.length) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { 'bubbles': true })); }"
         class="group w-full py-5 px-6 sm:py-10 sm:px-16 flex flex-col items-center justify-center rounded-lg border-dashed border-2 transition-colors cursor-pointer"
         :class="{ 'bg-zinc-100 border-zinc-300 dark:bg-white/15 dark:border-white/20': dragging, 'border-zinc-200 dark:border-white/10 bg-zinc-50 dark:bg-white/10': !dragging }"
         @click="$refs.fileInput.click()">
        
        <input x-ref="fileInput" type="file" wire:model="{{ $modelName }}" accept="image/jpeg, image/png, image/gif" class="sr-only" />

        <div class="relative mb-4">
            <svg x-show="!uploading" class="shrink-0 size-6 text-zinc-400 dark:text-white/60 transition group-hover:text-zinc-800 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M10.5 3.75a6 6 0 0 0-5.98 6.496A5.25 5.25 0 0 0 6.75 20.25H18a4.5 4.5 0 0 0 2.206-8.423 3.75 3.75 0 0 0-4.133-4.303A6.001 6.001 0 0 0 10.5 3.75Zm2.03 5.47a.75.75 0 0 0-1.06 0l-3 3a.75.75 0 1 0 1.06 1.06l1.72-1.72v4.94a.75.75 0 0 0 1.5 0v-4.94l1.72 1.72a.75.75 0 1 0 1.06-1.06l-3-3Z" clip-rule="evenodd"></path>
            </svg>
            <svg x-show="uploading" style="display: none;" class="shrink-0 size-6 animate-spin absolute inset-0 text-zinc-800 dark:text-white transition" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div class="flex flex-col items-center gap-2 text-center">
            <div class="text-sm font-medium text-zinc-800 dark:text-white cursor-pointer group-hover:underline">
                {{ $description }}
            </div>
            <div class="relative text-zinc-500 dark:text-white/60 text-sm">
                {{ $formatText }}
            </div>
        </div>
    </div>

    @error($modelName)
        <flux:error class="mt-2">{{ $message }}</flux:error>
    @enderror

    <div class="mt-4 flex flex-col gap-2">
        @if($file)
            <div class="overflow-hidden flex items-start shadow-xs bg-white dark:bg-white/10 min-h-10 text-base sm:text-sm rounded-lg block w-full border border-zinc-200 border-b-zinc-300/80 dark:border-white/10">
                <div class="p-[calc(0.5rem-1px)] flex items-baseline">
                    <button type="button" x-on:click="$flux.modal('{{ $modalName }}').show()" class="cursor-pointer relative mr-1 size-11 rounded-sm overflow-hidden after:absolute after:inset-0 after:inset-ring-[1px] after:inset-ring-black/7 dark:after:inset-ring-white/10 after:rounded-sm group">
                        <img class="h-full w-full object-cover transition group-hover:opacity-75" src="{{ $file->temporaryUrl() }}" alt="">
                    </button>
                </div>
                <div class="flex-1 overflow-hidden py-[calc(0.75rem-3px)] me-3 flex flex-col justify-center gap-1">
                    <div class="text-sm font-medium text-zinc-500 dark:text-white/80 whitespace-nowrap overflow-hidden text-ellipsis">{{ $file->getClientOriginalName() }}</div>
                    <div class="text-xs text-zinc-500">{{ __('Preview gambar baru') }} · {{ round($file->getSize() / 1024) }} KB</div>
                </div>
                <div class="p-[calc(0.25rem-1px)] flex-shrink-0 self-start flex h-full items-center gap-2">
                    <button type="button" wire:click="$set('{{ $modelName }}', null)" class="relative items-center font-medium justify-center gap-2 whitespace-nowrap h-8 text-sm rounded-md w-8 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-white self-start cursor-pointer mt-[3px] mr-1" aria-label="Hapus file">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M5.28 4.22a.75.75 0 0 0-1.06 1.06L6.94 8l-2.72 2.72a.75.75 0 1 0 1.06 1.06L8 9.06l2.72 2.72a.75.75 0 1 0 1.06-1.06L9.06 8l2.72-2.72a.75.75 0 0 0-1.06-1.06L8 6.94 5.28 4.22Z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @elseif($existingPath)
            <div class="overflow-hidden flex items-start shadow-xs bg-white dark:bg-white/10 min-h-10 text-base sm:text-sm rounded-lg block w-full border border-zinc-200 border-b-zinc-300/80 dark:border-white/10">
                <div class="p-[calc(0.5rem-1px)] flex items-baseline">
                    <button type="button" x-on:click="$flux.modal('{{ $modalName }}').show()" class="cursor-pointer relative mr-1 size-11 rounded-sm overflow-hidden after:absolute after:inset-0 after:inset-ring-[1px] after:inset-ring-black/7 dark:after:inset-ring-white/10 after:rounded-sm group">
                        <img class="h-full w-full object-cover transition group-hover:opacity-75" src="{{ asset('storage/' . $existingPath) }}" alt="">
                    </button>
                </div>
                <div class="flex-1 overflow-hidden py-[calc(0.75rem-3px)] me-3 flex flex-col justify-center gap-1">
                    <div class="text-sm font-medium text-zinc-500 dark:text-white/80 whitespace-nowrap overflow-hidden text-ellipsis">{{ basename($existingPath) }}</div>
                    <div class="text-xs text-zinc-500">{{ __('Gambar saat ini') }}</div>
                </div>
            </div>
        @endif
    </div>
</div>
