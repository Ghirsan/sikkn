<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MentoringLogsPdfController extends Controller
{
    public function download(User $student)
    {
        // Placeholder for PDF generation
        return response('Fitur Cetak PDF Buku Pembimbingan belum diimplementasikan.', 200)
            ->header('Content-Type', 'text/plain');
    }
}
