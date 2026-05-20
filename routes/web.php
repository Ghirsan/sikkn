<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // P2KKN Admin Routes
    Route::middleware('role:p2kkn')->prefix('admin')->group(function () {
        Route::view('periods', 'admin.periods.index')->name('admin.periods.index');
        Route::view('groups', 'admin.groups.index')->name('admin.groups.index');
        Route::view('students', 'admin.students.index')->name('admin.students.index');
        Route::view('dpl', 'admin.dpl.index')->name('admin.dpl.index');
        Route::view('documents', 'admin.documents.index')->name('admin.documents.index');
    });

    // DPL Routes
    Route::middleware('role:dpl')->prefix('dpl')->group(function () {
        Route::view('groups', 'dpl.groups.index')->name('dpl.groups.index');
        Route::view('programs', 'dpl.programs.index')->name('dpl.programs.index');
        Route::view('documents', 'dpl.documents.index')->name('dpl.documents.index');
        Route::view('daily-logs', 'dpl.daily-logs.index')->name('dpl.daily-logs.index');
        Route::view('mentoring', 'dpl.mentoring.index')->name('dpl.mentoring.index');
        Route::view('grades', 'dpl.grades.index')->name('dpl.grades.index');
    });

    // Mahasiswa Routes
    Route::middleware('role:mahasiswa')->group(function () {
        Route::view('programs', 'mahasiswa.programs.index')->name('programs.index');
        Route::view('documents', 'mahasiswa.documents.index')->name('documents.index');
        Route::view('daily-logs', 'mahasiswa.daily-logs.index')->name('daily-logs.index');
        Route::view('mentoring-logs', 'mahasiswa.mentoring-logs.index')->name('mentoring-logs.index');
        Route::view('groups', 'mahasiswa.groups.index')->name('groups.index');
    });

    // Prodi Routes
    Route::middleware('role:prodi')->prefix('prodi')->group(function () {
        Route::view('students', 'prodi.students.index')->name('prodi.students.index');
        Route::view('programs', 'prodi.programs.index')->name('prodi.programs.index');
    });
});

require __DIR__.'/settings.php';
