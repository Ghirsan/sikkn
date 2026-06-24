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

                <flux:textarea wire:model="program_lainnya_text" label="{{ __('Program Lainnya (Bebas)') }}" rows="4" placeholder="{{ __('Selain program utama, kelompok juga merencanakan ...') }}" />
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
                    <flux:input type="file" wire:model="location_map_image" label="{{ __('Gambar Peta Lokasi') }}" accept="image/*" />
                    <flux:text class="text-xs text-zinc-400 mt-1">{{ __('Format: JPG, PNG. Maksimal 2MB.') }}</flux:text>

                    @if($existing_map_path && !$location_map_image)
                        <div class="mt-3 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <flux:text class="text-xs text-zinc-500 mb-2">{{ __('Peta saat ini:') }}</flux:text>
                            <img src="{{ asset('storage/' . $existing_map_path) }}" alt="Peta Lokasi" class="max-w-sm rounded-lg border border-zinc-200 dark:border-zinc-700" />
                        </div>
                    @endif

                    @if($location_map_image)
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <flux:text class="text-xs text-blue-600 dark:text-blue-400 mb-2">{{ __('Preview gambar baru:') }}</flux:text>
                            <img src="{{ $location_map_image->temporaryUrl() }}" alt="Preview Peta" class="max-w-sm rounded-lg border border-blue-200 dark:border-blue-700" />
                        </div>
                    @endif
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
</flux:card>
