<?php

use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\AccountsMiddleware;
use App\Http\Middleware\PayrollMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route('dashboard'));
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', AccountsMiddleware::class])->group(function () {
    // account index / creation
    Route::get('/accounts', [ProfileController::class, 'index'])
        ->name('accounts');
    Route::get('/accounts/new', [ProfileController::class, 'add'])
        ->name('profile.add');
    Route::post('/accounts/new', [ProfileController::class, 'store'])
        ->name('profile.store');

    // account actions
    Route::get('/account/{user}', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/account/{user}', [ProfileController::class, 'update'])
        ->name('profile.update');

    // account variable actions
    Route::post('/account/{user}/variable/{variable}', [ProfileController::class, 'addVariable'])
        ->name('profile.addUserVariableItem');
    Route::patch('/userVariableItem/{variableItem}', [ProfileController::class, 'updateVariable'])
        ->name('profile.updateUserVariableItem');
    Route::delete('/userVariableItem/{variableItem}', [ProfileController::class, 'deleteVariable'])
        ->name('profile.deleteUserVariableItem');

    // cutoff index / creation
    Route::get('/cutoffs', [PayrollPeriodController::class, 'index'])
        ->name('cutoffs');
    Route::get('/cutoff/new', [PayrollPeriodController::class, 'add'])
        ->name('cutoff.add');
    Route::post('/cutoff/new', [PayrollPeriodController::class, 'store'])
        ->name('cutoff.store');

    // cutoff actions
    Route::get('/cutoff/{cutoff}', [PayrollPeriodController::class, 'get'])
        ->name('cutoff.get');
    Route::patch('/cutoff/{cutoff}', [PayrollPeriodController::class, 'update'])
        ->name('cutoff.update');
    Route::delete('/cutoff/{cutoff}', [PayrollPeriodController::class, 'delete'])
        ->name('cutoff.delete');
});

Route::middleware(['auth', PayrollMiddleware::class])->group(function () {
    // get a users's current payroll entry
    Route::get('/payroll/account/{user}', [PayrollController::class, 'getCurrentItemFromUser'])
        ->name('payroll.getCurrentItemFromUser');

    // list available accounts/cutoffs for a specific cutoff/account
    Route::get('/cutoff/{cutoff}/accounts', [ProfileController::class, 'getFromCutoff'])
        ->name('accounts.getFromCutoff');
    Route::get('/account/{user}/cutoffs', [PayrollPeriodController::class, 'getFromUser'])
        ->name('cutoffs.getFromUser');

    // get specific entry
    Route::get('/cutoff/{cutoff}/account/{user}', [PayrollController::class, 'getItem'])
        ->name('payroll.getItem');

    // addition actions
    Route::post('/payroll/{payrollItem}/additionItem/{addition}', [PayrollController::class, 'addAdditionItem'])
        ->name('payroll.addAdditionItem');
    Route::patch('/payroll/additionItem/{additionItem}', [PayrollController::class, 'updateAdditionItem'])
        ->name('payroll.updateAdditionItem');
    Route::delete('/payroll/additionItem/{additionItem}', [PayrollController::class, 'deleteAdditionItem'])
        ->name('payroll.deleteAdditionItem');

    // deduction actions
    Route::post('/payroll/{payrollItem}/deductionItem/{deduction}', [PayrollController::class, 'addDeductionItem'])
        ->name('payroll.addDeductionItem');
    Route::patch('/payroll/deductionItem/{deductionItem}', [PayrollController::class, 'updateDeductionItem'])
        ->name('payroll.updateDeductionItem');
    Route::delete('/payroll/deductionItem/{deductionItem}', [PayrollController::class, 'deleteDeductionItem'])
        ->name('payroll.deleteDeductionItem');
});

require __DIR__.'/auth.php';
