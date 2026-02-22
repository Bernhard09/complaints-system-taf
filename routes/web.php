<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/* Controllers */
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\SupervisorComplaintController;
use App\Http\Controllers\AgentComplaintController;
use App\Http\Controllers\ComplaintMessageController;
use App\Http\Controllers\ComplaintInternalNoteController;
use App\Http\Controllers\DashboardController;

/* Models */
use App\Models\Complaint;
use App\Models\ComplaintAttachment;


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    //return view('dashboard');

    $user = Auth::user();

    return match ($user->role) {
        'USER' => redirect()->route('user.dashboard'),
        'SUPERVISOR' => redirect()->route('supervisor.dashboard'),
        'AGENT' => redirect()->route('agent.dashboard'),
        default => view('dashboard'),
    };

})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated (ALL ROLES)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])
        ->whereNumber('complaint')
        ->name('complaints.show');

    // User sends message
    Route::post(
        '/complaints/{complaint}/messages/user',
        [ComplaintMessageController::class, 'storeUser']
    )->name('complaints.messages.user');

    // Agent sends message
    Route::post(
        '/agent/complaints/{complaint}/messages',
        [ComplaintMessageController::class, 'storeAgent']
    )->middleware('role:AGENT')
        ->name('agent.complaints.messages');


    Route::post(
        '/complaints/{complaint}/internal-notes',
        [ComplaintInternalNoteController::class, 'storeAgent']
    )->middleware('role:AGENT,SUPERVISOR')
    ->name('complaints.internal-notes.store');

    Route::get('/attachments/{attachment}/download', function ($attachment) {

        $attachment = ComplaintAttachment::findOrFail($attachment);

        return response()->download(
            storage_path('app/public/'.$attachment->file_path),
            $attachment->original_name
        );

    })->name('attachments.download');
});

/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:USER'])->group(function () {


    // User dashboard
    Route::get('/user/dashboard', [DashboardController::class, 'user'])
        ->name('user.dashboard');

    // Route::get('/complaints', function () {
    //     return 'USER complaints';
    // });

    // IMPORTANT: create BEFORE {complaint}
    Route::get('/complaints/create', [ComplaintController::class, 'create'])
        ->name('complaints.create');

    Route::post('/complaints', [ComplaintController::class, 'store'])
        ->name('complaints.store');

    // confirm resolution
    Route::post('/complaints/{complaint}/confirm', [ComplaintController::class, 'confirmResolution']
        )->name('complaints.confirm');

    // cancel complaint
    Route::post('/complaints/{complaint}/cancel', [ComplaintController::class, 'cancel']
        )->name('complaints.cancel');

});

/*
|--------------------------------------------------------------------------
| SUPERVISOR
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:SUPERVISOR'])->prefix('supervisor')
    ->group(function () {

        Route::get('/dashboard', [SupervisorComplaintController::class, 'index'])
            ->name('supervisor.dashboard');

        Route::get('/complaints/{complaint}', [SupervisorComplaintController::class, 'show']
        )->name('supervisor.complaints.show');


        Route::post(
            '/complaints/{complaint}/assign',
            [SupervisorComplaintController::class, 'assign']
        )->name('supervisor.complaints.assign');

        Route::post(
            '/complaints/{complaint}/assign-agent',
            [SupervisorComplaintController::class, 'assignAgent']
        )->name('supervisor.complaints.assignAgent');

        Route::post(
            '/complaints/{complaint}/reassign',
            [SupervisorComplaintController::class, 'reassign']
        )->name('supervisor.complaints.reassign');

        Route::post(
            '/complaints/{complaint}/reopen',
            [SupervisorComplaintController::class, 'reopen']
        )->name('supervisor.complaints.reopen');
});

/*
|--------------------------------------------------------------------------
| AGENT
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:AGENT'])->prefix('agent')
    ->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'agent'])
        ->name('agent.dashboard');


    Route::get('/complaints', [AgentComplaintController::class, 'index'])
        ->name('agent.complaints.index');

    Route::post('/complaints/{complaint}/waiting', [AgentComplaintController::class, 'markWaiting'])
        ->name('agent.complaints.waiting');

    // Request user confirmation
    Route::post('/complaints/{complaint}/request-confirmation',[AgentComplaintController::class, 'requestConfirmation'])
        ->name('agent.complaints.requestConfirmation');

    // Close complaint
    Route::post('/complaints/{complaint}/close',[AgentComplaintController::class, 'close'])
        ->name('agent.complaints.close');

});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
