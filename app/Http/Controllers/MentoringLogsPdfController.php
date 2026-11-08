<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\MentoringLog;
use App\Models\User;
use App\Services\PdfKitService;
use Illuminate\Http\Response;

class MentoringLogsPdfController extends Controller
{
    public function __construct(
        protected PdfKitService $pdfKitService
    ) {}

    public function download(User $student, ?bool $inline = null): Response
    {
        $inline = $inline ?? ! request()->boolean('download');

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
        $groupedLogsRaw = $logs->groupBy('program_id');

        if ($groupedLogsRaw->isEmpty()) {
            $groupedLogsRaw = collect([null => collect()]);
        }

        $groupedLogs = [];
        foreach ($groupedLogsRaw as $progId => $programLogs) {
            $key = $progId ? (string) $progId : 'default';
            $first = $programLogs->first();

            $groupedLogs[$key] = $programLogs->map(fn ($l) => [
                'program_type_name' => $l->program?->programType?->name ?? 'Multidisiplin',
                'program_title' => $l->program?->title ?? 'Program KKN',
                'date' => $l->date?->format('Y-m-d'),
                'topic' => $l->topic,
                'discussion_summary' => $l->discussion_summary,
                'dpl_feedback' => $l->dpl_feedback,
                'target_group' => $l->target_group,
                'student_count' => $l->student_count ?? 1,
                'output' => $l->output,
                'status' => $l->status?->value ?? (string) $l->status,
            ])->values()->all();

            if (empty($groupedLogs[$key])) {
                $groupedLogs[$key] = [
                    [
                        'program_type_name' => 'Multidisiplin',
                        'program_title' => 'Program Kerja KKN',
                        'date' => null,
                        'topic' => '-',
                        'discussion_summary' => '-',
                        'dpl_feedback' => '-',
                        'target_group' => '-',
                        'student_count' => 1,
                        'output' => '-',
                        'status' => 'pending',
                    ],
                ];
            }
        }

        $logoPath = public_path('images/logo-undip.png');

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
            'groupedLogs' => $groupedLogs,
            'assets' => [
                'logo_path' => file_exists($logoPath) ? $logoPath : null,
            ],
        ];

        $pdfBinary = $this->pdfKitService->generate('generate-mentoring.js', $payload);

        return $this->pdfKitService->makeResponse($pdfBinary, 'Buku_Pembimbingan_KKN_'.$student->nim.'.pdf', inline: $inline);
    }
}
