@props(['activities'])

<div class="space-y-4">
    <div class="flex flex-col gap-4">
        @foreach($activities as $index => $activity)
            <div class="rounded-xl border border-zinc-200 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs overflow-hidden">
                {{-- Item Header --}}
                <div class="flex items-center justify-between gap-3 px-4 py-3 bg-zinc-50 dark:bg-white/5 border-b border-zinc-200 dark:border-white/10">
                    <div class="flex items-center gap-2 min-w-0">
                        <flux:icon.clock variant="mini" class="shrink-0 text-zinc-400 dark:text-zinc-500" />
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300 truncate">
                            {{ __('Kegiatan') }} #{{ $index + 1 }}
                        </flux:text>
                    </div>
                    @if(count($activities) > 1)
                        <flux:button wire:click="removeActivity({{ $index }})" variant="ghost" size="sm" icon="x-mark" class="shrink-0 text-zinc-400 hover:text-red-500 dark:text-zinc-500 dark:hover:text-red-400" />
                    @endif
                </div>

                {{-- Item Body --}}
                <div class="p-4 flex flex-col md:flex-row gap-4 items-start">
                    <div class="flex gap-2 w-full md:w-auto">
                        <flux:input type="time" wire:model="activities.{{ $index }}.start_time" label="Mulai" />
                        <flux:input type="time" wire:model="activities.{{ $index }}.end_time" label="Selesai" />
                    </div>
                    <div class="w-full flex-1">
                        <flux:input wire:model="activities.{{ $index }}.activity_description" label="Deskripsi Kegiatan" placeholder="Contoh: Survei UMKM..." />
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <flux:button variant="subtle" size="sm" icon="plus" wire:click="addActivity" class="w-full mt-2">{{ __('Tambah Kegiatan') }}</flux:button>
</div>
