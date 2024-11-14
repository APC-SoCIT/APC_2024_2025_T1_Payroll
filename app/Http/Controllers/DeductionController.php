<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Models\Deduction;
use App\Models\ItemDeduction;
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

        return Inertia::render('Deduction/ItemDeductions', [
            'deduction' => $deduction,
            'cutoff' => $currentCutoff,
            'itemDeductions' => $entries,
        ]);
    }
}
