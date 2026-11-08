<?php

namespace App\Http\Controllers;

use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Services\PdfImageResolver;
use App\Services\PdfKitService;
use Carbon\Carbon;
use Illuminate\Http\Response;

class LpkPdfController extends Controller
{
    public function __construct(
        protected PdfKitService $pdfKitService,
        protected PdfImageResolver $imageResolver
    ) {}

    public function preview(Group $group): Response
    {
        return $this->download($group, inline: true);
    }

    public function download(Group $group, ?bool $inline = null): Response
    {
        $inline = $inline ?? ! request()->boolean('download');

        $group->load([
            'period',
            'dpls',
            'students.studyProgram',
            'students.faculty',
            'programs.programType',
            'programs.participants.student.studyProgram',
            'programs.participants.student.faculty',
            'programs.participants.outputs',
            'programs.participants.sdgCategory',
            'scheduleEvents',
        ]);

        $period = $group->period;

        // Build calendar structure
        $calendar = [];
        if ($period && $period->start_date && $period->end_date) {
            $startDate = $period->start_date->copy()->startOfWeek(Carbon::MONDAY);
            $endDate = $period->end_date->copy()->endOfWeek(Carbon::SUNDAY);

            $eventsByDate = $group->scheduleEvents->groupBy(function ($e) {
                return $e->date->format('Y-m-d');
            });

            $programsByDate = [];
            foreach ($group->programs as $program) {
                foreach ($program->participants as $participant) {
                    if ($participant->status === ProgramStatus::Approved && $participant->execution_date) {
                        $dateStr = $participant->execution_date->format('Y-m-d');
                        if (! isset($programsByDate[$dateStr])) {
                            $programsByDate[$dateStr] = [];
                        }
                        $participant->setRelation('program', $program);
                        $programsByDate[$dateStr][] = $participant;
                    }
                }
            }

            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $week = $currentDate->isoWeek;
                $dateStr = $currentDate->format('Y-m-d');

                $calendar[$week][] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'day' => $currentDate->day,
                    'in_period' => $currentDate->between($period->start_date, $period->end_date),
                    'events' => ($eventsByDate[$dateStr] ?? collect())->map(fn ($e) => ['title' => $e->title])->values()->all(),
                    'programs' => collect($programsByDate[$dateStr] ?? [])->map(fn ($p) => [
                        'code' => $p->participant_code ?? '',
                        'title' => $p->participant_title ?: ($p->program?->title ?? ''),
                    ])->values()->all(),
                ];
                $currentDate = $currentDate->addDay();
            }
        }

        // Build approved participants with program relation set
        $approvedParticipants = collect();
        foreach ($group->programs as $program) {
            foreach ($program->participants as $participant) {
                if ($participant->status === ProgramStatus::Approved) {
                    $participant->setRelation('program', $program);
                    $approvedParticipants->push($participant);
                }
            }
        }

        $multidisiplin = $approvedParticipants->filter(fn ($p) => $p->program->programType?->code === 'multidisiplin')->values();
        $sosialKemasyarakatan = $approvedParticipants->filter(fn ($p) => $p->program->programType?->code === 'sosial_kemasyarakatan')->values();
        $lainnya = $approvedParticipants->filter(fn ($p) => $p->program->programType?->code === 'lainnya')->values();

        $logoPath = public_path('images/logo-undip.png');

        // Extract outputs
        $outputs = collect();
        foreach ($approvedParticipants as $participant) {
            foreach ($participant->outputs as $out) {
                $outputs->push([
                    'code' => $participant->participant_code ?: 'LUARAN',
                    'program_title' => $participant->participant_title ?: ($participant->program?->title ?? '-'),
                    'student_name' => $participant->student?->name ?? '-',
                    'type' => $out->output_type ?? 'Dokumen / Media',
                    'url' => $out->url ?? '-',
                ]);
            }
        }

        // Extract documentation
        $documentation = collect();
        foreach ($approvedParticipants as $participant) {
            if ($participant->documentation_image_path || $participant->documentation_caption) {
                $documentation->push([
                    'title' => $participant->participant_title ?: ($participant->program?->title ?? 'Dokumentasi Program'),
                    'student_name' => $participant->student?->name ?? '-',
                    'caption' => $participant->documentation_caption,
                    'image_url' => $participant->documentation_image_path,
                ]);
            }
        }

        $payload = [
            'printed_at' => now()->locale('id')->isoFormat('D MMMM Y'),
            'assets' => [
                'logo_path' => file_exists($logoPath) ? $logoPath : null,
            ],
            'group' => [
                'name' => $group->name,
                'village' => $group->village,
                'district' => $group->district,
                'regency' => $group->regency,
                'partner_name' => $group->partner_name,
                'village_head' => $group->village_head,
                'background' => $group->background,
                'lpk_background' => $group->lpk_background ?? $group->background,
            ],
            'period' => [
                'semester' => $period?->semester?->value,
                'year' => $period?->year,
            ],
            'dpls' => $group->dpls->map(fn ($d) => [
                'name' => $d->name,
                'nip' => $d->nip,
            ])->values()->all(),
            'students' => $group->students->map(fn ($s) => [
                'name' => $s->name,
                'nim' => $s->nim,
                'prodi' => $s->studyProgram?->name,
                'fakultas' => $s->faculty?->name,
            ])->values()->all(),
            'multidisiplin' => $multidisiplin->map(fn ($p) => [
                'sequence' => $p->program?->sequence ?? 1,
                'is_video_profile' => $p->program ? $p->program->isVideoProfile() : false,
                'program_title' => $p->program?->title,
                'participant_title' => $p->participant_title,
                'student_name' => $p->student?->name,
                'student_nim' => $p->student?->nim,
                'student_fakultas' => $p->student?->faculty?->name,
                'student_prodi' => $p->student?->studyProgram?->name,
                'execution_date' => $p->execution_date?->format('d/m/Y'),
                'achievement' => $p->achievement,
                'obstacle' => $p->obstacle,
                'solution' => $p->solution,
                'role' => $p->role_in_program,
                'description' => $p->responsibility,
                'output_code' => $p->participant_code,
            ])->values()->all(),
            'sosialKemasyarakatan' => $sosialKemasyarakatan->map(fn ($p) => [
                'program_id' => $p->program_id,
                'program_title' => $p->program?->title,
                'participant_title' => $p->participant_title,
                'student_name' => $p->student?->name,
                'student_nim' => $p->student?->nim,
                'student_fakultas' => $p->student?->faculty?->name,
                'student_prodi' => $p->student?->studyProgram?->name,
                'execution_date' => $p->execution_date?->format('d/m/Y'),
                'role' => $p->role_in_program,
                'description' => $p->responsibility,
                'achievement' => $p->achievement,
                'output_code' => $p->participant_code,
                'is_initiator' => ($p->program?->student_id === $p->student_id),
            ])->values()->all(),
            'lainnya' => $lainnya->map(fn ($p) => [
                'program_id' => $p->program_id,
                'program_title' => $p->program?->title,
                'participant_title' => $p->participant_title,
                'student_name' => $p->student?->name,
                'student_nim' => $p->student?->nim,
                'student_fakultas' => $p->student?->faculty?->name,
                'student_prodi' => $p->student?->studyProgram?->name,
                'execution_date' => $p->execution_date?->format('d/m/Y'),
                'role' => $p->role_in_program,
                'description' => $p->responsibility,
                'achievement' => $p->achievement,
                'output_code' => $p->participant_code,
                'is_initiator' => ($p->program?->student_id === $p->student_id),
            ])->values()->all(),
            'calendar' => $calendar,
            'outputs' => $outputs->values()->all(),
            'documentation' => $documentation->values()->all(),
        ];

        $pdfBinary = $this->pdfKitService->generate('generate-lpk.js', $payload);

        return $this->pdfKitService->makeResponse($pdfBinary, 'LPK_KKN_'.$group->name.'.pdf', inline: $inline);
    }
}
