<?php

namespace App\Livewire\Dpl;

use App\Enums\PeriodStatus;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentGrades extends Component
{
    public string $selectedGroupId = '';

    public function exportCsv()
    {
        $user = Auth::user();

        $groupsQuery = $user->dplGroups()->with(['students.grade', 'students.group']);

        if ($this->selectedGroupId) {
            $groupsQuery->where('groups.id', $this->selectedGroupId);
        }

        $groups = $groupsQuery->get();
        $students = $groups->pluck('students')->flatten();

        $filename = 'Export_Nilai_KKN_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($students) {
            $file = fopen('php://output', 'w');
            
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, [
                'Nama', 'NIM', 'Prodi', 'Fakultas', 'Desa',
                'Pembekalan', 'Gelar Karya', 'Kehadiran', 'LRK', 'Integritas', 'Sosial Kemasyarakatan', 'LPK', 'Ujian Akhir',
                'Aktivitas Partisipatif', 'Hasil Proyek', 'Tugas', 'Quiz', 'UTS', 'UAS', 'Nilai Akhir', 'Nilai Huruf',
                'Keterangan'
            ], ';');

            foreach ($students as $student) {
                $grade = $student->grade;
                $village = $student->group ? $student->group->village : '-';
                
                if ($grade) {
                    fputcsv($file, [
                        $student->name,
                        $student->nim,
                        $student->prodi ?? '-',
                        $student->fakultas ?? '-',
                        $village,
                        $grade->pembekalan,
                        $grade->gelar_karya,
                        $grade->kehadiran,
                        $grade->lrk,
                        $grade->integritas,
                        $grade->sosial_kemasyarakatan,
                        $grade->lpk,
                        $grade->ujian_akhir,
                        $grade->aktivitas_partisipatif,
                        $grade->hasil_proyek,
                        $grade->tugas,
                        $grade->quiz,
                        $grade->uts,
                        $grade->uas,
                        $grade->final_grade,
                        $grade->grade_letter,
                        $grade->keterangan
                    ], ';');
                } else {
                    fputcsv($file, [
                        $student->name,
                        $student->nim,
                        $student->prodi ?? '-',
                        $student->fakultas ?? '-',
                        $village,
                        '-', '-', '-', '-', '-', '-', '-', '-',
                        '-', '-', '-', '-', '-', '-', '-', '-',
                        '-'
                    ], ';');
                }
            }
            fclose($file);
        }, $filename);
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
