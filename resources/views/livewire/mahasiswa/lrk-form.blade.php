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
                            <flux:heading size="sm" class="text-emerald-900 dark:text-emerald-100">{{ __('Dokumen LRK Telah Disetujui DPL') }}</flux:heading>
                            <flux:badge color="green" size="sm">{{ __('Disetujui') }}</flux:badge>
                        </div>
                        <flux:text class="text-xs text-emerald-700 dark:text-emerald-300">
                            {{ __('Dokumen telah disetujui DPL dan dapat langsung dicetak secara resmi.') }}
                        </flux:text>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <flux:button href="{{ route('lrk.pdf', Auth::user()->group) }}" target="_blank" icon="printer" variant="primary" size="sm" class="w-full sm:w-auto">
                        {{ __('Cetak PDF') }}
                    </flux:button>
                </div>
            </div>
        @elseif($isSubmitted)
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-800">
                <div class="flex items-center gap-3">
                    <flux:icon name="clock" class="w-6 h-6 text-amber-600 dark:text-amber-400 shrink-0" />
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            <flux:heading size="sm" class="text-amber-900 dark:text-amber-100">{{ __('Dokumen LRK Sedang Ditinjau') }}</flux:heading>
                            <flux:badge color="amber" size="sm">{{ __('Diajukan') }}</flux:badge>
                        </div>
                        <flux:text class="text-xs text-amber-700 dark:text-amber-300">
                            {{ __('Dokumen telah diajukan kepada Dosen KKN untuk diperiksa. Anda dapat menarik kembali pengajuan jika perlu mengubah draf.') }}
                        </flux:text>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" icon="arrow-top-right-on-square" variant="subtle" size="sm" class="w-full sm:w-auto">
                        {{ __('Pratinjau PDF') }}
                    </flux:button>
                    <flux:modal.trigger name="confirm-withdraw-modal" class="w-full sm:w-auto">
                        <flux:button type="button" variant="danger" size="sm" class="w-full sm:w-auto">
                            {{ __('Tarik Pengajuan') }}
                        </flux:button>
                    </flux:modal.trigger>
                </div>
            </div>
        @elseif($isNeedsRevision)
            <div class="flex flex-col gap-3 p-4 rounded-xl bg-rose-50 border border-rose-200 dark:bg-rose-950/30 dark:border-rose-800">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <flux:icon name="exclamation-circle" class="w-6 h-6 text-rose-600 dark:text-rose-400 shrink-0" />
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm" class="text-rose-900 dark:text-rose-100">{{ __('Dokumen LRK Perlu Revisi') }}</flux:heading>
                                <flux:badge color="red" size="sm">{{ __('Perlu Revisi') }}</flux:badge>
                            </div>
                            <flux:text class="text-xs text-rose-700 dark:text-rose-300">
                                {{ __('DPL meminta perbaikan pada dokumen LRK. Silakan periksa catatan revisi di bawah ini dan ajukan kembali setelah diperbaiki.') }}
                            </flux:text>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" icon="arrow-top-right-on-square" variant="subtle" size="sm" class="w-full sm:w-auto">
                            {{ __('Pratinjau PDF') }}
                        </flux:button>
                        @if($isReady)
                            <flux:modal.trigger name="confirm-submit-modal" class="w-full sm:w-auto">
                                <flux:button type="button" icon="paper-airplane" variant="primary" size="sm" class="w-full sm:w-auto">
                                    {{ __('Ajukan Ulang LRK') }}
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    </div>
                </div>
                @if($lrkNotes)
                    <div class="p-3 bg-white dark:bg-zinc-900 rounded-lg border border-rose-200 dark:border-rose-900 text-xs text-rose-800 dark:text-rose-200">
                        <span class="font-semibold">{{ __('Catatan Revisi dari DPL:') }}</span> {{ $lrkNotes }}
                    </div>
                @endif
            </div>
        @else
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-4 rounded-xl bg-zinc-50 border border-zinc-200 dark:bg-white/5 dark:border-white/10">
                <div class="space-y-0.5">
                    <div class="flex items-center gap-2">
                        <flux:heading size="sm">{{ __('Status Dokumen LRK') }}</flux:heading>
                        @if($isReady)
                            <flux:badge color="blue" size="sm">{{ __('Siap Diajukan') }}</flux:badge>
                        @else
                            <flux:badge color="zinc" size="sm">{{ __('Draf') }}</flux:badge>
                        @endif
                    </div>
                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                        @if($isReady)
                            {{ __('Seluruh rencana program kerja telah disetujui DPL. Dokumen siap diajukan untuk ditinjau.') }}
                        @else
                            {{ __('Lengkapi isi laporan LRK. Pengajuan dapat dilakukan setelah seluruh rencana program disetujui DPL.') }}
                        @endif
                    </flux:text>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" icon="arrow-top-right-on-square" variant="subtle" size="sm" class="w-full sm:w-auto">
                        {{ __('Pratinjau PDF') }}
                    </flux:button>
                    @if($isReady)
                        <flux:modal.trigger name="confirm-submit-modal" class="w-full sm:w-auto">
                            <flux:button type="button" icon="paper-airplane" variant="primary" size="sm" class="w-full sm:w-auto">
                                {{ __('Ajukan LRK ke DPL') }}
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
            <flux:textarea wire:model="background" rows="5" :disabled="!$canEdit" placeholder="{{ __('Jelaskan kondisi lokasi KKN, yang meliputi : permasalahan dan potensi yang ditemukan pada saat proses observasi dan prioritas dalam pemilihan permasalahan.') }}" />
        </div>

        <flux:separator />

        {{-- Usulan Rencana Program --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Usulan Rencana Program') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-4">{{ __('Tuliskan paragraf pengantar untuk setiap jenis program yang direncanakan.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="program_multidisiplin_text" label="{{ __('Program Multidisiplin') }}" rows="4" :disabled="!$canEdit" placeholder="{{ __('Tulis usulan program multidisiplin yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya.') }}" />

                <flux:textarea wire:model="program_sosmas_text" label="{{ __('Program Sosial Kemasyarakatan') }}" rows="4" :disabled="!$canEdit" placeholder="{{ __('Tulis usulan program sosial kemasyarakatan yang disesuaikan dengan permasalahan dan potensi yang telah dijabarkan sebelumnya.') }}" />

                <flux:textarea wire:model="program_lainnya_text" label="{{ __('Program Lainnya') }}" rows="4" :disabled="!$canEdit" placeholder="{{ __('Tulis usulan program lainnya yang disesuaikan dengan wilayah.') }}" />
            </div>
        </div>

        <flux:separator />

        {{-- Storyboard & Skrip Video (Multidisiplin 3) --}}
        <div class="space-y-6">
            <div>
                <flux:heading size="md" class="mb-1">{{ __('Storyboard Video Profil (Multidisiplin 3)') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Rincian visual dan sketsa gambar per adegan video profil desa.') }}</flux:text>

                <div class="space-y-3">
                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-white/10">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-white/10 text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-300">
                                <tr>
                                    <th class="p-3 w-80">{{ __('Scene & Sketsa Visual') }}</th>
                                    <th class="p-3">{{ __('Deskripsi Visual / Framing') }}</th>
                                    @if($canEdit)
                                        <th class="p-3 w-12 text-center">{{ __('Aksi') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                                @forelse($storyboard_scenes as $index => $scene)
                                    <tr class="align-top hover:bg-zinc-50/50 dark:hover:bg-white/5">
                                        <td class="p-3">
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <flux:badge size="sm" color="zinc" class="font-bold">Scene {{ $index + 1 }}</flux:badge>
                                                </div>

                                                @php
                                                    $preview = $this->getStoryboardPreviewUrl($scene['image_url'] ?? '');
                                                @endphp

                                                @if($preview)
                                                    <div class="relative w-full h-36 bg-zinc-100 dark:bg-zinc-800 rounded-lg overflow-hidden border border-zinc-200 dark:border-white/10 flex items-center justify-center">
                                                        <img src="{{ $preview }}" alt="Preview Scene {{ $index + 1 }}" class="w-full h-full object-cover" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                                                        <div style="display:none;" class="flex-col items-center justify-center text-zinc-400 text-xs p-2 text-center">
                                                            <flux:icon.photo class="w-6 h-6 mb-1 text-zinc-400" />
                                                            <span>{{ __('Gagal memuat gambar') }}</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="w-full h-36 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-dashed border-zinc-200 dark:border-white/10 flex flex-col items-center justify-center text-zinc-400 text-xs p-2 text-center">
                                                        <flux:icon.photo class="w-6 h-6 mb-1 text-zinc-300 dark:text-zinc-600" />
                                                        <span>{{ __('Pratinjau gambar akan tampil di sini') }}</span>
                                                    </div>
                                                @endif

                                                <flux:input wire:model.live.debounce.500ms="storyboard_scenes.{{ $index }}.image_url" placeholder="https://drive.google.com/... atau link gambar" :disabled="!$canEdit" size="sm" />
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <flux:textarea wire:model="storyboard_scenes.{{ $index }}.visual" rows="6" :disabled="!$canEdit" placeholder="{{ __('Tuliskan detail aksi visual, objek, pergerakan talent, framing kamera (misal: Wide Shot, Close Up), dan suasana adegan...') }}" />
                                        </td>
                                        @if($canEdit)
                                            <td class="p-3 text-center align-middle">
                                                <flux:button type="button" wire:click="removeStoryboardScene({{ $index }})" variant="ghost" size="sm" icon="trash" class="text-rose-500 hover:text-rose-700" title="{{ __('Hapus adegan') }}" />
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canEdit ? 3 : 2 }}" class="p-6 text-center text-zinc-500 text-xs">
                                            <flux:icon.photo class="w-8 h-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                                            {{ __('Belum ada adegan storyboard. Klik "Tambah Adegan Storyboard" untuk mulai menambahkan panel.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($canEdit)
                        <div class="flex justify-start">
                            <flux:button type="button" wire:click="addStoryboardScene" variant="subtle" size="sm" icon="plus">
                                {{ __('Tambah Adegan Storyboard') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <flux:heading size="md" class="mb-1">{{ __('Skrip Video Profil (Naskah Audio-Visual)') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Naskah terstruktur adegan, visual on-screen, dialog/voice-over, dan estimasi durasi per adegan.') }}</flux:text>

                <div class="space-y-3">
                    <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-white/10">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-white/10 text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-300">
                                <tr>
                                    <th class="p-3 w-28 text-center">{{ __('Scene') }}</th>
                                    <th class="p-3">{{ __('Aksi Visual On-Screen') }}</th>
                                    <th class="p-3">{{ __('Dialog / Voice-Over / Narasi') }}</th>
                                    <th class="p-3 w-44">{{ __('Durasi Adegan') }}</th>
                                    @if($canEdit)
                                        <th class="p-3 w-12 text-center">{{ __('Aksi') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                                @forelse($video_script_scenes as $index => $scene)
                                    <tr class="align-top hover:bg-zinc-50/50 dark:hover:bg-white/5">
                                        <td class="p-3 text-center">
                                            <flux:badge size="sm" color="zinc" class="font-bold">Scene {{ $index + 1 }}</flux:badge>
                                        </td>
                                        <td class="p-2">
                                            <flux:textarea wire:model="video_script_scenes.{{ $index }}.visual" rows="3" :disabled="!$canEdit" placeholder="{{ __('Aksi visual atau teks di layar...') }}" />
                                        </td>
                                        <td class="p-2">
                                            <flux:textarea wire:model="video_script_scenes.{{ $index }}.audio" rows="3" :disabled="!$canEdit" placeholder="{{ __('Kalimat narasi atau dialog pembicara...') }}" />
                                        </td>
                                        <td class="p-2">
                                            <flux:input wire:model="video_script_scenes.{{ $index }}.duration" :disabled="!$canEdit" placeholder="{{ __('misal: 15s atau 00:00 - 00:15') }}" />
                                        </td>
                                        @if($canEdit)
                                            <td class="p-2 text-center align-middle">
                                                <flux:button type="button" wire:click="removeVideoScriptScene({{ $index }})" variant="ghost" size="sm" icon="trash" class="text-rose-500 hover:text-rose-700" title="{{ __('Hapus adegan') }}" />
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canEdit ? 5 : 4 }}" class="p-6 text-center text-zinc-500 text-xs">
                                            <flux:icon.video-camera class="w-8 h-8 mx-auto mb-2 text-zinc-300 dark:text-zinc-600" />
                                            {{ __('Belum ada naskah adegan skrip. Klik "Tambah Adegan Skrip" untuk mulai menambahkan baris.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($canEdit)
                        <div class="flex justify-start">
                            <flux:button type="button" wire:click="addVideoScriptScene" variant="subtle" size="sm" icon="plus">
                                {{ __('Tambah Adegan Skrip') }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <flux:separator />

        {{-- Bab 3: Rencana Pelaksanaan (Tabel 7 Rencana Umum Kegiatan) --}}
        <div class="space-y-4">
            <div>
                <flux:heading size="md" class="mb-1">{{ __('Rencana Pelaksanaan: Tabel 7 Rencana Umum Kegiatan (Mingguan)') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Atur alokasi minggu pelaksanaan untuk kegiatan makro operasional KKN kelompok Anda (M1 s/d M:weeks).', ['weeks' => count($weekKeys)]) }}</flux:text>
            </div>

            <div class="space-y-3">
                <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-white/10">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-white/10 text-xs font-semibold uppercase text-zinc-600 dark:text-zinc-300">
                            <tr>
                                <th class="p-3 w-12 text-center">No</th>
                                <th class="p-3">{{ __('Nama Kegiatan Umum') }}</th>
                                @foreach($weekKeys as $wKey)
                                    <th class="p-3 w-14 text-center">{{ strtoupper($wKey) }}</th>
                                @endforeach
                                @if($canEdit)
                                    <th class="p-3 w-12 text-center">{{ __('Aksi') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-white/10">
                            @forelse($macro_schedules as $index => $sched)
                                <tr class="hover:bg-zinc-50/50 dark:hover:bg-white/5">
                                    <td class="p-3 text-center text-xs font-semibold text-zinc-500">
                                        {{ $index + 1 }}.
                                    </td>
                                    <td class="p-2">
                                        <flux:input wire:model="macro_schedules.{{ $index }}.kegiatan" :disabled="!$canEdit" placeholder="Nama kegiatan umum..." />
                                    </td>
                                    @foreach($weekKeys as $wKey)
                                        <td class="p-2 text-center">
                                            <input type="checkbox" wire:model="macro_schedules.{{ $index }}.{{ $wKey }}" @disabled(!$canEdit) class="rounded border-zinc-300 text-primary-600 focus:ring-primary-500 w-4 h-4" />
                                        </td>
                                    @endforeach
                                    @if($canEdit)
                                        <td class="p-2 text-center">
                                            <flux:button type="button" wire:click="removeMacroSchedule({{ $index }})" variant="ghost" size="sm" icon="trash" class="text-rose-500 hover:text-rose-700" />
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($weekKeys) + ($canEdit ? 3 : 2) }}" class="p-4 text-center text-zinc-500 text-xs">
                                        {{ __('Belum ada kegiatan umum mingguan.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($canEdit)
                    <div class="flex justify-start">
                        <flux:button type="button" wire:click="addMacroSchedule" variant="subtle" size="sm" icon="plus">
                            {{ __('Tambah Kegiatan Umum') }}
                        </flux:button>
                    </div>
                @endif
            </div>
        </div>

        <flux:separator />

        {{-- Lampiran Dokumentasi Survey --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Lampiran Dokumentasi Survey') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf keterangan untuk lampiran foto dokumentasi survey lapangan dan sematkan tautan gambarnya.') }}</flux:text>
            
            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="survey_documentation_text" rows="3" label="{{ __('Keterangan Survey') }}" :disabled="!$canEdit" placeholder="{{ __('Dokumentasi survey dilakukan pada tanggal ... di lokasi ... Kegiatan meliputi ...') }}" />

                @if($canEdit)
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
                @elseif($survey_document_preview_url)
                    <div class="space-y-1">
                        <flux:text class="text-xs font-medium text-zinc-500">{{ __('Gambar Dokumentasi Survey') }}</flux:text>
                        <flux:card variant="soft" class="overflow-hidden p-0 max-w-md">
                            <img src="{{ $survey_document_preview_url }}" alt="Dokumentasi Survey" class="w-full h-48 object-cover" />
                        </flux:card>
                    </div>
                @endif
            </div>
        </div>

        <flux:separator />

        {{-- Lampiran Peta Lokasi --}}
        <div>
            <flux:heading size="md" class="mb-2">{{ __('Lampiran Peta Lokasi') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 mb-3">{{ __('Tuliskan paragraf keterangan peta lokasi dan sematkan tautan gambar peta.') }}</flux:text>

            <div class="flex flex-col gap-4">
                <flux:textarea wire:model="location_map_text" rows="3" label="{{ __('Keterangan Peta') }}" :disabled="!$canEdit" placeholder="{{ __('Peta di bawah ini menunjukkan lokasi pelaksanaan KKN yang berada di ...') }}" />

                @if($canEdit)
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
                @elseif($location_map_preview_url)
                    <div class="space-y-1">
                        <flux:text class="text-xs font-medium text-zinc-500">{{ __('Gambar Peta Lokasi') }}</flux:text>
                        <flux:card variant="soft" class="overflow-hidden p-0 max-w-md">
                            <img src="{{ $location_map_preview_url }}" alt="Peta Lokasi" class="w-full h-48 object-cover" />
                        </flux:card>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
            <flux:button href="{{ route('lrk.index') }}" wire:navigate variant="ghost" class="w-full sm:w-auto">{{ __('Kembali') }}</flux:button>

            @if($isFinalized)
                <flux:button href="{{ route('lrk.pdf', Auth::user()->group) }}" target="_blank" icon="printer" variant="primary" class="w-full sm:w-auto">
                    {{ __('Cetak PDF') }}
                </flux:button>
            @elseif($isSubmitted)
                <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" variant="subtle" icon="arrow-top-right-on-square" class="w-full sm:w-auto">
                    {{ __('Pratinjau PDF') }}
                </flux:button>
                <flux:modal.trigger name="confirm-withdraw-modal" class="w-full sm:w-auto">
                    <flux:button type="button" variant="danger" class="w-full sm:w-auto">
                        {{ __('Tarik Pengajuan') }}
                    </flux:button>
                </flux:modal.trigger>
            @else
                <flux:button type="button" wire:click="preview" wire:loading.attr="disabled" variant="subtle" icon="arrow-top-right-on-square" class="w-full sm:w-auto">
                    {{ __('Pratinjau PDF') }}
                </flux:button>
                @if($canEdit)
                    <flux:button type="submit" class="w-full sm:w-auto">{{ __('Simpan') }}</flux:button>
                @endif
                @if($isReady)
                    <flux:modal.trigger name="confirm-submit-modal" class="w-full sm:w-auto">
                        <flux:button type="button" icon="paper-airplane" variant="primary" class="w-full sm:w-auto">
                            {{ $isNeedsRevision ? __('Ajukan Ulang LRK') : __('Ajukan LRK ke DPL') }}
                        </flux:button>
                    </flux:modal.trigger>
                @endif
            @endif
        </div>

    </form>
</flux:card>

{{-- Submit Confirmation Modal --}}
<flux:modal name="confirm-submit-modal" class="max-w-md w-full">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Ajukan Dokumen LRK ke DPL?') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-500">
                {{ __('Apakah Anda yakin ingin mengajukan dokumen LRK kepada Dosen KKN? Pastikan seluruh isian latar belakang, rencana program, dan lampiran sudah lengkap dan benar.') }}
            </flux:text>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
            <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Kembali') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="submit" variant="primary" icon="paper-airplane" class="w-full sm:w-auto">
                {{ __('Ya, Ajukan') }}
            </flux:button>
        </div>
    </div>
</flux:modal>

{{-- Withdraw Confirmation Modal --}}
<flux:modal name="confirm-withdraw-modal" class="max-w-md w-full">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Tarik Pengajuan Dokumen LRK?') }}</flux:heading>
            <flux:text class="mt-2 text-sm text-zinc-500">
                {{ __('Apakah Anda yakin ingin membatalkan pengajuan dokumen LRK? Dokumen akan kembali ke status draf sehingga dapat Anda edit kembali.') }}
            </flux:text>
        </div>

        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 pt-3 border-t border-zinc-200 dark:border-white/10">
            <flux:modal.close class="w-full sm:w-auto block sm:inline-block">
                <flux:button variant="ghost" class="w-full sm:w-auto">{{ __('Batal') }}</flux:button>
            </flux:modal.close>
            <flux:button wire:click="withdraw" variant="danger" class="w-full sm:w-auto">
                {{ __('Ya, Tarik Pengajuan') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
</div>
