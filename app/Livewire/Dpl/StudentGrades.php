<?php

namespace App\Livewire\Dpl;

use App\Enums\UserRole;
use App\Models\Period;
use App\Models\User;
use App\Services\GradeExcelService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class StudentGrades extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $selectedGroupId = '';

    public $gradeFile = null;

    public bool $showImportPanel = false;

    public function updatedSelectedGroupId(): void
    {
        $this->resetPage();
    }

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

        $currentPeriod = Period::current();

        if (! $currentPeriod) {
            $this->importErrors = ['Tidak ada periode KKN aktif untuk melakukan penilaian.'];
            $this->dispatch('toast', message: 'Tidak ada periode KKN.', type: 'danger');

            return;
        }

        $allowedGroupsQuery = $user->dplGroups()->where('period_id', $currentPeriod->id)->with(['students.grade', 'students.group']);

        if ($this->selectedGroupId) {
            $allowedGroupsQuery->where('groups.id', $this->selectedGroupId);
        }

        $allowedGroups = $allowedGroupsQuery->get();

        if ($allowedGroups->isEmpty()) {
            $this->importErrors = ['Kelompok yang dipilih tidak berada pada periode KKN saat ini. Penilaian telah ditutup karena periode baru telah dibuat.'];
            $this->dispatch('toast', message: 'Penilaian telah ditutup untuk kelompok ini.', type: 'danger');

            return;
        }

        $this->validate([
            'gradeFile' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'gradeFile.required' => 'Silakan pilih file Excel (.xlsx) untuk diimpor.',
            'gradeFile.mimes' => 'Format file harus berupa dokumen Excel (.xlsx).',
            'gradeFile.max' => 'Ukuran file Excel tidak boleh melebihi 10MB.',
        ]);

        $allowedStudents = $allowedGroups->pluck('students')->flatten();

        $result = $excelService->importDplGrades($this->gradeFile, $user, $allowedStudents);

        // Clean up temporary file immediately from server
        try {
            $this->gradeFile?->delete();
        } catch (\Throwable) {
            // Ignore if already cleaned up
        }

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

        $groupsQuery = $user->dplGroups()->with(['period']);

        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }

        $groups = $groupsQuery->get();
        $allGroups = $user->dplGroups()->get();

        $currentPeriod = Period::current();

        // Grading is open if any of the displayed groups belong to the current period
        $isGradingOpen = $currentPeriod !== null && $groups->isNotEmpty() && $groups->some(fn ($g) => $g->period_id === $currentPeriod->id);

        $assignedGroupIds = $groups->pluck('id');

        $studentsQuery = User::where('role', UserRole::Mahasiswa)
            ->whereIn('group_id', $assignedGroupIds)
            ->with(['grade', 'studyProgram', 'group']);

        $totalStudents = (clone $studentsQuery)->count();
        $gradedCount = (clone $studentsQuery)->whereHas('grade')->count();

        $students = $studentsQuery->latest()->paginate(10);

        return view('livewire.dpl.student-grades', [
            'groups' => $groups,
            'allGroups' => $allGroups,
            'students' => $students,
            'isGradingOpen' => $isGradingOpen,
            'currentPeriod' => $currentPeriod,
            'stats' => [
                'total' => $totalStudents,
                'graded' => $gradedCount,
                'ungraded' => max(0, $totalStudents - $gradedCount),
            ],
        ]);
    }
}
