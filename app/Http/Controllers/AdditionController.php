<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Helpers\PayrollHelper;
use App\Models\Addition;
use App\Models\ItemAddition;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdditionController extends Controller
{
    public function index(): Response
    {
        $additions = [];
        if (AuthHelper::isPayroll()) {
            $additions = Addition::all();
        } else {
            $additions = Addition::whereHrAccess(true)->get();
        }
        return Inertia::render('Addition/Index', ['additions' => $additions]);
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

    public function new(): Response
    {
        return Inertia::render('Addition/NewAddition');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(self::$validationRules);

        $validated['required'] = false;
        $validated['calculated'] = false;
        $validated['hour_based'] = false;

        Addition::create($validated);

        return redirect(route('additions'));
    }

    public function edit(Addition $addition): Response
    {
        return Inertia::render('Addition/EditAddition', ['addition' => $addition]);
    }

    public function update(Addition $addition, Request $request): void
    {
        $validated = $request->validate(self::$validationRules);

        $addition->update($validated);
    }

    private static $validationRules = [
        'name' => ['required', 'string', 'min:1', 'max:255'],
        'description' => ['required', 'string', 'min:1', 'max:255'],
        'taxable' => ['boolean'],
        'hr_access' => ['boolean'],
    ];
}
