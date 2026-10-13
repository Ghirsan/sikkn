<?php

namespace App\Livewire\Dpl;

use App\Enums\PeriodStatus;
use App\Services\GradeExcelService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentGrades extends Component
{
    use WithFileUploads;

    public string $selectedGroupId = '';

    public $gradeFile = null;

    public bool $showImportPanel = false;

    /**
     * @var array<string>
     */
    public array $importErrors = [];

    public function toggleImportPanel(): void
    {
        $this->showImportPanel = ! $this->showImportPanel;
        $this->importErrors = [];
        $this->reset('gradeFile');
    }

    public function exportExcel(GradeExcelService $excelService)
    {
        $user = Auth::user();

        $groupsQuery = $user->dplGroups()->with(['students.grade', 'students.group']);

        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }

        $groups = $groupsQuery->get();
        $students = $groups->pluck('students')->flatten();

        $groupName = null;
        if ($this->selectedGroupId) {
            $selectedGroup = $groups->firstWhere('id', $this->selectedGroupId);
            $groupName = $selectedGroup ? $selectedGroup->name : null;
        }

        return $excelService->exportDplGrades($students, $groupName);
    }

    public function exportCsv(GradeExcelService $excelService)
    {
        return $this->exportExcel($excelService);
    }

    public function importExcel(GradeExcelService $excelService)
    {
        $user = Auth::user();

        $allGroups = $user->dplGroups()->with('period')->get();
        $periodCompleted = $allGroups->isNotEmpty() && $allGroups->every(fn ($g) => $g->period?->status === PeriodStatus::Completed);

        if (! $periodCompleted) {
            $this->importErrors = ['Penilaian belum dapat diimpor karena masa pelaksanaan KKN belum berakhir.'];
            $this->dispatch('toast', message: 'Masa pelaksanaan KKN belum berakhir.', type: 'danger');

            return;
        }

        $this->validate([
            'gradeFile' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'gradeFile.required' => 'Silakan pilih file Excel (.xlsx) untuk diimpor.',
            'gradeFile.mimes' => 'Format file harus berupa dokumen Excel (.xlsx).',
            'gradeFile.max' => 'Ukuran file Excel tidak boleh melebihi 10MB.',
        ]);

        $allowedStudents = $user->dplGroups()->with(['students.grade', 'students.group'])->get()->pluck('students')->flatten();

        $result = $excelService->importDplGrades($this->gradeFile, $user, $allowedStudents);

        if ($result['success']) {
            $this->reset(['gradeFile', 'importErrors']);
            $this->showImportPanel = false;

            $msg = 'Berhasil mengimpor '.$result['imported_count'].' nilai mahasiswa.';
            if ($result['skipped_count'] > 0) {
                $msg .= ' ('.$result['skipped_count'].' mahasiswa dilewati karena belum diisi).';
            }

            $this->dispatch('toast', message: $msg, type: 'success');
        } else {
            $this->importErrors = $result['errors'];
            $this->dispatch('toast', message: 'Gagal mengimpor file. Periksa daftar kesalahan.', type: 'danger');
        }
    }

    public function render()
    {
        $user = Auth::user();

        $groupsQuery = $user->dplGroups()->with(['students.grade', 'period']);

        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }

        $groups = $groupsQuery->get();
        $allGroups = $user->dplGroups()->get();

        // If there's no period, or if ANY group is not completed, we consider period not completed.
        // It's safer to just check if every group is completed.
        $periodCompleted = $groups->isNotEmpty() && $groups->every(fn ($g) => $g->period?->status === PeriodStatus::Completed);

        $students = $groups->pluck('students')->flatten();
        $graded = $students->filter(fn ($s) => $s->grade !== null)->count();

        return view('livewire.dpl.student-grades', [
            'groups' => $groups,
            'allGroups' => $allGroups,
            'students' => $students,
            'periodCompleted' => $periodCompleted,
            'stats' => [
                'total' => $students->count(),
                'graded' => $graded,
                'ungraded' => $students->count() - $graded,
            ],
        ]);
    }
}
