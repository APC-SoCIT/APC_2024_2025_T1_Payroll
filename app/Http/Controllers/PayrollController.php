<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Models\Addition;
use App\Models\Cutoff;
use App\Models\Deduction;
use App\Models\ItemAddition;
use App\Models\ItemDeduction;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for dealing with payroll entries
 *
 * @see { App\Http\Controllers\CutoffController } for cutoff scheduling
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

    public function getItem(Cutoff $cutoff, User $user): Response
    {
        $payrollItem = PayrollItem::firstOrCreate([
            'user_id' => $user->id,
            'cutoff_id' => $cutoff->id,
        ]);

        if (! $payrollItem->cutoff->hasEnded()) {
            PayrollHelper::calculateAll($payrollItem);
        }

        // upon first creation, it's not loaded
        $payrollItem->load([
            'cutoff',
            'itemAdditions.addition',
            'itemDeductions.deduction',
        ]);

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
            'deductions' => Deduction::all(),
        ]);
    }

    public function addItemAddition(PayrollItem $payrollItem, Addition $addition): void
    {
        if ($payrollItem->cutoff->hasEnded()) {
            abort(403);
        }

        ItemAddition::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'addition_id' => $addition->id,
        ], [
            'amount' => 0,
        ]);

        PayrollHelper::calculateAll($payrollItem->load('itemAdditions'));
    }

    public function updateItemAddition(Request $request, ItemAddition $itemAddition): void
    {
        if ($itemAddition->payrollItem->cutoff->hasEnded()
            || $itemAddition->addition->calculated) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $itemAddition->amount = round($validated['amount'], 2);
        $itemAddition->save();

        PayrollHelper::calculateAll($itemAddition->payrollItem->load('itemAdditions'));
    }

    public function deleteItemAddition(ItemAddition $itemAddition): void
    {
        if ($itemAddition->payrollItem->cutoff->hasEnded()
            || $itemAddition->addition->required) {
            abort(403);
        }

        $payrollItem = $itemAddition->payrollItem;
        $itemAddition->delete();

        PayrollHelper::calculateAll($payrollItem->load('itemAdditions'));
    }

    public function addItemDeduction(PayrollItem $payrollItem, Deduction $deduction): void
    {
        if ($payrollItem->cutoff->hasEnded()) {
            abort(403);
        }

        ItemDeduction::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => $deduction->id,
        ], [
            'amount' => 0,
        ]);

        PayrollHelper::calculateAll($payrollItem->load('itemDeductions'));
    }

    public function updateItemDeduction(Request $request, ItemDeduction $itemDeduction): void
    {
        if ($itemDeduction->payrollItem->cutoff->hasEnded()
            || $itemDeduction->deduction->calculated) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $itemDeduction->amount = round($validated['amount'], 2);
        $itemDeduction->save();

        PayrollHelper::calculateAll($itemDeduction->payrollItem->load('itemDeductions'));
    }

    public function deleteItemDeduction(ItemDeduction $itemDeduction): void
    {
        if ($itemDeduction->payrollItem->cutoff->hasEnded()
            || $itemDeduction->deduction->required) {
            abort(403);
        }

        $payrollItem = $itemDeduction->payrollItem;
        $itemDeduction->delete();

        PayrollHelper::calculateAll($payrollItem->load('itemDeductions'));
    }
}
