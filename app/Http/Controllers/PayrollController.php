<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Models\Addition;
use App\Models\AdditionItem;
use App\Models\Deduction;
use App\Models\DeductionItem;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for dealing with payroll entries
 *
 * @see { App\Http\Controllers\PayrollPeriodController } for cutoff scheduling
 */
class PayrollController extends Controller
{
    public function getCurrentItemFromUser(User $user): Response
    {
        if (! $user->active) {
            abort(404);
        }

        $currentPeriod = PayrollHelper::currentPeriod();
        $currentPeriod->save();

        return self::getItem($currentPeriod, $user);
    }

    public function getItem(PayrollPeriod $cutoff, User $user): Response
    {
        $payrollItem = PayrollItem::firstOrCreate([
            'user_id' => $user->id,
            'payroll_period_id' => $cutoff->id,
        ]);

        if (! $payrollItem->payrollPeriod->hasEnded()) {
            PayrollHelper::calculateAll($payrollItem);
        }

        // upon first creation, it's not loaded
        $payrollItem->load([
            'payrollPeriod',
            'additionItems.addition',
            'deductionItems.deduction',
        ]);

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
            'deductions' => Deduction::all(),
        ]);
    }

    public function addAdditionItem(PayrollItem $payrollItem, Addition $addition): void
    {
        if ($payrollItem->payrollPeriod->hasEnded()) {
            abort(403);
        }

        AdditionItem::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'addition_id' => $addition->id,
        ], [
            'amount' => 0,
        ]);

        PayrollHelper::calculateAll($payrollItem->load('additionItems'));
    }

    public function updateAdditionItem(Request $request, AdditionItem $additionItem): void
    {
        if ($additionItem->payrollItem->payrollPeriod->hasEnded()
            || $additionItem->addition->calculated) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $additionItem->amount =round($validated['amount'], 2);
        $additionItem->save();

        PayrollHelper::calculateAll($additionItem->payrollItem->load('additionItems'));
    }

    public function deleteAdditionItem(AdditionItem $additionItem): void
    {
        if ($additionItem->payrollItem->payrollPeriod->hasEnded()
            || $additionItem->addition->required) {
            abort(403);
        }

        $payrollItem = $additionItem->payrollItem;
        $additionItem->delete();

        PayrollHelper::calculateAll($payrollItem->load('additionItems'));
    }

    public function addDeductionItem(PayrollItem $payrollItem, Deduction $deduction): void
    {
        if ($payrollItem->payrollPeriod->hasEnded()) {
            abort(403);
        }

        DeductionItem::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => $deduction->id,
        ], [
            'amount' => 0,
        ]);

        PayrollHelper::calculateAll($payrollItem->load('deductionItems'));
    }

    public function updateDeductionItem(Request $request, DeductionItem $deductionItem): void
    {
        if ($deductionItem->payrollItem->payrollPeriod->hasEnded()
            || $deductionItem->deduction->calculated) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $deductionItem->update([
            'amount' => round($validated['amount'], 2),
        ]);

        $deductionItem->amount =round($validated['amount'], 2);
        $deductionItem->save();

        PayrollHelper::calculateAll($deductionItem->payrollItem->load('deductionItems'));
    }

    public function deleteDeductionItem(DeductionItem $deductionItem): void
    {
        if ($deductionItem->payrollItem->payrollPeriod->hasEnded()
            || $deductionItem->deduction->required) {
            abort(403);
        }

        $payrollItem = $deductionItem->payrollItem;
        $deductionItem->delete();

        PayrollHelper::calculateAll($payrollItem->load('deductionItems'));
    }
}
