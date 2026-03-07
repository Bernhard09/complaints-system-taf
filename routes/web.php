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
use App\Http\Controllers\NotificationController;

/* Models */
use App\Models\Complaint;
use App\Models\ComplaintAttachment;


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

Route::get('/test-lang', function () {
    app()->setLocale('id');
    return __('Workspace') . ' | ' . app()->getLocale() . ' | ' . base_path('lang/id.json') . ' | ' . (file_exists(base_path('lang/id.json')) ? 'EXISTS' : 'MISSING');
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

    // Language Switch
    Route::get('/lang/{locale}', function (string $locale) {
        if (!in_array($locale, ['en', 'id'])) {
            abort(400);
        }

        session()->put('locale', $locale);
        session()->save(); // Force save to see if it fixes
        
        \Log::info('Language switched to: ' . $locale . ' for user ' . auth()->id());
        
        return back();
    })->name('lang.switch');

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
        ->name('complaints.messages.agent');


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

    // Notifications
    Route::get('/inbox', [NotificationController::class, 'index'])->name('notifications.inbox');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'delete'])->name('notifications.delete');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::delete('/notifications', [NotificationController::class, 'clearAll'])->name('notifications.clearAll');

    // Chat polling
    Route::get('/complaints/{complaint}/messages/poll', [ComplaintMessageController::class, 'poll'])->name('complaints.messages.poll');

    // Dashboard polling (per-role)
    Route::get('/api/poll/user-dashboard', [DashboardController::class, 'pollUser'])->name('poll.user.dashboard');
    Route::get('/api/poll/agent-dashboard', [DashboardController::class, 'pollAgent'])->name('poll.agent.dashboard');
    Route::get('/api/poll/supervisor-dashboard', [DashboardController::class, 'pollSupervisor'])->name('poll.supervisor.dashboard');

    // Complaint status polling
    Route::get('/api/poll/complaint/{complaint}/status', [DashboardController::class, 'pollComplaintStatus'])->name('poll.complaint.status');
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

    // User complaints list
    Route::get('/user/complaints', [DashboardController::class, 'complaints'])
        ->name('user.complaints');

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

    // reject resolution
    Route::post('/complaints/{complaint}/reject-resolution', [ComplaintController::class, 'rejectResolution']
        )->name('complaints.reject');

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
        Route::get('/dashboardtemp', [SupervisorComplaintController::class, 'index'])
            ->name('supervisor.dashboard.temp');

        Route::get('/history', [SupervisorComplaintController::class, 'history'])
            ->name('supervisor.history');

        Route::get('/sla', [SupervisorComplaintController::class, 'sla'])
            ->name('supervisor.sla');

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


    Route::get('/dashboard', [AgentComplaintController::class, 'index'])
        ->name('agent.dashboard');

        // Route::get('/agent/dashboard', [AgentComplaintController::class,'index'])
        // ->name('agent.dashboard');

    Route::get('/assigned', [AgentComplaintController::class,'assigned'])
        ->name('agent.assigned');

    Route::get('/history', [AgentComplaintController::class,'history'])
        ->name('agent.history');

    Route::get('/sla', [AgentComplaintController::class,'sla'])
        ->name('agent.sla');


    // Route::get('/complaints', [AgentComplaintController::class, 'index'])
    //     ->name('agent.complaints.index');

    Route::post('/complaints/{complaint}/waiting', [AgentComplaintController::class, 'markWaiting'])
        ->name('agent.complaints.waiting');

    // Request user confirmation
    Route::post('/complaints/{complaint}/request-confirmation',[AgentComplaintController::class, 'requestConfirmation'])
        ->name('agent.complaints.requestConfirmation');

    // Close complaint
    Route::post('/complaints/{complaint}/close',[AgentComplaintController::class, 'close'])
        ->name('agent.complaints.close');

    // Reassign confirmation
    Route::post('/reassign/{assignment}/confirm', [AgentComplaintController::class, 'confirmReassign'])
        ->name('agent.reassign.confirm');

    Route::post('/reassign/{assignment}/reject', [AgentComplaintController::class, 'rejectReassign'])
        ->name('agent.reassign.reject');

});

/*
|--------------------------------------------------------------------------
| Auth Routes (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
