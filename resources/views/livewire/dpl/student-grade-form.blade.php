<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <flux:card>
            <form wire:submit="saveGrade" class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">{{ __('Aspek Penilaian KKN') }}</flux:heading>
                    <flux:subheading>{{ __('Masukkan nilai (0-100) untuk setiap aspek. Prediksi nilai SIAP akan diperbarui otomatis.') }}</flux:subheading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model.live.debounce.300ms="pembekalan" type="number" step="0.01" min="0" max="100" label="{{ __('Pembekalan') }}" />
                    <flux:input wire:model.live.debounce.300ms="gelar_karya" type="number" step="0.01" min="0" max="100" label="{{ __('Gelar Karya') }}" />
                    <flux:input wire:model.live.debounce.300ms="kehadiran" type="number" step="0.01" min="0" max="100" label="{{ __('Kehadiran') }}" />
                    <flux:input wire:model.live.debounce.300ms="lrk" type="number" step="0.01" min="0" max="100" label="{{ __('LRK') }}" />
                    <flux:input wire:model.live.debounce.300ms="integritas" type="number" step="0.01" min="0" max="100" label="{{ __('Integritas') }}" />
                    <flux:input wire:model.live.debounce.300ms="sosial_kemasyarakatan" type="number" step="0.01" min="0" max="100" label="{{ __('Sosial Kemasyarakatan') }}" />
                    <flux:input wire:model.live.debounce.300ms="lpk" type="number" step="0.01" min="0" max="100" label="{{ __('LPK') }}" />
                    <flux:input wire:model.live.debounce.300ms="ujian_akhir" type="number" step="0.01" min="0" max="100" label="{{ __('Ujian Akhir') }}" />
                </div>

                <flux:textarea wire:model="keterangan" label="{{ __('Keterangan') }}" rows="3" />

                <div class="flex items-center justify-end gap-2 mt-4">
                    <flux:button href="{{ route('dpl.grades.index') }}" wire:navigate variant="ghost">{{ __('Batal') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Simpan Nilai') }}</flux:button>
                </div>
            </form>
        </flux:card>
    </div>

    <div class="lg:col-span-1 space-y-6">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Preview Konversi SIAP') }}</flux:heading>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <flux:text>{{ __('Aktivitas Partisipatif') }} (30%)</flux:text>
                    <flux:text variant="strong">{{ number_format($this->aktivitasPartisipatif, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('Hasil Proyek') }} (25%)</flux:text>
                    <flux:text variant="strong">{{ number_format($this->hasilProyek, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('Tugas') }} (20%)</flux:text>
                    <flux:text variant="strong">{{ number_format($this->tugas, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('Quiz') }} (5%)</flux:text>
                    <flux:text variant="strong">{{ number_format($this->quiz, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('UTS') }} (10%)</flux:text>
                    <flux:text variant="strong">{{ number_format($this->uts, 2) }}</flux:text>
                </div>
                <div class="flex justify-between">
                    <flux:text>{{ __('UAS') }} (10%)</flux:text>
                    <flux:text variant="strong">{{ number_format($this->uas, 2) }}</flux:text>
                </div>
                
                <flux:separator class="my-2" />

                <div class="flex justify-between items-center mt-2">
                    <flux:text variant="strong" class="font-semibold">{{ __('Nilai Akhir') }}</flux:text>
                    <flux:text variant="strong" class="text-xl font-bold">{{ number_format($this->nilaiAkhir, 2) }}</flux:text>
                </div>

                <div class="flex justify-between items-center mt-1">
                    <flux:text variant="strong" class="font-semibold">{{ __('Nilai Huruf') }}</flux:text>
                    <flux:badge size="lg" color="green" inset="top bottom">{{ $this->nilaiHuruf }}</flux:badge>
                </div>
            </div>
        </flux:card>
    </div>
</div>

