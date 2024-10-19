<?php

use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AccountsMiddleware;
use App\Http\Middleware\PayrollMiddleware;
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
    Route::get('/accounts/new', [ProfileController::class, 'add'])
        ->name('profile.add');
    Route::post('/accounts/new', [ProfileController::class, 'store'])
        ->name('profile.store');

    Route::get('/account/{user}', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/account/{user}', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/account/{user}', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::get('/cutoffs', [PayrollPeriodController::class, 'index'])
        ->name('cutoffs');
    Route::get('/cutoff/new', [PayrollPeriodController::class, 'add'])
        ->name('cutoff.add');
    Route::post('/cutoff/new', [PayrollPeriodController::class, 'store'])
        ->name('cutoff.store');
    Route::get('/cutoff/{cutoff}', [PayrollPeriodController::class, 'get'])
        ->name('cutoff.get');
    Route::patch('/cutoff/{cutoff}', [PayrollPeriodController::class, 'update'])
        ->name('cutoff.update');
    Route::delete('/cutoff/{cutoff}', [PayrollPeriodController::class, 'delete'])
        ->name('cutoff.delete');
});

Route::middleware(['auth', PayrollMiddleware::class])->group(function () {
    Route::get('/payroll/user/{user}', [PayrollController::class, 'getCurrentItemFromUser'])
        ->name('payroll.getCurrentItemFromUser');

    Route::post('/payroll/additionItem/{payrollItem}/{addition}', [PayrollController::class, 'addAdditionItem'])
        ->name('payroll.addAdditionItem');
    Route::patch('/payroll/additionItem/{additionItem}', [PayrollController::class, 'updateAdditionItem'])
        ->name('payroll.updateAdditionItem');
    Route::delete('/payroll/additionItem/{additionItem}', [PayrollController::class, 'deleteAdditionItem'])
        ->name('payroll.deleteAdditionItem');

    Route::post('/payroll/deductionItem/{payrollItem}/{deduction}', [PayrollController::class, 'addDeductionItem'])
        ->name('payroll.addDeductionItem');
    Route::patch('/payroll/deductionItem/{deductionItem}', [PayrollController::class, 'updateDeductionItem'])
        ->name('payroll.updateDeductionItem');
    Route::delete('/payroll/deductionItem/{deductionItem}', [PayrollController::class, 'deleteDeductionItem'])
        ->name('payroll.deleteDeductionItem');
});

require __DIR__ . '/auth.php';
