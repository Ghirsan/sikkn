<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DailyLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LogbookPdfController extends Controller
{
    public function download(User $student)
    {
        // Pastikan user yang dicetak adalah mahasiswa
        if ($student->role !== \App\Enums\UserRole::Mahasiswa) {
            abort(404, 'User is not a student');
        }

        // Ambil grup dan period
        $group = $student->group()->with(['period', 'dpls'])->first();
        if (!$group || !$group->period) {
            abort(404, 'Group or Period not found for this student');
        }

        $period = $group->period;
        $dpl = $group->dpls->first(); // Mengambil DPL pertama

        // Ambil daily logs dengan aktivitas, urut berdasarkan tanggal
        $logs = DailyLog::with('activities')
            ->where('student_id', $student->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->orderBy('date', 'asc')
            ->get();

        // Kelompokkan logs per minggu (Minggu Ke-N)
        $logsGroupedByWeek = [];
        
        foreach ($logs as $log) {
            // Hitung minggu ke-berapa
            $dayDiff = $period->start_date->diffInDays($log->date);
            $weekNumber = floor($dayDiff / 7) + 1;
            
            // Hitung hari ke-berapa
            $log->day_number = $dayDiff + 1;
            
            if (!isset($logsGroupedByWeek[$weekNumber])) {
                $logsGroupedByWeek[$weekNumber] = [];
            }
            $logsGroupedByWeek[$weekNumber][] = $log;
        }

        $pdf = Pdf::loadView('pdf.logbook', [
            'student' => $student,
            'group' => $group,
            'dpl' => $dpl,
            'logsGroupedByWeek' => $logsGroupedByWeek,
        ]);

        return $pdf->download('Logbook_KKN_'.$student->nim.'.pdf');
    }
}
