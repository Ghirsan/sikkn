<?php

namespace App\Livewire\Observer;

use App\Enums\UserRole;
use App\Models\Grade;
use App\Models\GradeWeight;
use App\Models\User;
use App\Services\GradeExcelService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GradeDetail extends Component
{
    public $student;

    public float $pembekalan = 0;

    public float $gelar_karya = 0;

    public float $kehadiran = 0;

    public float $lrk = 0;

    public float $integritas = 0;

    public float $sosial_kemasyarakatan = 0;

    public float $lpk = 0;

    public float $ujian_akhir = 0;

    public ?string $keterangan = null;

    public function mount($student = null): void
    {
        $studentId = $student ?? request()->route('student');
        $this->student = User::with(['grade.dpl', 'group.period', 'group.leadDpl', 'group.dpls', 'studyProgram', 'faculty'])->findOrFail($studentId);

        if ($this->student->role !== UserRole::Mahasiswa) {
            abort(404, 'Mahasiswa tidak ditemukan.');
        }

        $user = Auth::user();

        // Enforce observer scope
        if ($user->hasRole(UserRole::Prodi) && $this->student->study_program_id !== $user->study_program_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat nilai mahasiswa program studi lain.');
        }

        if ($user->hasRole(UserRole::Fakultas) && $this->student->faculty_id !== $user->faculty_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat nilai mahasiswa fakultas lain.');
        }

        if ($this->student->grade) {
            $this->pembekalan = (float) $this->student->grade->pembekalan;
            $this->gelar_karya = (float) $this->student->grade->gelar_karya;
            $this->kehadiran = (float) $this->student->grade->kehadiran;
            $this->lrk = (float) $this->student->grade->lrk;
            $this->integritas = (float) $this->student->grade->integritas;
            $this->sosial_kemasyarakatan = (float) $this->student->grade->sosial_kemasyarakatan;
            $this->lpk = (float) $this->student->grade->lpk;
            $this->ujian_akhir = (float) $this->student->grade->ujian_akhir;
            $this->keterangan = $this->student->grade->keterangan;
        }
    }

    #[Computed]
    public function isGraded(): bool
    {
        return $this->student->grade !== null;
    }

    #[Computed]
    public function graderDpl(): ?User
    {
        return $this->student->grade?->dpl ?? $this->student->group?->leadDpl ?? $this->student->group?->dpls->first();
    }

    #[Computed]
    public function backUrl(): string
    {
        if (Auth::user()?->hasRole(UserRole::Prodi)) {
            return route('prodi.grades.index');
        }

        return route('fakultas.grades.index');
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
    public function aktivitasPartisipatif(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        return (float) ($this->student->grade->aktivitas_partisipatif ?? ($this->sosial_kemasyarakatan * 1.0));
    }

    #[Computed]
    public function hasilProyek(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        return (float) ($this->student->grade->hasil_proyek ?? ($this->lrk * 1.0));
    }

    #[Computed]
    public function tugas(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        return (float) ($this->student->grade->tugas ?? (($this->kehadiran + $this->integritas) * 0.5));
    }

    #[Computed]
    public function quiz(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        return (float) ($this->student->grade->quiz ?? (($this->pembekalan + $this->gelar_karya) * 0.5));
    }

    #[Computed]
    public function uts(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        return (float) ($this->student->grade->uts ?? ($this->lpk * 1.0));
    }

    #[Computed]
    public function uas(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        return (float) ($this->student->grade->uas ?? ($this->ujian_akhir * 1.0));
    }

    #[Computed]
    public function nilaiAkhir(): float
    {
        if (! $this->isGraded) {
            return 0.0;
        }

        if ($this->student->grade->final_grade !== null) {
            return (float) $this->student->grade->final_grade;
        }

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
    public function nilaiHuruf(): string
    {
        if (! $this->isGraded) {
            return '-';
        }

        return $this->student->grade->grade_letter ?? Grade::determineGradeLetter($this->nilaiAkhir, $this->student?->nim);
    }

    public function exportExcel(GradeExcelService $excelService)
    {
        if (! $this->isGraded) {
            $this->dispatch('toast', message: 'Mahasiswa ini belum dinilai sehingga data nilai tidak dapat diekspor.', type: 'warning');

            return;
        }

        return $excelService->exportSingleStudentSiapGrade($this->student);
    }

    public function render()
    {
        return view('livewire.observer.grade-detail');
    }
}
