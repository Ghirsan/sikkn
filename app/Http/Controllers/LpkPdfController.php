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
            'programs.student',
            'programs.lpk',
        ]);

        $pdf = Pdf::loadView('pdf.lpk', [
            'group' => $group,
            'period' => $group->period,
            'dpls' => $group->dpls,
            'students' => $group->students,
            'programs' => $group->programs,
        ]);

        return $pdf->download('LPK_KKN_'.$group->name.'.pdf');
    }
}
