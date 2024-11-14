<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Models\Deduction;
use App\Models\ItemDeduction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class DeductionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Deduction/Index', ['deductions' => Deduction::all()]);
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
}
