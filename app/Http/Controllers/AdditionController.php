<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Models\Addition;
use App\Models\ItemAddition;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class AdditionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Addition/Index', ['additions' => Addition::all()]);
    }

    public function getRelatedEntries(Addition $addition): Response
    {
        $currentCutoff = PayrollHelper::currentPeriod();
        $entries = ItemAddition::with([
                'payrollItem.user',
                'addition',
            ])
            ->where('addition_id', $addition->id)
            ->whereHas('payrollItem', function (Builder $query) use ($currentCutoff) {
                $query->where('cutoff_id', $currentCutoff->id);
            })
            ->get();

        return Inertia::render('Addition/ItemAdditions', [
            'addition' => $addition,
            'cutoff' => $currentCutoff,
            'itemAdditions' => $entries,
        ]);
    }
}
