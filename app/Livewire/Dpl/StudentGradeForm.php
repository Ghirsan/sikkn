<?php

namespace App\Livewire\Dpl;

use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\Period;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

class StudentGradeForm extends Component
{
    public $student;

    public bool $isGradingOpen = true;

    #[Validate('required|numeric|min:0|max:100')]
    public $pembekalan;

    #[Validate('required|numeric|min:0|max:100')]
    public $gelar_karya;

    #[Validate('required|numeric|min:0|max:100')]
    public $kehadiran;

    #[Validate('required|numeric|min:0|max:100')]
    public $lrk;

    #[Validate('required|numeric|min:0|max:100')]
    public $integritas;

    #[Validate('required|numeric|min:0|max:100')]
    public $sosial_kemasyarakatan;

    #[Validate('required|numeric|min:0|max:100')]
    public $lpk;

    #[Validate('required|numeric|min:0|max:100')]
    public $ujian_akhir;

    #[Validate('nullable|string')]
    public $keterangan;

    public function mount($student = null)
    {
        $studentId = $student ?? request()->route('student');
        $this->student = User::with(['grade', 'group.period'])->findOrFail($studentId);

        $currentPeriod = Period::current();
        if ($this->student->group?->period_id && $currentPeriod) {
            $this->isGradingOpen = $this->student->group->period_id === $currentPeriod->id;
        } else {
            $this->isGradingOpen = true;
        }

        // Populate existing grade if any
        if ($this->student->grade) {
            $this->pembekalan = $this->student->grade->pembekalan;
            $this->gelar_karya = $this->student->grade->gelar_karya;
            $this->kehadiran = $this->student->grade->kehadiran;
            $this->lrk = $this->student->grade->lrk;
            $this->integritas = $this->student->grade->integritas;
            $this->sosial_kemasyarakatan = $this->student->grade->sosial_kemasyarakatan;
            $this->lpk = $this->student->grade->lpk;
            $this->ujian_akhir = $this->student->grade->ujian_akhir;
            $this->keterangan = $this->student->grade->keterangan;
        } else {
            $this->pembekalan = 0;
            $this->gelar_karya = 0;
            $this->kehadiran = 0;
            $this->lrk = 0;
            $this->integritas = 0;
            $this->sosial_kemasyarakatan = 0;
            $this->lpk = 0;
            $this->ujian_akhir = 0;
            $this->keterangan = null;
        }
    }

    #[Computed]
    public function aktivitasPartisipatif()
    {
        return (float) $this->sosial_kemasyarakatan * 1.0;
    }

    #[Computed]
    public function hasilProyek()
    {
        return (float) $this->lrk * 1.0;
    }

    #[Computed]
    public function tugas()
    {
        return ((float) $this->kehadiran + (float) $this->integritas) * 0.5;
    }

    #[Computed]
    public function quiz()
    {
        return ((float) $this->pembekalan + (float) $this->gelar_karya) * 0.5;
    }

    #[Computed]
    public function uts()
    {
        return (float) $this->lpk * 1.0;
    }

    #[Computed]
    public function uas()
    {
        return (float) $this->ujian_akhir * 1.0;
    }

    #[Computed]
    public function gradeWeights(): array
    {
        return GradeWeight::getWeights();
    }

    #[Computed]
    public function formattedWeights(): array
    {
        return GradeWeight::getPercentages();
    }

    #[Computed]
    public function nilaiAkhir()
    {
        $weights = $this->gradeWeights;

        return round(
            ($this->aktivitasPartisipatif * ($weights['aktivitas_partisipatif'] ?? 0.30)) +
            ($this->hasilProyek * ($weights['hasil_proyek'] ?? 0.25)) +
            ($this->tugas * ($weights['tugas'] ?? 0.20)) +
            ($this->quiz * ($weights['quiz'] ?? 0.05)) +
            ($this->uts * ($weights['uts'] ?? 0.10)) +
            ($this->uas * ($weights['uas'] ?? 0.10)),
            2
        );
    }

    #[Computed]
    public function nilaiHuruf()
    {
        $na = $this->nilaiAkhir;

        return match (true) {
            $na >= 80 => 'A',
            $na >= 70 => 'B',
            $na >= 60 => 'C',
            $na >= 50 => 'D',
            default => 'E',
        };
    }

    public function saveGrade()
    {
        if (! $this->isGradingOpen) {
            abort(403, 'Penilaian untuk periode KKN ini telah ditutup karena periode baru telah dibuat.');
        }

        $this->validate();

        $grade = Grade::firstOrNew([
            'student_id' => $this->student->id,
            'dpl_id' => Auth::id(),
        ]);

        $grade->pembekalan = $this->pembekalan;
        $grade->gelar_karya = $this->gelar_karya;
        $grade->kehadiran = $this->kehadiran;
        $grade->lrk = $this->lrk;
        $grade->integritas = $this->integritas;
        $grade->sosial_kemasyarakatan = $this->sosial_kemasyarakatan;
        $grade->lpk = $this->lpk;
        $grade->ujian_akhir = $this->ujian_akhir;
        $grade->keterangan = $this->keterangan;

        $grade->calculateGrade();
        $grade->save();

        $this->dispatch('toast', message: 'Nilai berhasil disimpan.', type: 'success');

        return $this->redirect(route('dpl.grades.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.dpl.student-grade-form');
    }
}
