<?php

namespace App\Http\Controllers;

use App\Enums\ProgramStatus;
use App\Models\Group;
use App\Services\PdfImageResolver;
use App\Services\PdfKitService;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('viewLpk', $group);

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
        $effectiveStart = $group->effective_start_date;
        $effectiveEnd = $group->effective_end_date;
        if ($effectiveStart && $effectiveEnd) {
            $startDate = Carbon::parse($effectiveStart)->startOfWeek(Carbon::MONDAY);
            $endDate = Carbon::parse($effectiveEnd)->endOfWeek(Carbon::SUNDAY);

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
                    'in_period' => $currentDate->between($effectiveStart, $effectiveEnd),
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

        // Build dynamic legend for calendar codes
        $legend = $approvedParticipants->filter(fn ($p) => ! empty($p->participant_code))
            ->map(fn ($p) => [
                'code' => $p->participant_code,
                'student_name' => $p->student?->name,
                'program_title' => $p->participant_title ?: ($p->program?->title ?? ''),
                'type_label' => match ($p->program?->programType?->code) {
                    'multidisiplin' => 'Multidisiplin',
                    'sosial_kemasyarakatan' => 'Sosial Kemasyarakatan',
                    'lainnya' => 'Lainnya',
                    default => 'Program',
                },
            ])
            ->unique('code')
            ->sortBy('code')
            ->values()
            ->all();

        // Helper to determine Saintek vs Soshum by NIM
        $isSaintek = function ($student) {
            if ($student && method_exists($student, 'isSaintek')) {
                return $student->isSaintek();
            }
            $nim = (string) ($student?->nim ?? '');
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

        $logoPath = public_path('images/logo-undip.png');

        $storyboardScenes = collect(! empty($group->storyboard_scenes) ? $group->storyboard_scenes : Group::defaultStoryboardScenes())
            ->map(function ($s, $idx) {
                $imageUrl = $s['image_url'] ?? '';
                $imgBase64 = null;
                if (! empty($imageUrl)) {
                    try {
                        $imgBase64 = $this->imageResolver->resolve($imageUrl);
                    } catch (\Throwable) {
                        $imgBase64 = null;
                    }
                }

                return [
                    'scene' => 'Scene '.($idx + 1),
                    'image_url' => $imageUrl,
                    'image_base64' => $imgBase64,
                    'visual' => $s['visual'] ?? '',
                ];
            })->values()->all();

        $videoScriptScenes = collect(! empty($group->video_script_scenes) ? $group->video_script_scenes : Group::defaultVideoScriptScenes())
            ->map(function ($s, $idx) {
                return [
                    'scene' => 'Scene '.($idx + 1),
                    'visual' => $s['visual'] ?? '',
                    'audio' => $s['audio'] ?? '',
                    'duration' => $s['duration'] ?? '',
                ];
            })->values()->all();

        // Extract outputs
        $outputs = collect();
        foreach ($approvedParticipants as $participant) {
            if ($participant->outputs->isNotEmpty()) {
                foreach ($participant->outputs as $out) {
                    $outputs->push([
                        'code' => $participant->participant_code ?: 'LUARAN',
                        'program_title' => $participant->participant_title ?: ($participant->program?->title ?? '-'),
                        'student_name' => $participant->student?->name ?? '-',
                        'type' => $out->output_type ?? 'Dokumen / Media',
                        'url' => $out->url ?? '-',
                    ]);
                }
            } elseif ($participant->participant_code) {
                $outputs->push([
                    'code' => $participant->participant_code,
                    'program_title' => $participant->participant_title ?: ($participant->program?->title ?? '-'),
                    'student_name' => $participant->student?->name ?? '-',
                    'type' => $participant->output_target ?: 'Laporan / Luaran Program',
                    'url' => '-',
                ]);
            }
        }
        $outputs = $outputs->sortBy('code')->values();

        // Extract documentation
        $documentation = collect();
        foreach ($approvedParticipants as $participant) {
            if ($participant->documentation_image_path || $participant->documentation_caption) {
                $imgBase64 = null;
                if (! empty($participant->documentation_image_path)) {
                    try {
                        $imgBase64 = $this->imageResolver->resolve($participant->documentation_image_path);
                    } catch (\Throwable) {
                        $imgBase64 = null;
                    }
                }

                $documentation->push([
                    'title' => $participant->participant_title ?: ($participant->program?->title ?? 'Dokumentasi Program'),
                    'student_name' => $participant->student?->name ?? '-',
                    'caption' => $participant->documentation_caption,
                    'image_url' => $participant->documentation_image_path,
                    'image_base64' => $imgBase64,
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
                'program_multidisiplin_text' => $group->program_multidisiplin_text,
                'program_sosmas_text' => $group->program_sosmas_text,
                'program_lainnya_text' => $group->program_lainnya_text,
                'storyboard_text' => $group->storyboard_text,
                'storyboard_scenes' => $storyboardScenes,
                'video_script_text' => $group->video_script_text,
                'video_script_scenes' => $videoScriptScenes,
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
                'is_saintek' => $isSaintek($p->student),
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
                'is_saintek' => $isSaintek($p->student),
            ])->values()->all(),
            'calendar' => $calendar,
            'legend' => $legend,
            'outputs' => $outputs->values()->all(),
            'documentation' => $documentation->values()->all(),
        ];

        $pdfBinary = $this->pdfKitService->generate('generate-lpk.js', $payload);

        return $this->pdfKitService->makeResponse($pdfBinary, 'LPK_KKN_'.$group->name.'.pdf', inline: $inline);
    }
}
