<?php

namespace App\Livewire\Admin;

use App\Models\GradeWeight;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GradeWeights extends Component
{
    /**
     * Weights in percentage (0 - 100), keyed by aspect code.
     *
     * @var array<string, float|int>
     */
    public array $weights = [];

    /**
     * Aspect metadata (name and source aspect in KKN).
     *
     * @var array<string, array{name: string, source: string}>
     */
    public array $aspects = [];

    public function mount(): void
    {
        $this->loadWeights();
    }

    public function loadWeights(): void
    {
        $records = GradeWeight::orderBy('id')->get();

        $sourceMap = [
            'aktivitas_partisipatif' => 'Sosial Kemasyarakatan',
            'hasil_proyek' => 'Laporan Rencana Kegiatan (LRK)',
            'tugas' => 'Kehadiran (50%) & Integritas (50%)',
            'quiz' => 'Pembekalan (50%) & Gelar Karya (50%)',
            'uts' => 'Laporan Pelaksanaan Kegiatan (LPK)',
            'uas' => 'Ujian Akhir',
        ];

        $this->weights = [];
        $this->aspects = [];

        foreach ($records as $record) {
            $this->weights[$record->code] = $record->percentage;
            $this->aspects[$record->code] = [
                'name' => $record->name,
                'source' => $sourceMap[$record->code] ?? '',
            ];
        }

        $this->resetValidation();
    }

    #[Computed]
    public function totalPercentage(): float
    {
        return (float) array_sum(array_map('floatval', $this->weights));
    }

    public function promptSave(): void
    {
        $this->validate([
            'weights.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (abs($this->totalPercentage - 100) >= 0.01) {
            $errorMessage = 'Total akumulasi bobot harus bernilai tepat 100%. Saat ini: '.number_format($this->totalPercentage, 1).'%.';

            $this->addError('total', $errorMessage);

            Flux::toast(
                variant: 'danger',
                heading: __('Validasi Gagal'),
                text: $errorMessage,
            );

            $this->dispatch('toast', message: $errorMessage, type: 'error');

            return;
        }

        Flux::modal('confirm-save-modal')->show();
    }

    public function save()
    {
        $this->validate([
            'weights.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        if (abs($this->totalPercentage - 100) >= 0.01) {
            $errorMessage = 'Total akumulasi bobot harus bernilai tepat 100%. Saat ini: '.number_format($this->totalPercentage, 1).'%.';

            $this->addError('total', $errorMessage);

            Flux::toast(
                variant: 'danger',
                heading: __('Validasi Gagal'),
                text: $errorMessage,
            );

            $this->dispatch('toast', message: $errorMessage, type: 'error');

            return null;
        }

        foreach ($this->weights as $code => $percentage) {
            GradeWeight::where('code', $code)->update([
                'weight' => round((float) $percentage / 100, 4),
            ]);
        }

        Flux::toast(
            variant: 'success',
            text: __('Bobot penilaian berhasil disimpan.'),
        );

        $this->dispatch('toast', message: 'Bobot penilaian berhasil disimpan.', type: 'success');

        return $this->redirect(route('admin.grade-weights.index'), navigate: true);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.admin.grade-weights');
    }
}
