<?php

use App\Http\Controllers\PayrollController;
use App\Http\Controllers\CutoffController;
use App\Http\Controllers\AccountController;
use App\Http\Middleware\AuthorizedMiddleware;
use App\Http\Middleware\HrMiddleware;
use App\Http\Middleware\PayrollMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// PUBLIC
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect(route('dashboard'));
    })->name('index');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    // get own account
    Route::get('/account', [AccountController::class, 'getOwn'])
        ->name('account.me');

    // get own related cutoffs
    Route::get('/cutoffs/me', [CutoffController::class, 'getOwn'])
        ->name('cutoffs.me');

    // get specific entry
    Route::get('/cutoff/{cutoff}/account/{user}', [PayrollController::class, 'getItem'])
        ->name('payroll.get');
});

// HR OR PAYROLL
Route::middleware(['auth', AuthorizedMiddleware::class])->group(function () {
    // account index
    Route::get('/accounts', [AccountController::class, 'index'])
        ->name('accounts');

    // account actions
    Route::get('/account/new', [AccountController::class, 'add'])
        ->name('account.newForm');
    Route::post('/account/new', [AccountController::class, 'store'])
        ->name('account.new');
    Route::get('/account/{user}', [AccountController::class, 'edit'])
        ->name('account.get');
    Route::patch('/account/{user}', [AccountController::class, 'update'])
        ->name('account.update');

    // cutoffs
    Route::get('/cutoffs', [CutoffController::class, 'index'])
        ->name('cutoffs');
    Route::get('/account/{user}/cutoffs', [CutoffController::class, 'getFromUser'])
        ->name('cutoffs.getFromUser');
    Route::get('/cutoff/{cutoff}/accounts', [AccountController::class, 'getFromCutoff'])
        ->name('accounts.getFromCutoff');
});

// HR ONLY
Route::middleware(['auth', HrMiddleware::class])->group(function () {
    // cutoff actions
    Route::get('/cutoff/new', [CutoffController::class, 'add'])
        ->name('cutoff.newForm');
    Route::post('/cutoff/new', [CutoffController::class, 'store'])
        ->name('cutoff.new');
    Route::get('/cutoff/{cutoff}', [CutoffController::class, 'get'])
        ->name('cutoff.get');
    Route::patch('/cutoff/{cutoff}', [CutoffController::class, 'update'])
        ->name('cutoff.update');
    Route::delete('/cutoff/{cutoff}', [CutoffController::class, 'delete'])
        ->name('cutoff.delete');
});

// PAYROLL ONLY
Route::middleware(['auth', PayrollMiddleware::class])->group(function () {
    // get a users's current payroll entry
    Route::get('/account/{user}/current', [PayrollController::class, 'getCurrentItemFromUser'])
        ->name('payroll.getCurrentFromUser');

    // addition actions
    Route::post('/payroll/{payrollItem}/addition/{addition}', [PayrollController::class, 'addItemAddition'])
        ->name('itemAddition.new');
    Route::patch('/itemAddition/{itemAddition}', [PayrollController::class, 'updateItemAddition'])
        ->name('itemAddition.update');
    Route::delete('/itemAddition/{itemAddition}', [PayrollController::class, 'deleteItemAddition'])
        ->name('itemAddition.delete');

    // deduction actions
    Route::post('/payroll/{payrollItem}/deduction/{deduction}', [PayrollController::class, 'addItemDeduction'])
        ->name('itemDeduction.new');
    Route::patch('/itemDeduction/{itemDeduction}', [PayrollController::class, 'updateItemDeduction'])
        ->name('itemDeduction.update');
    Route::delete('/itemDeduction/{itemDeduction}', [PayrollController::class, 'deleteItemDeduction'])
        ->name('itemDeduction.delete');
});

require __DIR__.'/auth.php';
