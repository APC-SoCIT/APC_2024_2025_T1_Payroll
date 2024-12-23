<?php

use App\Http\Controllers\PayrollController;
use App\Http\Controllers\CutoffController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdditionController;
use App\Http\Controllers\DeductionController;
use App\Http\Middleware\RoleMiddleware;
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
    Route::get('/cutoff/{cutoff}/account/{user}', [PayrollController::class, 'getItem'])
        ->name('payroll.get');

    Route::get('/cutoff/{cutoff}/account/{user}/payslip', [PayrollController::class, 'exportPdf'])
        ->name('payslip.download');


    // Mockup Additions
    Route::get('/mockup/addition', function () {
        return Inertia::render('Mockup/Additions/Addition');
    })
        ->name('mockup.addition');

    Route::get('/mockup/additionsalaryaccount', function () {
        return Inertia::render('Mockup/Additions/AdditionSalaryAccount');
    })
        ->name('mockup.additionsalaryaccount');

    Route::get('/mockup/additionsalarydetails', function () {
        return Inertia::render('Mockup/Additions/AdditionSalaryDetails');
    })
        ->name('mockup.additionsalarydetails');

    // Mockup Deductions
    Route::get('/mockup/deduction', function () {
        return Inertia::render('Mockup/Deductions/Deduction');
    })
        ->name('mockup.deduction');

    Route::get('/mockup/deductionabsenceaccount', function () {
        return Inertia::render('Mockup/Deductions/DeductionAbsenceAccount');
    })
        ->name('mockup.deductionabsenceaccount');

    Route::get('/mockup/deductionabsencedetails', function () {
        return Inertia::render('Mockup/Deductions/DeductionAbsenceDetails');
    })
        ->name('mockup.deductionabsencedetails');
    });


// ADMIN
Route::middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::post('/account/{user}/role/{role}', [AccountController::class, 'addRole'])
        ->name('role.add');
    Route::delete('/role/{userRole}', [AccountController::class, 'removeRole'])
        ->name('role.remove');
});

// HR OR PAYROLL OR ADMIN
Route::middleware(['auth', RoleMiddleware::class . ':admin,hr,payroll'])->group(function () {
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
});

// HR OR PAYROLL
Route::middleware(['auth', RoleMiddleware::class . ':hr,payroll'])->group(function () {
    // cutoffs
    Route::get('/cutoffs', [CutoffController::class, 'index'])
        ->name('cutoffs');
    Route::get('/account/{user}/cutoffs', [CutoffController::class, 'getFromUser'])
        ->name('cutoffs.getFromUser');
    Route::get('/cutoff/{cutoff}/accounts', [AccountController::class, 'getFromCutoff'])
        ->name('accounts.getFromCutoff');

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

    // addition type actions
    Route::get('/additions', [AdditionController::class, 'index'])
        ->name('additions');
    Route::get('/addition/{addition}/entries', [AdditionController::class, 'getRelatedEntries'])
        ->name('addition.getRelated');

    // addition actions
    Route::post('/cutoff/{cutoff}/account/{user}/addition/{addition}', [PayrollController::class, 'addItemAddition'])
        ->name('itemAddition.new');
    Route::patch('/itemAddition/{itemAddition}', [PayrollController::class, 'updateItemAddition'])
        ->name('itemAddition.update');
    Route::delete('/itemAddition/{itemAddition}', [PayrollController::class, 'deleteItemAddition'])
        ->name('itemAddition.delete');

    // deduction type actions
    Route::get('/deductions', [DeductionController::class, 'index'])
        ->name('deductions');
    Route::get('/deduction/{deduction}/entries', [DeductionController::class, 'getRelatedEntries'])
        ->name('deduction.getRelated');

    // deduction actions
    Route::post('/cutoff/{cutoff}/account/{user}/deduction/{deduction}', [PayrollController::class, 'addItemDeduction'])
        ->name('itemDeduction.new');
    Route::patch('/itemDeduction/{itemDeduction}', [PayrollController::class, 'updateItemDeduction'])
        ->name('itemDeduction.update');
    Route::delete('/itemDeduction/{itemDeduction}', [PayrollController::class, 'deleteItemDeduction'])
        ->name('itemDeduction.delete');
});

// PAYROLL ONLY
Route::middleware(['auth', RoleMiddleware::class . ':payroll'])->group(function () {
    // current payroll entry actions
    Route::get('/account/{user}/current', [PayrollController::class, 'getCurrentItemFromUser'])
        ->name('payroll.getCurrentFromUser');
    Route::get('/cutoff/{cutoff}/payslips', [PayrollController::class, 'exportPdfs'])
        ->name('cutoff.payslips');

    // specific entry actions
    Route::delete('/cutoff/{cutoff}/account/{user}', [PayrollController::class, 'deleteItem'])
        ->name('payroll.delete');
    Route::get('/cutoff/{cutoff}/export', [PayrollController::class, 'exportCutoffData'])
        ->name('cutoff.export');

    // addition type actions
    Route::get('/addition/new', [AdditionController::class, 'new'])
        ->name('addition.newForm');
    Route::post('/addition/new', [AdditionController::class, 'store'])
        ->name('addition.new');
    Route::get('/addition/{addition}', [AdditionController::class, 'edit'])
        ->name('addition.edit');
    Route::patch('/addition/{addition}', [AdditionController::class, 'update'])
        ->name('addition.update');

    // deduction type actions
    Route::get('/deduction/new', [DeductionController::class, 'new'])
        ->name('deduction.newForm');
    Route::post('/deduction/new', [DeductionController::class, 'store'])
        ->name('deduction.new');
    Route::get('/deduction/{deduction}', [DeductionController::class, 'edit'])
        ->name('deduction.edit');
    Route::patch('/deduction/{deduction}', [DeductionController::class, 'update'])
        ->name('deduction.update');
});

require __DIR__.'/auth.php';
