<div
    x-data="{
        weights: @entangle('weights'),
        get totalPercentage() {
            let sum = 0;
            for (let key in this.weights) {
                sum += (parseFloat(this.weights[key]) || 0);
            }
            return Math.round(sum * 10) / 10;
        },
        get isValid() {
            return Math.abs(this.totalPercentage - 100) < 0.01;
        },
        confirmSave() {
            if (!this.isValid) {
                if (typeof Flux !== 'undefined' && Flux.toast) {
                    Flux.toast({
                        variant: 'danger',
                        heading: '{{ __('Validasi Gagal') }}',
                        text: '{{ __('Total akumulasi bobot harus bernilai tepat 100%. Saat ini: ') }}' + this.totalPercentage.toFixed(1) + '%.',
                    });
                }
                return;
            }

            $wire.weights = this.weights;
            $wire.promptSave();
        }
    }"
>
    <div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <flux:heading size="xl">{{ __('Bobot Penilaian (SIAP)') }}</flux:heading>
                <flux:subheading>{{ __('Atur persentase bobot setiap aspek penilaian untuk konversi nilai sistem SIAP.') }}</flux:subheading>
            </div>
        </div>

        <flux:card>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($aspects as $code => $info)
                    <flux:card wire:key="aspect-card-{{ $code }}" class="flex flex-col gap-3 p-4">
                        <x-slider 
                            x-model="weights['{{ $code }}']" 
                            label="{{ $info['name'] }}" 
                            description="{{ __('Aspek asal:') }} {{ $info['source'] }}"
                            min="0" 
                            max="100" 
                            step="0.5" 
                        />
                    </flux:card>
                @endforeach
            </div>

            <flux:separator class="my-6" />

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-2">
                    <flux:text>{{ __('Total Akumulasi:') }}</flux:text>
                    
                    {{-- Valid indicator --}}
                    <div x-show="isValid" class="flex items-center gap-2">
                        <flux:text variant="strong" color="green" class="text-base font-semibold">
                            <span x-text="Math.round(totalPercentage) + '%'">{{ number_format($this->totalPercentage, 0) }}%</span>
                        </flux:text>
                        <flux:badge size="sm" color="green">{{ __('Tepat 100%') }}</flux:badge>
                    </div>

                    {{-- Invalid indicator --}}
                    <div x-show="!isValid" x-cloak class="flex items-center gap-2">
                        <flux:text variant="strong" color="red" class="text-base font-semibold">
                            <span x-text="totalPercentage.toFixed(1) + '%'">{{ number_format($this->totalPercentage, 1) }}%</span>
                        </flux:text>
                        <flux:badge size="sm" color="red">{{ __('Belum 100%') }}</flux:badge>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <flux:button type="button" variant="ghost" @click="$wire.loadWeights().then(() => { weights = $wire.weights; })">
                        {{ __('Reset Nilai') }}
                    </flux:button>
                    <flux:button type="button" variant="primary" @click="confirmSave()">
                        {{ __('Simpan Bobot') }}
                    </flux:button>
                </div>
            </div>
        </flux:card>
    </div>

    {{-- Confirmation Modal --}}
    <flux:modal name="confirm-save-modal" class="max-w-md w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Konfirmasi Perubahan Bobot') }}</flux:heading>
                <flux:text class="mt-2 text-sm text-zinc-500">
                    {{ __('Apakah Anda yakin ingin menyimpan perubahan bobot penilaian ini? Perubahan ini akan memengaruhi perhitungan konversi nilai SIAP mahasiswa selanjutnya.') }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Batal') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Ya, Simpan') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
