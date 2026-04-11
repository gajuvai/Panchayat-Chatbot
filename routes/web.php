<?php

use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminComplaintCategoryController;
use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminPollController;
use App\Http\Controllers\Admin\AdminRuleBookController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AdminMaintenanceController;
use App\Http\Controllers\Resident\ComplaintController;
use App\Http\Controllers\Resident\MaintenanceController;
use App\Http\Controllers\Resident\ComplaintMediaController;
use App\Http\Controllers\Resident\ComplaintUpvoteController;
use App\Http\Controllers\Resident\ResidentDashboardController;
use App\Http\Controllers\Resident\VisitorPassController;
use App\Http\Controllers\Security\EmergencyAlertController;
use App\Http\Controllers\Security\PatrolAssignmentController;
use App\Http\Controllers\Security\SecurityDashboardController;
use App\Http\Controllers\Security\SecurityIncidentController;
use App\Http\Controllers\Security\VisitorGateController;
use App\Http\Controllers\Shared\AnnouncementController;
use App\Http\Controllers\Shared\ChatController;
use App\Http\Controllers\Shared\EventController;
use App\Http\Controllers\Shared\FeedbackController;
use App\Http\Controllers\Shared\ForumController;
use App\Http\Controllers\Shared\ForumReplyController;
use App\Http\Controllers\Shared\NotificationController;
use App\Http\Controllers\Shared\PollController;
use App\Http\Controllers\Shared\LostFoundController;
use App\Http\Controllers\Shared\ResidentDirectoryController;
use App\Http\Controllers\Shared\RuleBookController;
use Illuminate\Support\Facades\Route;

// Generic dashboard alias — required by Breeze auth controllers (email verify, confirm password, etc.)
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->getDashboardRoute());
})->middleware('auth')->name('dashboard');

// Public routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->getDashboardRoute());
    }
    return view('welcome');
})->name('home');

Route::get('/rules', [RuleBookController::class, 'index'])->name('rules.index');
Route::get('/rules/{ruleBookSection}', [RuleBookController::class, 'show'])->name('rules.show');

// ─── RESIDENT ROUTES ───────────────────────────────────────────────
Route::middleware(['auth', 'role:resident'])->prefix('resident')->name('resident.')->group(function () {
    Route::get('/dashboard', [ResidentDashboardController::class, 'index'])->name('dashboard');
    Route::resource('complaints', ComplaintController::class);
    Route::post('complaints/{complaint}/upvote', [ComplaintUpvoteController::class, 'toggle'])->name('complaints.upvote');
    Route::post('complaints/{complaint}/media', [ComplaintMediaController::class, 'store'])->name('complaints.media.store');
    Route::delete('complaints/media/{media}', [ComplaintMediaController::class, 'destroy'])->name('complaints.media.destroy');
    Route::post('feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('visitor-passes', [VisitorPassController::class, 'index'])->name('visitor-passes.index');
    Route::post('visitor-passes', [VisitorPassController::class, 'store'])->name('visitor-passes.store');
    Route::patch('visitor-passes/{visitorPass}/cancel', [VisitorPassController::class, 'destroy'])->name('visitor-passes.cancel');
});

// ─── ADMIN ROUTES ──────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('complaints', [AdminComplaintController::class, 'index'])->name('complaints.index');
    Route::get('complaints/{complaint}', [AdminComplaintController::class, 'show'])->name('complaints.show');
    Route::patch('complaints/{complaint}/assign', [AdminComplaintController::class, 'assign'])->name('complaints.assign');
    Route::patch('complaints/{complaint}/status', [AdminComplaintController::class, 'updateStatus'])->name('complaints.status');

    Route::resource('categories', AdminComplaintCategoryController::class)->except(['show']);

    Route::resource('announcements', AdminAnnouncementController::class);
    Route::patch('announcements/{announcement}/publish', [AdminAnnouncementController::class, 'publish'])->name('announcements.publish');

    Route::resource('events', AdminEventController::class);
    Route::resource('polls', AdminPollController::class);
    Route::resource('users', AdminUserController::class)->only(['index', 'show', 'edit', 'update']);
    Route::resource('rules', AdminRuleBookController::class);

    Route::get('maintenance', [AdminMaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('maintenance/{maintenance}', [AdminMaintenanceController::class, 'show'])->name('maintenance.show');
    Route::patch('maintenance/{maintenance}/status', [AdminMaintenanceController::class, 'updateStatus'])->name('maintenance.status');
    Route::patch('maintenance/{maintenance}/assign', [AdminMaintenanceController::class, 'assign'])->name('maintenance.assign');
    Route::post('maintenance/{maintenance}/media', [AdminMaintenanceController::class, 'storeMedia'])->name('maintenance.media.store');

    Route::get('analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/data', [AdminAnalyticsController::class, 'complaints'])->name('analytics.data');
    Route::get('analytics/export', [AdminAnalyticsController::class, 'export'])->name('analytics.export');
});

// ─── SECURITY HEAD ROUTES ──────────────────────────────────────────
Route::middleware(['auth', 'role:security_head'])->prefix('security')->name('security.')->group(function () {
    Route::get('/dashboard', [SecurityDashboardController::class, 'index'])->name('dashboard');
    Route::resource('incidents', SecurityIncidentController::class);
    Route::resource('patrols', PatrolAssignmentController::class);
    Route::get('visitors', [VisitorGateController::class, 'index'])->name('visitors.index');
    Route::patch('visitors/{visitorPass}/approve',   [VisitorGateController::class, 'approve'])->name('visitors.approve');
    Route::patch('visitors/{visitorPass}/check-in',  [VisitorGateController::class, 'checkIn'])->name('visitors.check-in');
    Route::patch('visitors/{visitorPass}/check-out', [VisitorGateController::class, 'checkOut'])->name('visitors.check-out');
    Route::get('alerts', [EmergencyAlertController::class, 'index'])->name('alerts.index');
    Route::post('alerts', [EmergencyAlertController::class, 'store'])->name('alerts.store');
    Route::get('alerts/{alert}', [EmergencyAlertController::class, 'show'])->name('alerts.show');
    Route::patch('alerts/{alert}/resolve', [EmergencyAlertController::class, 'resolve'])->name('alerts.resolve');
});

// ─── SHARED AUTHENTICATED ROUTES ──────────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/session', [ChatController::class, 'session'])->name('chat.session');
    Route::post('/chat/message', [ChatController::class, 'sendMessage'])->name('chat.message');
    Route::get('/chat/history', [ChatController::class, 'getHistory'])->name('chat.history');

    Route::resource('forum', ForumController::class);
    Route::resource('forum.replies', ForumReplyController::class)->shallow();
    Route::post('forum/{thread}/replies/{reply}/upvote', [ForumReplyController::class, 'upvote'])->name('forum.replies.upvote');

    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::post('events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');

    Route::get('polls', [PollController::class, 'index'])->name('polls.index');
    Route::get('polls/{poll}', [PollController::class, 'show'])->name('polls.show');
    Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');

    Route::get('directory', [ResidentDirectoryController::class, 'index'])->name('directory.index');
    Route::patch('directory/settings', [ResidentDirectoryController::class, 'update'])->name('directory.update');

    Route::resource('lost-found', LostFoundController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['lost-found' => 'lostFound']);
    Route::patch('lost-found/{lostFound}/resolve', [LostFoundController::class, 'resolve'])->name('lost-found.resolve');

    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// ─── Catch-all fallback → 404 ─────────────────────────────────────────────────
Route::fallback(function (\Illuminate\Http\Request $request) {
    if ($request->expectsJson()) {
        return response()->json(['message' => 'The requested URL was not found.'], 404);
    }
    return response()->view('errors.404', [], 404);
});
