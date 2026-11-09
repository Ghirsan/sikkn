<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\DailyLog;
use App\Models\User;
use App\Services\PdfImageResolver;
use App\Services\PdfKitService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class LogbookPdfController extends Controller
{
    public function __construct(
        protected PdfKitService $pdfKitService,
        protected PdfImageResolver $imageResolver
    ) {}

    public function download(User $student, ?bool $inline = null): Response
    {
        $inline = $inline ?? ! request()->boolean('download');

        // Pastikan user yang dicetak adalah mahasiswa
        if ($student->role !== UserRole::Mahasiswa) {
            abort(404, 'User is not a student');
        }

        Gate::authorize('viewLogbook', $student);

        $student->loadMissing(['studyProgram.faculty', 'faculty']);

        // Ambil grup dan period
        $group = $student->group()->with(['period', 'dpls', 'leadDpl'])->first();
        if (! $group || ! $group->period) {
            abort(404, 'Group or Period not found for this student');
        }

        $period = $group->period;
        $dpl = $group->leadDpl ?? $group->dpls->first();

        // Ambil daily logs dengan aktivitas, urut berdasarkan tanggal
        $logs = DailyLog::with('activities')
            ->where('student_id', $student->id)
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->orderBy('date', 'asc')
            ->get();

        // Kelompokkan logs per minggu (Minggu Ke-N)
        $logsGroupedByWeek = [];

        foreach ($logs as $log) {
            $dayDiff = $period->start_date->diffInDays($log->date);
            $weekNumber = (int) floor($dayDiff / 7) + 1;
            $dayNumber = $dayDiff + 1;

            if (! isset($logsGroupedByWeek[$weekNumber])) {
                $logsGroupedByWeek[$weekNumber] = [];
            }

            $imageSrc = $log->image_path ? $this->imageResolver->resolve($log->image_path, $log->image_url) : null;

            $logsGroupedByWeek[$weekNumber][] = [
                'day_number' => $dayNumber,
                'date' => $log->date?->format('Y-m-d'),
                'date_formatted' => $log->date?->locale('id')->isoFormat('dddd, D MMMM Y'),
                'activities' => $log->activities->map(fn ($a) => [
                    'start_time' => $a->start_time ? substr($a->start_time, 0, 5) : '',
                    'end_time' => $a->end_time ? substr($a->end_time, 0, 5) : '',
                    'activity_description' => $a->activity_description,
                ])->values()->all(),
                'important_notes' => $log->important_notes,
                'image_path' => $log->image_path,
                'image_url' => $log->image_url,
                'image_base64' => $imageSrc,
            ];
        }

        $logoPath = public_path('images/logo-undip-logbook.png');
        if (! file_exists($logoPath)) {
            $logoPath = public_path('images/logo-undip.png');
        }

        $payload = [
            'printed_at' => now()->locale('id')->isoFormat('D MMMM Y'),
            'student' => [
                'name' => $student->name,
                'nim' => $student->nim,
                'prodi' => $student->studyProgram?->name,
                'fakultas' => $student->faculty?->name,
            ],
            'group' => [
                'name' => $group->name,
                'village' => $group->village,
                'district' => $group->district,
                'regency' => $group->regency,
                'province' => $group->province ?? 'Jawa Tengah',
            ],
            'period' => [
                'semester' => $period->semester?->value,
                'year' => $period->year,
            ],
            'dpl' => [
                'name' => $dpl?->name,
                'nip' => $dpl?->nip,
            ],
            'logsGroupedByWeek' => $logsGroupedByWeek,
            'assets' => [
                'logo_path' => file_exists($logoPath) ? $logoPath : null,
            ],
        ];

        $pdfBinary = $this->pdfKitService->generate('generate-logbook.js', $payload);

        return $this->pdfKitService->makeResponse($pdfBinary, 'Logbook_KKN_'.$student->nim.'.pdf', inline: $inline);
    }
}
