<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LpkPdfController extends Controller
{
    public function download(Group $group)
    {
        $group->load([
            'period',
            'dpls',
            'students',
            'programs.participants.student',
            'scheduleEvents',
        ]);

        $period = $group->period;

        // Build calendar
        $calendar = [];
        if ($period && $period->start_date && $period->end_date) {
            $startDate = $period->start_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $endDate = $period->end_date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

            $eventsByDate = $group->scheduleEvents->groupBy(function($e) {
                return $e->date->format('Y-m-d');
            });

            $programsByDate = [];
            foreach ($group->programs as $program) {
                foreach ($program->participants as $participant) {
                    if ($participant->status === \App\Enums\ProgramStatus::Approved && $participant->execution_date) {
                        $dateStr = $participant->execution_date->format('Y-m-d');
                        if (!isset($programsByDate[$dateStr])) {
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
                    'date' => $currentDate->copy(),
                    'day' => $currentDate->day,
                    'in_period' => $currentDate->between($period->start_date, $period->end_date),
                    'events' => $eventsByDate[$dateStr] ?? collect(),
                    'programs' => $programsByDate[$dateStr] ?? [],
                ];
                $currentDate = $currentDate->addDay();
            }
        }

        // Build approved participants with program relation set
        $approvedParticipants = collect();
        foreach ($group->programs as $program) {
            foreach ($program->participants as $participant) {
                if ($participant->status === \App\Enums\ProgramStatus::Approved) {
                    $participant->setRelation('program', $program);
                    $approvedParticipants->push($participant);
                }
            }
        }

        $pdf = Pdf::loadView('pdf.lpk', [
            'group' => $group,
            'period' => $period,
            'dpls' => $group->dpls,
            'students' => $group->students,
            'multidisiplin' => $approvedParticipants->filter(fn($p) => $p->program->type === \App\Enums\ProgramType::Multidisiplin),
            'sosialKemasyarakatan' => $approvedParticipants->filter(fn($p) => $p->program->type === \App\Enums\ProgramType::SosialKemasyarakatan),
            'lainnya' => $approvedParticipants->filter(fn($p) => $p->program->type === \App\Enums\ProgramType::Lainnya),
            'scheduleEvents' => $group->scheduleEvents->sortBy('date'),
            'calendar' => $calendar,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption(['dpi' => 96, 'defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

        return $pdf->download('LPK_KKN_'.$group->name.'.pdf');
    }
}
