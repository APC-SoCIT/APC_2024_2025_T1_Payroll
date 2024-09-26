<?php

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AccountsMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Auth::check()
        ? redirect(route('dashboard'))
        : Inertia::render('Welcome', [
            'canLogin' => Route::has('auth.redirect'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', AccountsMiddleware::class])->group(function () {
    Route::get('/accounts', [ProfileController::class, 'index'])
        ->name('accounts');
    Route::post('/accounts/new', [ProfileController::class, 'store'])
        ->name('profile.store');

    Route::get('/account/{user}', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/account/{user}', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/account/{user}', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
