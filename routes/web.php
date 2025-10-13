<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CalculatorController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\FavoritesController;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Public city routes
Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
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

// Newsletter routes
Route::get('/newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'processUnsubscribe'])->name('newsletter.unsubscribe.process');
Route::get('/newsletter/stats', [NewsletterController::class, 'stats'])->name('newsletter.stats');

// Favorites routes (authenticated only)
Route::middleware('auth')->group(function () {
    Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle', [FavoritesController::class, 'toggle'])->name('favorites.toggle');
    Route::delete('/favorites/{favorite}', [FavoritesController::class, 'destroy'])->name('favorites.destroy');
    Route::patch('/favorites/{favorite}/notes', [FavoritesController::class, 'updateNotes'])->name('favorites.update-notes');
    Route::get('/favorites/count', [FavoritesController::class, 'getCount'])->name('favorites.count');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
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
