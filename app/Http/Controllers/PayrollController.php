<?php

namespace App\Http\Controllers;

use App\Models\AdditionItem;
use App\Models\DeductionItem;
use App\Models\PayrollItem;
use App\Models\User;
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

        AdditionItem::firstOrCreate([
            'payroll_item_id' => 1,
            'addition_id' => 1,
            'amount' => 100,
        ]);

        DeductionItem::firstOrCreate([
            'payroll_item_id' => 1,
            'deduction_id' => 1,
            'amount' => 100,
        ]);

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
        ]);
    }
}
