<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SupervisorComplaintController;
use App\Http\Controllers\AgentComplaintController;

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

Route::middleware(['auth', 'role:SUPERVISOR'])->prefix('supervisor')->group(function () {
    Route::get('/complaints', [SupervisorComplaintController::class, 'index'])
        ->name('supervisor.complaints.index');

    // assign complaint ke department
    Route::post('/complaints/{complaint}/assign', [SupervisorComplaintController::class, 'assign'])->name('supervisor.complaints.assign');

    // assign complaint ke agent
    Route::post('/complaints/{complaint}/assign-agent', [SupervisorComplaintController::class, 'assignAgent'])->name('supervisor.complaints.assignAgent');
});

Route::middleware(['auth', 'role:AGENT'])->prefix('agent')->group(function () {
    Route::get('/complaints', [AgentComplaintController::class, 'index'])
        ->name('agent.complaints.index');
});


require __DIR__.'/auth.php';
