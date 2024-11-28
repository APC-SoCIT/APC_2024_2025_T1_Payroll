<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\PayrollHelper;
use App\Models\Deduction;
use App\Models\ItemDeduction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeductionController extends Controller
{
    public function index(): Response
    {
        $deductions = [];
        if (AuthHelper::isPayroll()) {
            $deductions = Deduction::all();
        } else {
            $deductions = Deduction::whereHrAccess(true)->get();
        }
        return Inertia::render('Deduction/Index', ['deductions' => $deductions]);
    }

    public function getRelatedEntries(Deduction $deduction): Response
    {
        $currentCutoff = PayrollHelper::currentPeriod();
        $entries = ItemDeduction::with([
            'payrollItem.user',
            'deduction',
        ])
            ->where('deduction_id', $deduction->id)
            ->whereHas('payrollItem', function (Builder $query) use ($currentCutoff) {
                $query->where('cutoff_id', $currentCutoff->id);
            })
            ->get();

        $accountsWithout = User::whereDoesntHave('payrollItems', function (Builder $query) use ($deduction, $currentCutoff) {
            $query->where('cutoff_id', $currentCutoff->id)
                ->whereHas('itemDeductions', function (Builder $query) use ($deduction) {
                    $query->where('deduction_id', $deduction->id);
                });
        })
            ->get();

        return Inertia::render('Deduction/ItemDeductions', [
            'deduction' => $deduction,
            'cutoff' => $currentCutoff,
            'itemDeductions' => $entries,
            'accountsWithout' => $accountsWithout,
        ]);
    }

    public function new(): Response
    {
        return Inertia::render('Deduction/NewDeduction');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(self::$validationRules);

        $validated['required'] = false;
        $validated['calculated'] = false;
        $validated['hour_based'] = false;

        Deduction::create($validated);

        return redirect(route('deductions'));
    }

    public function edit(Deduction $deduction): Response
    {
        return Inertia::render('Deduction/EditDeduction', ['deduction' => $deduction]);
    }

    public function update(Deduction $deduction, Request $request): void
    {
        $validated = $request->validate(self::$validationRules);

        $deduction->update($validated);
    }

    private static $validationRules = [
        'name' => ['required', 'string', 'min:1', 'max:255'],
        'description' => ['required', 'string', 'min:1', 'max:255'],
        'taxable' => ['boolean'],
        'has_deadline' => ['boolean'],
        'hr_access' => ['boolean'],
    ];
}
