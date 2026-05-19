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
});

require __DIR__.'/settings.php';
