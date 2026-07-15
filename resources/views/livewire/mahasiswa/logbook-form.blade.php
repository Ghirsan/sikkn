<div>
    <flux:breadcrumbs class="mb-6">
        <flux:breadcrumbs.item href="{{ route('dashboard') }}">{{ __('Dashboard') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item href="{{ route('logbook.index') }}">{{ __('Logbook') }}</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>{{ $logId ? __('Edit Entri') : __('Tambah Entri') }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex justify-between items-center mb-6">
        <div>
            <flux:heading size="xl" level="1">{{ $logId ? __('Edit Catatan Harian') : __('Catatan Harian Baru') }}</flux:heading>
            <flux:subheading>{{ __('Isi detail kegiatan dan catatan penting Anda hari ini.') }}</flux:subheading>
        </div>
    </div>

    <flux:card>
        <form wire:submit="saveLog" class="space-y-6">
            <div>
                <flux:heading size="md" class="mb-4">{{ __('Informasi Waktu') }}</flux:heading>
                <flux:input type="date" wire:model="date" label="Tanggal Kegiatan" :disabled="$logId !== null" />
            </div>

            <div>
                <flux:heading size="md" class="mb-4">{{ __('Rincian Kegiatan') }}</flux:heading>
                <div class="space-y-4">
                    @foreach($activities as $index => $activity)
                        <div class="relative group p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                            <div class="flex flex-col md:flex-row gap-4 items-start">
                                <div class="flex gap-2 w-full md:w-auto">
                                    <flux:input type="time" wire:model="activities.{{ $index }}.start_time" label="Mulai" />
                                    <flux:input type="time" wire:model="activities.{{ $index }}.end_time" label="Selesai" />
                                </div>
                                <div class="w-full flex-1">
                                    <flux:input wire:model="activities.{{ $index }}.activity_description" label="Deskripsi Kegiatan" placeholder="Contoh: Survei UMKM..." />
                                </div>
                            </div>
                            @if(count($activities) > 1)
                                <flux:button variant="danger" size="sm" icon="trash" class="absolute -top-3 -right-3 rounded-full hidden group-hover:flex" wire:click="removeActivity({{ $index }})" />
                            @endif
                        </div>
                    @endforeach
                    <flux:button variant="subtle" size="sm" icon="plus" wire:click="addActivity" class="w-full mt-2">{{ __('Tambah Kegiatan') }}</flux:button>
                </div>
                @error('activities') <flux:error>{{ $message }}</flux:error> @enderror
            </div>

            <div>
                <flux:heading size="md" class="mb-4">{{ __('Catatan Penting Harian') }}</flux:heading>
                <div class="flex flex-col gap-4">
                    <flux:textarea wire:model="importantNotes" label="Catatan Teks" placeholder="Opsional: Tuliskan catatan penting hari ini..." rows="4" />

                    <x-image-upload
                        modelName="notesImage"
                        :file="$notesImage"
                        :existingPath="$existingImagePath"
                        label="Gambar Pendukung"
                        description="Klik untuk mengunggah gambar catatan harian"
                        formatText="Format: JPG, PNG. Maksimal 2MB."
                        modalName="logbook-notes-image-preview"
                    />
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" href="{{ route('logbook.index') }}" wire:navigate>{{ __('Batal') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
