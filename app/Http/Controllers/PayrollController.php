<?php

namespace App\Http\Controllers;

use App\Models\Addition;
use App\Models\AdditionItem;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PayrollController extends Controller
{
    public function getItem(User $user): Response
    {
        $payrollItem = PayrollItem::with([
            'additionItems.addition',
            'deductionItems.deduction',
            'payrollPeriod',
        ])->firstOrCreate([
            'user_id' => $user->id,
            'payroll_period_id' => 1,
        ]);

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
        ]);
    }

    public function addAdditionItem(PayrollItem $payrollItem, Addition $addition): RedirectResponse
    {
        AdditionItem::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'addition_id' => $addition->id,
        ], [
            'amount' => 0
        ]);

        return redirect(route('payroll.getItem', Auth::user()->id));
    }

    public function updateAdditionItem(Request $request, AdditionItem $additionItem): RedirectResponse
    {
        $additionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.getItem', Auth::user()->id));
    }
}
