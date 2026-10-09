<?php

use App\Http\Controllers\LogbookPdfController;
use App\Http\Controllers\LpkPdfController;
use App\Http\Controllers\LrkPdfController;
use App\Http\Controllers\MentoringLogsPdfController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // LRK & LPK Print Page (accessible by all authenticated roles)
    Route::get('lrk/{group}/pdf', [LrkPdfController::class, 'download'])->name('lrk.pdf');
    Route::get('lpk/{group}/pdf', [LpkPdfController::class, 'download'])->name('lpk.pdf');
    Route::get('logbook/{student}/pdf', [LogbookPdfController::class, 'download'])->name('logbook.pdf');
    Route::get('mentoring-logs/{student}/pdf', [MentoringLogsPdfController::class, 'download'])->name('mentoring-logs.pdf');

    // P2KKN Admin Routes
    Route::middleware('role:p2kkn')->prefix('admin')->group(function () {
        Route::view('periods', 'admin.periods.index')->name('admin.periods.index');
        Route::view('groups', 'admin.groups.index')->name('admin.groups.index');
        Route::view('students', 'admin.students.index')->name('admin.students.index');
        Route::view('dpl', 'admin.dpl.index')->name('admin.dpl.index');
        Route::view('documents', 'admin.documents.index')->name('admin.documents.index');

        // Master Data
        Route::view('faculties', 'admin.faculties.index')->name('admin.faculties.index');
        Route::view('study-programs', 'admin.study-programs.index')->name('admin.study-programs.index');
        Route::view('group-types', 'admin.group-types.index')->name('admin.group-types.index');
        Route::view('program-types', 'admin.program-types.index')->name('admin.program-types.index');
        Route::view('sdg-categories', 'admin.sdg-categories.index')->name('admin.sdg-categories.index');

        // System
        Route::view('users', 'admin.users.index')->name('admin.users.index');
    });

    // DPL Routes
    Route::middleware('role:dpl')->prefix('dpl')->group(function () {
        Route::view('groups', 'dpl.groups.index')->name('dpl.groups.index');
        Route::view('programs', 'dpl.programs.index')->name('dpl.programs.index');
        Route::view('documents', 'dpl.documents.index')->name('dpl.documents.index');
        Route::view('logbook', 'dpl.logbook.index')->name('dpl.logbook.index');
        Route::view('mentoring', 'dpl.mentoring.index')->name('dpl.mentoring.index');
        Route::view('grades', 'dpl.grades.index')->name('dpl.grades.index');
        Route::view('grades/{student}/form', 'dpl.grades.form')->name('dpl.grades.form');
    });

    // Mahasiswa Routes
    Route::middleware('role:mahasiswa')->group(function () {
        Route::view('programs', 'mahasiswa.programs.index')->name('programs.index');
        Route::view('programs/form', 'mahasiswa.programs.form')->name('programs.form');
        Route::view('lrk', 'mahasiswa.lrk.index')->name('lrk.index');
        Route::view('lrk/form', 'mahasiswa.lrk.form')->name('lrk.form');
        Route::view('lpk', 'mahasiswa.lpk.index')->name('lpk.index');
        Route::view('logbook', 'mahasiswa.logbook.index')->name('logbook.index');
        Route::view('logbook/form', 'mahasiswa.logbook.form')->name('logbook.form');
        Route::view('mentoring-logs', 'mahasiswa.mentoring-logs.index')->name('mentoring-logs.index');
        Route::view('mentoring-logs/form', 'mahasiswa.mentoring-logs.form')->name('mentoring-logs.form');
        Route::view('groups', 'mahasiswa.groups.index')->name('groups.index');
    });

    // Prodi Routes
    Route::middleware('role:prodi')->prefix('prodi')->group(function () {
        Route::view('students', 'prodi.students.index')->name('prodi.students.index');
        Route::view('dpls', 'prodi.dpls.index')->name('prodi.dpls.index');
        Route::view('programs', 'prodi.programs.index')->name('prodi.programs.index');
        Route::view('logbooks', 'prodi.logbooks.index')->name('prodi.logbooks.index');
        Route::view('mentoring', 'prodi.mentoring.index')->name('prodi.mentoring.index');
        Route::view('grades', 'prodi.grades.index')->name('prodi.grades.index');
        Route::view('documents', 'prodi.documents.index')->name('prodi.documents.index');
    });

    // Fakultas Routes
    Route::middleware('role:fakultas')->prefix('fakultas')->group(function () {
        Route::view('students', 'fakultas.students.index')->name('fakultas.students.index');
        Route::view('dpls', 'fakultas.dpls.index')->name('fakultas.dpls.index');
        Route::view('programs', 'fakultas.programs.index')->name('fakultas.programs.index');
        Route::view('logbooks', 'fakultas.logbooks.index')->name('fakultas.logbooks.index');
        Route::view('mentoring', 'fakultas.mentoring.index')->name('fakultas.mentoring.index');
        Route::view('grades', 'fakultas.grades.index')->name('fakultas.grades.index');
        Route::view('documents', 'fakultas.documents.index')->name('fakultas.documents.index');
    });
});

require __DIR__.'/settings.php';
