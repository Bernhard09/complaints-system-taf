<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComplaintController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:USER'])->group(function () {
    Route::get('/complaints', function () {
        return 'USER complaints';
    });

    Route::get('/complaints/create', [ComplaintController::class, 'create'])
        ->name('complaints.create');

    Route::post('/complaints', [ComplaintController::class, 'store'])
        ->name('complaints.store');


});

Route::middleware(['auth', 'role:SUPERVISOR'])->group(function () {
    Route::get('/supervisor/complaints', function () {
        return 'SUPERVISOR complaints';
    });
});

Route::middleware(['auth', 'role:AGENT'])->group(function () {
    Route::get('/agent/complaints', function () {
        return 'AGENT complaints';
    });
});


require __DIR__.'/auth.php';
