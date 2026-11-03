<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        @if(! $this->isGraded)
            <flux:callout variant="warning" icon="exclamation-circle">
                <flux:callout.heading>{{ __('Belum Dinilai') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Mahasiswa ini belum dinilai oleh Dosen Pembimbing Lapangan (DPL). Formulir ditampilkan dengan nilai default.') }}</flux:callout.text>
            </flux:callout>
        @else
            <flux:callout variant="info" icon="information-circle">
                <flux:callout.heading>{{ __('Mode Hanya Baca') }}</flux:callout.heading>
                <flux:callout.text>{{ __('Detail penilaian KKN mahasiswa ini bersifat hanya baca untuk peran Pengamat (Observer).') }}</flux:callout.text>
            </flux:callout>
        @endif

        <flux:card>
            <div class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">{{ __('Aspek Penilaian KKN') }}</flux:heading>
                    <flux:subheading>{{ __('Nilai (0-100) untuk setiap aspek penilaian. Formulir ini ditampilkan dalam mode hanya-baca.') }}</flux:subheading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input value="{{ number_format($pembekalan, 2) }}" label="{{ __('Pembekalan') }}" disabled />
                    <flux:input value="{{ number_format($gelar_karya, 2) }}" label="{{ __('Gelar Karya') }}" disabled />
                    <flux:input value="{{ number_format($kehadiran, 2) }}" label="{{ __('Kehadiran') }}" disabled />
                    <flux:input value="{{ number_format($lrk, 2) }}" label="{{ __('LRK') }}" disabled />
                    <flux:input value="{{ number_format($integritas, 2) }}" label="{{ __('Integritas') }}" disabled />
                    <flux:input value="{{ number_format($sosial_kemasyarakatan, 2) }}" label="{{ __('Sosial Kemasyarakatan') }}" disabled />
                    <flux:input value="{{ number_format($lpk, 2) }}" label="{{ __('LPK') }}" disabled />
                    <flux:input value="{{ number_format($ujian_akhir, 2) }}" label="{{ __('Ujian Akhir') }}" disabled />
                </div>

                <flux:textarea label="{{ __('Keterangan / Catatan Evaluasi DPL') }}" rows="3" disabled>{{ $keterangan ?? '-' }}</flux:textarea>

                <div class="flex items-center justify-end gap-2 mt-4">
                    <flux:button href="{{ $this->backUrl }}" wire:navigate variant="ghost">{{ __('Kembali') }}</flux:button>
                </div>
            </div>
        </flux:card>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Mahasiswa') }}</flux:heading>
            
            <div class="flex items-center gap-3 mb-4">
                <flux:avatar :name="$student->name" :initials="$student->initials()" />
                <div>
                    <flux:text variant="strong" class="leading-tight">{{ $student->name }}</flux:text>
                    <flux:text variant="subtle" size="sm">{{ $student->nim }}</flux:text>
                </div>
            </div>

            <flux:separator class="my-4" />

            <div class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <flux:text variant="subtle">{{ __('Program Studi') }}</flux:text>
                    <flux:text variant="strong" class="text-right">{{ $student->prodi ?? '-' }}</flux:text>
                </div>
                <div class="flex justify-between gap-4">
                    <flux:text variant="subtle">{{ __('Fakultas') }}</flux:text>
                    <flux:text variant="strong" class="text-right">{{ $student->fakultas ?? '-' }}</flux:text>
                </div>
                <div class="flex justify-between gap-4">
                    <flux:text variant="subtle">{{ __('Kelompok') }}</flux:text>
                    <flux:text variant="strong" class="text-right">{{ $student->group?->name ?? '-' }}</flux:text>
                </div>
                <div class="flex justify-between gap-4">
                    <flux:text variant="subtle">{{ __('Desa') }}</flux:text>
                    <flux:text variant="strong" class="text-right">{{ $student->group?->village ?? '-' }}</flux:text>
                </div>
                <div class="flex justify-between gap-4">
                    <flux:text variant="subtle">{{ __('DPL') }}</flux:text>
                    <flux:text variant="strong" class="text-right">
                        @if($this->graderDpl)
                            {{ $this->graderDpl->name }}
                        @else
                            -
                        @endif
                    </flux:text>
                </div>
            </div>
        </flux:card>

        <flux:card>
            <div class="flex items-center justify-between mb-4">
                <flux:heading size="lg">{{ __('Konversi Nilai SIAP') }}</flux:heading>
                @if($this->isGraded && $this->nilaiHuruf !== '-')
                    <flux:badge size="sm" color="blue">{{ $this->nilaiHuruf }}</flux:badge>
                @endif
            </div>
            
            <div class="space-y-3">
                @php
                    $items = [
                        ['key' => 'aktivitas_partisipatif', 'label' => __('Aktivitas Partisipatif'), 'value' => $this->aktivitasPartisipatif],
                        ['key' => 'hasil_proyek',           'label' => __('Hasil Proyek'),           'value' => $this->hasilProyek],
                        ['key' => 'tugas',                  'label' => __('Tugas'),                  'value' => $this->tugas],
                        ['key' => 'quiz',                   'label' => __('Quiz'),                   'value' => $this->quiz],
                        ['key' => 'uts',                    'label' => __('UTS'),                    'value' => $this->uts],
                        ['key' => 'uas',                    'label' => __('UAS'),                    'value' => $this->uas],
                    ];
                @endphp

                @foreach ($items as $item)
                    <div class="flex justify-between items-center text-sm">
                        <flux:text>
                            {{ $item['label'] }} ({{ $this->formattedWeights[$item['key']] ?? 0 }}%)
                        </flux:text>
                        <flux:text variant="strong">
                            {{ number_format($item['value'], 2) }}
                        </flux:text>
                    </div>
                @endforeach
                
                <flux:separator class="my-2" />

                <div class="flex justify-between items-center mt-2">
                    <flux:text variant="strong" class="font-semibold">{{ __('Nilai Akhir') }}</flux:text>
                    <flux:text variant="strong" class="text-xl font-bold">{{ number_format($this->nilaiAkhir, 2) }}</flux:text>
                </div>

                <div class="flex justify-between items-center">
                    <flux:text variant="subtle">{{ __('Nilai Huruf') }}</flux:text>
                    <flux:text variant="strong" class="text-base font-semibold">{{ $this->nilaiHuruf }}</flux:text>
                </div>

                <flux:separator class="my-3" />

                <div class="pt-1">
                    <flux:button
                        wire:click="exportExcel"
                        icon="document-arrow-down"
                        variant="primary"
                        class="w-full justify-center min-h-[44px]"
                        :disabled="!$this->isGraded"
                    >
                        {{ __('Export Excel SIAP') }}
                    </flux:button>
                    @if(! $this->isGraded)
                        <flux:text variant="subtle" size="xs" class="text-center mt-2 block">
                            {{ __('Ekspor Excel tersedia setelah mahasiswa dinilai oleh DPL.') }}
                        </flux:text>
                    @endif
                </div>
            </div>
        </flux:card>
    </div>
</div>

