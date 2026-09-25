<div>
    <flux:card>
        <form wire:submit="saveDraft" class="space-y-6">
            <div>
                <flux:heading size="md" class="mb-4">{{ __('Informasi Waktu') }}</flux:heading>
                <flux:input type="text" value="{{ $date ? \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') : '' }}" label="Tanggal Kegiatan" disabled />
                <input type="hidden" wire:model="date" />
            </div>

            <div>
                <flux:heading size="md" class="mb-4">{{ __('Rincian Kegiatan') }}</flux:heading>
                <x-activity-repeater :activities="$activities" />
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
                <flux:button type="submit" variant="primary">{{ __('Simpan Draf') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
