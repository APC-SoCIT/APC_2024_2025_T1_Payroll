<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Models\Addition;
use App\Models\ItemAddition;
use App\Models\User;
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

        $accountsWithout = User::whereDoesntHave('payrollItems', function (Builder $query) use ($addition, $currentCutoff) {
            $query->where('cutoff_id', $currentCutoff->id)
                ->whereHas('itemAdditions', function (Builder $query) use ($addition) {
                    $query->where('addition_id', $addition->id);
                });
        })
            ->get();

        return Inertia::render('Addition/ItemAdditions', [
            'addition' => $addition,
            'cutoff' => $currentCutoff,
            'itemAdditions' => $entries,
            'accountsWithout' => $accountsWithout,
        ]);
    }
}
