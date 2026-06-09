<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class LrkPdfController extends Controller
{
    public function download(Group $group)
    {
        $group->load([
            'period',
            'dpls',
            'students',
            'programs.student',
            'programs.dates',
            'scheduleEvents',
            'surveyDocuments',
        ]);

        $programs = $group->programs->where('status', \App\Enums\ProgramStatus::Approved);
        $period = $group->period;

        $calendar = [];
        if ($period && $period->start_date && $period->end_date) {
            $startDate = $period->start_date->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            $endDate = $period->end_date->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

            $eventsByDate = $group->scheduleEvents->groupBy(function($e) {
                return $e->date->format('Y-m-d');
            });

            $programsByDate = [];
            foreach ($programs as $program) {
                foreach ($program->dates as $pDate) {
                    $dateStr = $pDate->date->format('Y-m-d');
                    if (!isset($programsByDate[$dateStr])) {
                        $programsByDate[$dateStr] = [];
                    }
                    $programsByDate[$dateStr][] = $program;
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

        $pdf = Pdf::loadView('pdf.lrk', [
            'group' => $group,
            'period' => $period,
            'dpls' => $group->dpls,
            'students' => $group->students,
            'multidisiplin' => $programs->where('type', \App\Enums\ProgramType::Multidisiplin),
            'sosialKemasyarakatan' => $programs->where('type', \App\Enums\ProgramType::SosialKemasyarakatan),
            'lainnya' => $programs->where('type', \App\Enums\ProgramType::Lainnya),
            'scheduleEvents' => $group->scheduleEvents->sortBy('date'),
            'surveyDocuments' => $group->surveyDocuments->sortBy('sort_order'),
            'calendar' => $calendar,
        ]);

        return $pdf->download('LRK_KKN_'.$group->name.'.pdf');
    }
}
