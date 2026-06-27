<flux:card>
    <form wire:submit="save" class="flex flex-col gap-6">

        {{-- Latar Belakang --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Latar Belakang') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf latar belakang pelaksanaan KKN di lokasi kelompok Anda.') }}</flux:text>
            <flux:textarea wire:model="background" rows="5" placeholder="{{ __('Desa/kelurahan ... terletak di ... dengan kondisi ...') }}" />
        </div>

        <flux:separator />

        {{-- Usulan Rencana Program --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Usulan Rencana Program') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-4">{{ __('Tuliskan paragraf pengantar untuk setiap jenis program yang direncanakan.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="program_multidisiplin_text" label="{{ __('Program Multidisiplin') }}" rows="4" placeholder="{{ __('Rencana program multidisiplin yang akan dilaksanakan meliputi ...') }}" />

                <flux:textarea wire:model="program_sosmas_text" label="{{ __('Program Sosial Kemasyarakatan') }}" rows="4" placeholder="{{ __('Program sosial kemasyarakatan yang dirancang bertujuan untuk ...') }}" />

                <flux:textarea wire:model="program_lainnya_text" label="{{ __('Program Lainnya') }}" rows="4" placeholder="{{ __('Selain program utama, kelompok juga merencanakan ...') }}" />
            </div>
        </div>

        <flux:separator />

        {{-- Storyboard & Skrip Video (Multidisiplin Video) --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Storyboard & Skrip Video Profil') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-4">{{ __('Bagian ini untuk program Multidisiplin yang berformat video profil desa.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="storyboard_text" label="{{ __('Storyboard') }}" rows="5" placeholder="{{ __('Scene 1: ...\nScene 2: ...\nScene 3: ...') }}" />

                <flux:textarea wire:model="video_script_text" label="{{ __('Skrip Video') }}" rows="5" placeholder="{{ __('Opening: Selamat datang di Desa ...\nVoice Over: Desa ini memiliki potensi ...') }}" />
            </div>
        </div>

        <flux:separator />

        {{-- Lampiran Dokumentasi Survey --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Lampiran Dokumentasi Survey') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf keterangan untuk lampiran foto-foto dokumentasi survey lapangan.') }}</flux:text>
            <flux:textarea wire:model="survey_documentation_text" rows="4" placeholder="{{ __('Dokumentasi survey dilakukan pada tanggal ... di lokasi ... Kegiatan meliputi ...') }}" />
        </div>

        <flux:separator />

        {{-- Lampiran Peta Lokasi --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Lampiran Peta Lokasi') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf keterangan peta lokasi dan unggah gambar peta.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="location_map_text" rows="3" label="{{ __('Keterangan Peta') }}" placeholder="{{ __('Peta di bawah ini menunjukkan lokasi pelaksanaan KKN yang berada di ...') }}" />

                <div>
                    <x-image-upload
                        modelName="location_map_image"
                        :file="$location_map_image"
                        :existingPath="$existing_map_path"
                        label="Gambar Peta Lokasi"
                        modalName="location-map-preview"
                    />
                </div>
            </div>
        </div>

        <flux:separator />

        {{-- Actions --}}
        <div class="flex gap-2 justify-end">
            <flux:button href="{{ route('lrk.index') }}" wire:navigate variant="ghost">{{ __('Batal') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Simpan Laporan') }}</flux:button>
        </div>

    </form>

    <flux:modal name="location-map-preview" class="max-w-4xl w-full space-y-4">
        <div>
            <flux:heading size="lg">{{ __('Preview Peta Lokasi') }}</flux:heading>
        </div>
        <div>
            @if($location_map_image)
                <img src="{{ $location_map_image->temporaryUrl() }}" alt="Peta Lokasi" class="w-full h-auto max-h-[75vh] object-contain rounded-lg border border-zinc-200 dark:border-zinc-700" />
            @elseif($existing_map_path)
                <img src="{{ asset('storage/' . $existing_map_path) }}" alt="Peta Lokasi" class="w-full h-auto max-h-[75vh] object-contain rounded-lg border border-zinc-200 dark:border-zinc-700" />
            @endif
        </div>
    </flux:modal>
</flux:card>
