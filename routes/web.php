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
    Route::get('/account/new', [ProfileController::class, 'add'])
        ->name('account.newForm');
    Route::post('/account/new', [ProfileController::class, 'store'])
        ->name('account.new');

    // account actions
    Route::get('/account/{user}', [ProfileController::class, 'edit'])
        ->name('account.updateForm');
    Route::patch('/account/{user}', [ProfileController::class, 'update'])
        ->name('account.update');

    // account variable actions
    Route::post('/account/{user}/variable/{variable}', [ProfileController::class, 'addVariable'])
        ->name('userVariableItem.add');
    Route::patch('/userVariableItem/{variableItem}', [ProfileController::class, 'updateVariable'])
        ->name('userVariableItem.update');
    Route::delete('/userVariableItem/{variableItem}', [ProfileController::class, 'deleteVariable'])
        ->name('userVariableItem.delete');

    // cutoff index / creation
    Route::get('/cutoffs', [PayrollPeriodController::class, 'index'])
        ->name('cutoffs');
    Route::get('/cutoff/new', [PayrollPeriodController::class, 'add'])
        ->name('cutoff.newForm');
    Route::post('/cutoff/new', [PayrollPeriodController::class, 'store'])
        ->name('cutoff.new');

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
    Route::get('/account/{user}/current', [PayrollController::class, 'getCurrentItemFromUser'])
        ->name('payroll.getCurrentFromUser');

    // list available accounts/cutoffs for a specific cutoff/account
    Route::get('/cutoff/{cutoff}/accounts', [ProfileController::class, 'getFromCutoff'])
        ->name('accounts.getFromCutoff');
    Route::get('/account/{user}/cutoffs', [PayrollPeriodController::class, 'getFromUser'])
        ->name('cutoffs.getFromUser');

    // get specific entry
    Route::get('/cutoff/{cutoff}/account/{user}', [PayrollController::class, 'getItem'])
        ->name('payroll.get');

    // addition actions
    Route::post('/payroll/{payrollItem}/addition/{addition}', [PayrollController::class, 'addAdditionItem'])
        ->name('additionItem.new');
    Route::patch('/additionItem/{additionItem}', [PayrollController::class, 'updateAdditionItem'])
        ->name('additionItem.update');
    Route::delete('/additionItem/{additionItem}', [PayrollController::class, 'deleteAdditionItem'])
        ->name('additionItem.delete');

    // addition variable actions
    Route::patch('/additionVariableItem/{variableItem}', [PayrollController::class, 'updateAdditionVariableItem'])
        ->name('additionVariableItem.update');

    // deduction actions
    Route::post('/payroll/{payrollItem}/deduction/{deduction}', [PayrollController::class, 'addDeductionItem'])
        ->name('deductionItem.new');
    Route::patch('/deductionItem/{deductionItem}', [PayrollController::class, 'updateDeductionItem'])
        ->name('deductionItem.update');
    Route::delete('/deductionItem/{deductionItem}', [PayrollController::class, 'deleteDeductionItem'])
        ->name('deductionItem.delete');
});

require __DIR__.'/auth.php';
