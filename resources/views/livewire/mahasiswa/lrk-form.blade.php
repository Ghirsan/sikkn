<div>
<flux:card>
    <form wire:submit="save" class="flex flex-col gap-6">

        {{-- Status & Quick Action Header --}}
        @if($isFinalized)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/30 dark:border-emerald-800">
                <div class="flex items-center gap-3">
                    <flux:icon name="check-badge" class="w-6 h-6 text-emerald-600 dark:text-emerald-400 shrink-0" />
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <flux:heading size="sm" class="text-emerald-900 dark:text-emerald-100">{{ __('Dokumen LRK Telah Difinalisasi') }}</flux:heading>
                            <flux:badge color="green" size="sm">{{ __('Final') }}</flux:badge>
                        </div>
                        <flux:text class="text-xs text-emerald-700 dark:text-emerald-300">
                            {{ __('Dokumen telah siap dan dapat langsung dicetak secara resmi.') }}
                        </flux:text>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <flux:button href="{{ route('lrk.pdf', Auth::user()->group) }}" target="_blank" icon="printer" variant="primary" size="sm" class="w-full sm:w-auto">
                        {{ __('Cetak PDF') }}
                    </flux:button>
                    <flux:modal.trigger name="confirm-unfinalize-modal" class="w-full sm:w-auto">
                        <flux:button type="button" variant="ghost" size="sm" class="w-full sm:w-auto">
                            {{ __('Ubah Kembali') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-zinc-50 border border-zinc-200 dark:bg-white/5 dark:border-white/10">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <flux:heading size="sm">{{ __('Status Dokumen LRK') }}</flux:heading>
                        @if($isReady)
                            <flux:badge color="green" size="sm">{{ __('Siap Difinalisasi') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Draf') }}</flux:badge>
                        @endif
                    </div>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                        @if($isReady)
                            {{ __('Seluruh rencana program kerja telah disetujui DPL. Dokumen siap difinalisasi.') }}
                        @else
                            {{ __('Simpan perubahan dan buka tampilan dokumen PDF LRK di halaman baru.') }}
                        @endif
                    </flux:text>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" icon="arrow-top-right-on-square" variant="subtle" size="sm" class="w-full sm:w-auto">
                        {{ __('Pratinjau PDF') }}
                    </flux:button>
                    @if($isReady)
                        <flux:modal.trigger name="confirm-finalize-modal" class="w-full sm:w-auto">
                            <flux:button type="button" icon="check" variant="primary" size="sm" class="w-full sm:w-auto">
                                {{ __('Finalisasi LRK') }}
                            </flux:button>
                        </flux:modal.trigger>
                    @endif
                </div>
            </div>
        @endif

        {{-- Latar Belakang --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Latar Belakang') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf latar belakang pelaksanaan KKN di lokasi kelompok Anda.') }}</flux:text>
            <flux:textarea wire:model="background" rows="5" placeholder="{{ __('Jelaskan kondisi lokasi KKN, yang meliputi : permasalahan dan potensi yang ditemukan pada saat proses observasi dan prioritas dalam pemilihan permasalahan.') }}" />
        </div>

        <flux:separator />

        {{-- Usulan Rencana Program --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Usulan Rencana Program') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-4">{{ __('Tuliskan paragraf pengantar untuk setiap jenis program yang direncanakan.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="program_multidisiplin_text" label="{{ __('Program Multidisiplin') }}" rows="4" placeholder="{{ __('Tulis usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya.') }}" />

                <flux:textarea wire:model="program_sosmas_text" label="{{ __('Program Sosial Kemasyarakatan') }}" rows="4" placeholder="{{ __('Tulis usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya.') }}" />

                <flux:textarea wire:model="program_lainnya_text" label="{{ __('Program Lainnya') }}" rows="4" placeholder="{{ __('Tulis usulan program lainnya yang disesuaikan dengan wilayah.') }}" />
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
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf keterangan untuk lampiran foto dokumentasi survey lapangan dan sematkan tautan gambarnya.') }}</flux:text>
            
            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="survey_documentation_text" rows="3" label="{{ __('Keterangan Survey') }}" placeholder="{{ __('Dokumentasi survey dilakukan pada tanggal ... di lokasi ... Kegiatan meliputi ...') }}" />

                <div>
                    <x-image-url-input
                        model-name="survey_document_url"
                        :preview-url="$survey_document_preview_url"
                        check-action="validateSurveyDocument"
                        verified-name="survey_document_verified"
                        label="{{ __('Tautan Gambar Dokumentasi Survey') }}"
                        placeholder="{{ __('https://drive.google.com/file/d/.../view atau https://ibb.co/...') }}"
                        description="{{ __('Gunakan tautan berbagi Google Drive (akses publik) atau tautan gambar imgbb.') }}"
                    />
                </div>
            </div>
        </div>

        <flux:separator />

        {{-- Lampiran Peta Lokasi --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Lampiran Peta Lokasi') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf keterangan peta lokasi dan sematkan tautan gambar peta.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="location_map_text" rows="3" label="{{ __('Keterangan Peta') }}" placeholder="{{ __('Peta di bawah ini menunjukkan lokasi pelaksanaan KKN yang berada di ...') }}" />

                <div>
                    <x-image-url-input
                        model-name="location_map_url"
                        :preview-url="$location_map_preview_url"
                        check-action="validateLocationMap"
                        verified-name="location_map_verified"
                        label="{{ __('Tautan Gambar Peta Lokasi') }}"
                        placeholder="{{ __('https://drive.google.com/file/d/.../view atau https://ibb.co/...') }}"
                        description="{{ __('Gunakan tautan berbagi Google Drive (akses publik) atau tautan gambar imgbb.') }}"
                    />
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
            <flux:button href="{{ route('lrk.index') }}" wire:navigate variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>

            @if($isFinalized)
                <flux:modal.trigger name="confirm-unfinalize-modal" class="w-full sm:w-auto">
                    <flux:button type="button" variant="ghost" class="w-full sm:w-auto">
                        {{ __('Ubah Kembali') }}
                    </flux:button>
                </flux:modal.trigger>
                <flux:button href="{{ route('lrk.pdf', Auth::user()->group) }}" target="_blank" icon="printer" variant="primary" class="w-full sm:w-auto">
                    {{ __('Cetak PDF') }}
                </flux:button>
            @else
                <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" variant="subtle" icon="arrow-top-right-on-square" class="w-full sm:w-auto">
                    {{ __('Pratinjau PDF') }}
                </flux:button>
                <flux:button type="submit" variant="ghost" class="w-full sm:w-auto">{{ __('Simpan Laporan') }}</flux:button>
                @if($isReady)
                    <flux:modal.trigger name="confirm-finalize-modal" class="w-full sm:w-auto">
                        <flux:button type="button" icon="check" variant="primary" class="w-full sm:w-auto">
                            {{ __('Finalisasi LRK') }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            @endif
        </div>

    </form>
</flux:card>

{{-- Finalize Confirmation Modal --}}
<flux:modal name="confirm-finalize-modal" class="max-w-md w-full">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Finalisasi Dokumen LRK?') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-500">
                {{ __('Apakah Anda yakin ingin memfinalisasi dokumen LRK? Dokumen yang telah difinalisasi menandakan seluruh rencana program kerja telah disetujui dan siap dicetak.') }}
            </flux:text>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
            <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="finalize" variant="primary" icon="check" class="w-full sm:w-auto">
                {{ __('Ya, Finalisasi') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

{{-- Unfinalize Confirmation Modal --}}
<flux:modal name="confirm-unfinalize-modal" class="max-w-md w-full">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Ubah Kembali Dokumen LRK?') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-500">
                {{ __('Apakah Anda yakin ingin membatalkan status finalisasi dokumen LRK? Dokumen akan kembali ke status draf agar dapat diedit kembali.') }}
            </flux:text>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
            <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="unfinalize" variant="danger" icon="arrow-uturn-left" class="w-full sm:w-auto">
                {{ __('Ya, Ubah Kembali') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
</div>
