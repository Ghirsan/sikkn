<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\MentoringLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class MentoringLogsPdfController extends Controller
{
    public function download(User $student)
    {
        if ($student->role !== UserRole::Mahasiswa) {
            abort(404, 'User is not a student');
        }

        $student->loadMissing(['studyProgram.faculty', 'faculty']);

        $group = $student->group()->with(['period', 'dpls', 'leadDpl'])->first();
        if (! $group || ! $group->period) {
            abort(404, 'Group or Period not found for this student');
        }

        $period = $group->period;
        $dpl = $group->leadDpl ?? $group->dpls->first();

        // Ambil catatan pembimbingan mahasiswa dalam rentang periode KKN
        $logs = MentoringLog::with(['program.programType'])
            ->where('student_id', $student->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->orderBy('date', 'asc')
            ->get();

        // Kelompokkan catatan pembimbingan berdasarkan program
        $groupedLogs = $logs->groupBy('program_id');

        // Jika sama sekali belum ada log, sediakan 1 grup kosong untuk mencetak lembar template
        if ($groupedLogs->isEmpty()) {
            $groupedLogs = collect([null => collect()]);
        }

        $pdf = Pdf::loadView('pdf.mentoring', [
            'student' => $student,
            'group' => $group,
            'period' => $period,
            'dpl' => $dpl,
            'groupedLogs' => $groupedLogs,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption(['dpi' => 96, 'defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

        return $pdf->download('Buku_Pembimbingan_KKN_'.$student->nim.'.pdf');
    }
}
