<?php

use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CoworkingSpaceController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TestSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Test session routes (temporary)
Route::get('/test-session', [TestSessionController::class, 'testSession'])->name('test.session');
Route::post('/test-session', [TestSessionController::class, 'testSessionPost'])->name('test.session.post');

// Public city routes
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
Route::get('/cities/search-suggestions', [CityController::class, 'searchSuggestions'])->name('cities.search-suggestions');
Route::get('/cities/{city}', [CityController::class, 'show'])->name('cities.show');

// Calculator routes
Route::get('/calculator', [CalculatorController::class, 'index'])->name('calculator.index');
Route::post('/calculator', [CalculatorController::class, 'calculate'])->name('calculator.calculate');
Route::get('/calculator/compare', [CalculatorController::class, 'compare'])->name('calculator.compare');

// Article routes
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/articles/{article}', [ArticleController::class, 'show'])->name('articles.show');

// Deal routes
Route::get('/deals', [DealController::class, 'index'])->name('deals.index');
Route::get('/deals/{deal}', [DealController::class, 'show'])->name('deals.show');
Route::post('/deals/{deal}/click', [DealController::class, 'trackClick'])->name('deals.click');

// Coworking Spaces routes
Route::get('/coworking-spaces', [CoworkingSpaceController::class, 'index'])->name('coworking-spaces.index');
Route::get('/coworking-spaces/{coworkingSpace}', [CoworkingSpaceController::class, 'show'])->name('coworking-spaces.show');
Route::get('/cities/{city}/coworking-spaces', [CoworkingSpaceController::class, 'byCity'])->name('coworking-spaces.city');

// Newsletter routes with rate limiting
Route::get('/newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
    Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'processUnsubscribe'])->name('newsletter.unsubscribe.process');
});
Route::get('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::get('/newsletter/stats', [NewsletterController::class, 'stats'])->name('newsletter.stats');

// Profile routes
Route::get('/profiles', [ProfileController::class, 'index'])->name('profiles.index');
Route::get('/discover', [ProfileController::class, 'discover'])->name('profiles.discover');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
// Place company route BEFORE the dynamic job route to avoid conflicts
Route::get('/jobs/company/{company}', [JobController::class, 'company'])->name('jobs.company');
Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
Route::get('/companies/{company}', [JobController::class, 'company'])->name('companies.show');

// Authenticated job routes
Route::middleware('auth')->group(function () {
    Route::post('/jobs/{job}/save', [JobController::class, 'toggleSave'])->name('jobs.save');
    // Additional alias to satisfy tests hitting /toggle-save
    Route::post('/jobs/{job}/toggle-save', [JobController::class, 'toggleSave'])->name('jobs.toggle-save');
});
// Apply route without middleware to allow custom error message
Route::post('/jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');
Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

// Public endpoint for favorites count (tests expect this unauthenticated)
Route::get('/favorites/count', [FavoritesController::class, 'getCount'])->name('favorites.count');

// Favorites routes (authenticated only)
Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/{favorite}', [FavoritesController::class, 'destroy'])->name('favorites.destroy');
    Route::patch('/favorites/{favorite}/notes', [FavoritesController::class, 'updateNotes'])->name('favorites.update-notes');
    // Accept PUT as well to satisfy tests using PUT
    Route::put('/favorites/{favorite}/notes', [FavoritesController::class, 'updateNotes']);

    // Profile management routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::delete('/profile/image', [ProfileController::class, 'deleteImage'])->name('profile.delete-image');
});

// Authentication Routes with rate limiting
Route::middleware(['guest', 'throttle:20,1'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Real-time notification routes (for testing Socket.IO)
Route::middleware('auth')->group(function () {
    Route::get('/socket-test', function () {
        return view('socket-test');
    })->name('socket-test');

    Route::post('/notifications/broadcast-all', [NotificationController::class, 'broadcastToAll'])->name('notifications.broadcast-all');
    Route::post('/notifications/broadcast-user/{userId}', [NotificationController::class, 'broadcastToUser'])->name('notifications.broadcast-user');
    Route::post('/notifications/broadcast-current', [NotificationController::class, 'broadcastToCurrentUser'])->name('notifications.broadcast-current');
});

// Admin backup routes (protected by admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('filament.admin.backup.')->group(function () {
    Route::get('/backups', [BackupController::class, 'index'])->name('list');
    Route::post('/backups', [BackupController::class, 'create'])->name('create');
    Route::post('/backups/cleanup', [BackupController::class, 'cleanup'])->name('cleanup');
    Route::get('/backups/{backupDir}/download', [BackupController::class, 'downloadZip'])->name('download');
    Route::get('/backups/{backupDir}/{filename}', [BackupController::class, 'download'])->name('download.file');
    Route::delete('/backups/{backupDir}', [BackupController::class, 'destroy'])->name('delete');
});
