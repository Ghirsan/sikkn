<?php

namespace App\Http\Controllers;

use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Services\PdfImageResolver;
use App\Services\PdfKitService;
use Carbon\Carbon;
use Illuminate\Http\Response;

class LrkPdfController extends Controller
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

        // Build approved participants
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

        // Helper to determine Saintek vs Soshum by NIM
        $isSaintek = function ($nim) {
            $nim = (string) $nim;
            if (str_starts_with($nim, '4003') || str_starts_with($nim, '4004')) {
                return true;
            }
            if (str_starts_with($nim, '4001')) {
                return false;
            }
            if (str_starts_with($nim, '2')) {
                return true;
            }

            return false;
        };

        // Resolving images if present
        $logoPath = public_path('images/logo-undip.png');
        $surveyImgB64 = $group->survey_document_path ? $this->imageResolver->resolve($group->survey_document_path) : null;
        $mapImgB64 = $group->location_map_path ? $this->imageResolver->resolve($group->location_map_path) : null;

        $payload = [
            'printed_at' => now()->locale('id')->isoFormat('D MMMM Y'),
            'assets' => [
                'logo_path' => file_exists($logoPath) ? $logoPath : null,
                'survey_image_base64' => $surveyImgB64,
                'map_image_base64' => $mapImgB64,
            ],
            'group' => [
                'name' => $group->name,
                'village' => $group->village,
                'district' => $group->district,
                'regency' => $group->regency,
                'partner_name' => $group->partner_name,
                'village_head' => $group->village_head,
                'background' => $group->background,
                'program_multidisiplin_text' => $group->program_multidisiplin_text,
                'program_sosmas_text' => $group->program_sosmas_text,
                'program_lainnya_text' => $group->program_lainnya_text,
                'storyboard_text' => $group->storyboard_text,
                'video_script_text' => $group->video_script_text,
                'survey_documentation_text' => $group->survey_documentation_text,
                'location_map_text' => $group->location_map_text,
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
                'problem_potential' => $p->problem_potential,
                'method' => $p->method,
                'target_audience' => $p->target_audience,
                'output_target' => $p->output_target,
                'role_in_program' => $p->role_in_program,
                'responsibility' => $p->responsibility,
                'sdg_category' => $p->sdgCategory?->label(),
                'participant_code' => $p->participant_code,
            ])->values()->all(),
            'sosialKemasyarakatan' => $sosialKemasyarakatan->map(fn ($p) => [
                'is_saintek' => $isSaintek($p->student?->nim),
                'program_title' => $p->program?->title,
                'student_name' => $p->student?->name,
                'student_nim' => $p->student?->nim,
                'student_fakultas' => $p->student?->faculty?->name,
                'student_prodi' => $p->student?->studyProgram?->name,
                'role_in_program' => $p->role_in_program,
                'responsibility' => $p->responsibility,
                'sdg_category' => $p->sdgCategory?->label(),
                'participant_code' => $p->participant_code,
            ])->values()->all(),
            'lainnya' => $lainnya->map(fn ($p) => [
                'program_title' => $p->program?->title,
                'student_name' => $p->student?->name,
                'student_nim' => $p->student?->nim,
                'student_fakultas' => $p->student?->faculty?->name,
                'student_prodi' => $p->student?->studyProgram?->name,
                'role_in_program' => $p->role_in_program,
                'responsibility' => $p->responsibility,
                'sdg_category' => $p->sdgCategory?->label(),
                'participant_code' => $p->participant_code,
            ])->values()->all(),
            'scheduleEvents' => $group->scheduleEvents->sortBy('date')->map(fn ($e) => [
                'title' => $e->title,
                'week_number' => $e->week_number,
            ])->values()->all(),
            'calendar' => $calendar,
        ];

        $pdfBinary = $this->pdfKitService->generate('generate-lrk.js', $payload);

        return $this->pdfKitService->makeResponse($pdfBinary, 'LRK_KKN_'.$group->name.'.pdf', inline: $inline);
    }
}
