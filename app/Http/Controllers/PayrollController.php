<?php

namespace App\Http\Controllers;

use App\Models\Addition;
use App\Models\AdditionItem;
use App\Models\Deduction;
use App\Models\DeductionItem;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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
        $currentPeriod = self::currentPeriod();
        $payrollItem = PayrollItem::with([
            'additionItems.addition',
            'deductionItems.deduction',
            'payrollPeriod',
        ])->firstOrCreate([
            'user_id' => $user->id,
            'payroll_period_id' => $currentPeriod->id,
        ]);

        // upon first creation, it's not loaded
        $payrollItem->load('payrollPeriod');

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
            'deductions' => Deduction::all(),
        ]);
    }

    public function getItem(PayrollPeriod $cutoff, User $user): Response
    {
        $payrollItem = PayrollItem::with([
            'additionItems.addition',
            'deductionItems.deduction',
            'payrollPeriod',
        ])->firstOrCreate([
            'user_id' => $user->id,
            'payroll_period_id' => $cutoff->id,
        ]);

        // upon first creation, it's not loaded
        $payrollItem->load('payrollPeriod');

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
            'deductions' => Deduction::all(),
        ]);
    }

    public function addAdditionItem(PayrollItem $payrollItem, Addition $addition): RedirectResponse
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

        return redirect(route('payroll.getItem', [
            'cutoff' => $payrollItem->payrollPeriod->id,
            'user' => $payrollItem->user->id,
        ]));
    }

    public function updateAdditionItem(Request $request, AdditionItem $additionItem): RedirectResponse
    {
        if ($additionItem->payrollItem->payrollPeriod->hasEnded()) {
            abort(403);
        }

        $additionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.getItem', [
            'cutoff' => $additionItem->payrollItem->payrollPeriod->id,
            'user' => $additionItem->payrollItem->user->id,
        ]));
    }

    public function deleteAdditionItem(AdditionItem $additionItem): RedirectResponse
    {
        if ($additionItem->payrollItem->payrollPeriod->hasEnded()) {
            abort(403);
        }

        $cutoff_id = $additionItem->payrollItem->id;
        $user_id = $additionItem->payrollItem->user->id;
        $additionItem->delete();

        return redirect(route('payroll.getItem', [
            'cutoff' => $cutoff_id,
            'user' => $user_id,
        ]));
    }

    public function addDeductionItem(PayrollItem $payrollItem, Deduction $deduction): RedirectResponse
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

        return redirect(route('payroll.getItem', [
            'cutoff' => $payrollItem->payrollPeriod->id,
            'user' => $payrollItem->user->id,
        ]));
    }

    public function updateDeductionItem(Request $request, DeductionItem $deductionItem): RedirectResponse
    {
        if ($deductionItem->payrollItem->payrollPeriod->hasEnded()) {
            abort(403);
        }

        $deductionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.getItem', [
            'cutoff' => $deductionItem->payrollItem->payrollPeriod->id,
            'user' => $deductionItem->payrollItem->user->id,
        ]));
    }

    public function deleteDeductionItem(DeductionItem $deductionItem): RedirectResponse
    {
        if ($deductionItem->payrollItem->payrollPeriod->hasEnded()) {
            abort(403);
        }

        $cutoff_id = $deductionItem->payrollItem->id;
        $user_id = $deductionItem->payrollItem->user->id;
        $deductionItem->delete();

        return redirect(route('payroll.getItem', [
            'cutoff' => $cutoff_id,
            'user' => $user_id,
        ]));
    }

    /*
     * Get the current period.
     * If the latest period in the database is outdated, generate a new one.
     */
    public static function currentPeriod(): PayrollPeriod
    {
        $now = Carbon::now();
        $nowString = $now->toDateString();

        $currentPeriod = PayrollPeriod::where('start_date', '<=', $nowString)
            ->where('end_date', '>=', $nowString)
            ->oldest('end_date')
            ->first();

        if (is_null($currentPeriod)) {
            $start = null;
            $cutoff = null;
            $end = null;

            if ($now->day < 11) {
                $start = $now->copy()
                    ->startOfMonth();

                $cutoff = $now->copy()
                    ->setDay(10);

                $end = $now->copy()
                    ->setDay(15);
            } elseif ($now->day > 25) {
                $start = $now->copy()
                    ->addMonth()
                    ->startOfMonth();

                $cutoff = $now->copy()
                    ->addMonth()
                    ->setDay(10);

                $end = $now->copy()
                    ->addMonth()
                    ->setDay(15);
            } else {
                $start = $now->copy()
                    ->setDay(16);

                $cutoff = $now->copy()
                    ->setDay(25);

                $end = $now->copy()
                    ->endOfMonth();
            }

            $currentPeriod = PayrollPeriod::create([
                'start_date' => $start->toDateString(),
                'cutoff_date' => $cutoff->toDateString(),
                'end_date' => $end->toDateString(),
            ]);
        }

        return $currentPeriod;
    }
}
