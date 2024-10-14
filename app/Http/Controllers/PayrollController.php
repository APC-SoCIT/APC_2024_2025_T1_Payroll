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

class PayrollController extends Controller
{
    public function getCurrentItemFromUser(User $user): Response
    {
        $payrollItem = PayrollItem::with([
            'additionItems.addition',
            'deductionItems.deduction',
            'payrollPeriod',
        ])->firstOrCreate([
            'user_id' => $user->id,
            'payroll_period_id' => self::currentPeriod()->id,
        ]);

        if (!self::isCurrentPeriod($payrollItem->payrollPeriod)) {
            abort(403);
        }

        return Inertia::render('Payroll/Item', [
            'targetAccount' => $user,
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
            'deductions' => Deduction::all(),
        ]);
    }

    public function addAdditionItem(PayrollItem $payrollItem, Addition $addition): RedirectResponse
    {
        if (!self::isCurrentPeriod($payrollItem->payrollPeriod)) {
            abort(403);
        }

        $additionItem = AdditionItem::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'addition_id' => $addition->id,
        ], [
            'amount' => 0
        ]);

        return redirect(route('payroll.getCurrentItemFromUser', $additionItem->payrollItem->user->id));
    }

    public function updateAdditionItem(Request $request, AdditionItem $additionItem): RedirectResponse
    {
        if (!self::isCurrentPeriod($additionItem->payrollItem->payrollPeriod)) {
            abort(403);
        }

        $additionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.getCurrentItemFromUser', $additionItem->payrollItem->user->id));
    }

    public function deleteAdditionItem(AdditionItem $additionItem): RedirectResponse
    {
        if (!self::isCurrentPeriod($additionItem->payrollItem->payrollPeriod)) {
            abort(403);
        }

        $id = $additionItem->payrollItem->user->id;
        $additionItem->delete();

        return redirect(route('payroll.getCurrentItemFromUser', $id));
    }

    public function addDeductionItem(PayrollItem $payrollItem, Deduction $deduction): RedirectResponse
    {
        if (!self::isCurrentPeriod($payrollItem->payrollPeriod)) {
            abort(403);
        }

        $deductionItem = DeductionItem::firstOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => $deduction->id,
        ], [
            'amount' => 0
        ]);

        return redirect(route('payroll.getCurrentItemFromUser', $deductionItem->payrollItem->user->id));
    }

    public function updateDeductionItem(Request $request, DeductionItem $deductionItem): RedirectResponse
    {
        if (!self::isCurrentPeriod($deductionItem->payrollItem->payrollPeriod)) {
            abort(403);
        }

        $deductionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.getCurrentItemFromUser', $deductionItem->payrollItem->user->id));
    }

    public function deleteDeductionItem(DeductionItem $deductionItem): RedirectResponse
    {
        if (!self::isCurrentPeriod($deductionItem->payrollItem->payrollPeriod)) {
            abort(403);
        }

        $id = $deductionItem->payrollItem->user->id;
        $deductionItem->delete();

        return redirect(route('payroll.getCurrentItemFromUser', $id));
    }

    /*
     * Get the current period.
     * If the latest period in the database is outdated, generate a new one.
     */
    public static function currentPeriod(): PayrollPeriod
    {
        $now = Carbon::now();

        $start = null;
        $end = null;

        if ($now->day < 11) {
            $start = $now->copy()
                ->subMonth()
                ->setDay(26)
                ->setHour(0)
                ->setMinute(0)
                ->setSecond(0);

            $end = $now->copy()
                ->setDay(10)
                ->setHour(23)
                ->setMinute(59)
                ->setSecond(59);
        } else if ($now->day > 25) {
            $start = $now->copy()
                ->setDay(26)
                ->setHour(0)
                ->setMinute(0)
                ->setSecond(0);

            $end = $now->copy()
                ->addMonth()
                ->setDay(10)
                ->setHour(23)
                ->setMinute(59)
                ->setSecond(59);
        } else {
            $start = $now->copy()
                ->setDay(11)
                ->setHour(0)
                ->setMinute(0)
                ->setSecond(0);

            $end = $now->copy()
                ->setDay(25)
                ->setHour(23)
                ->setMinute(59)
                ->setSecond(59);
        }

        return PayrollPeriod::firstOrCreate([
            'start_date' => $start,
            'end_date' => $end,
        ]);
    }

    public static function isCurrentPeriod(PayrollPeriod $payrollPeriod): bool
    {
        return self::currentPeriod()->is($payrollPeriod);
    }
}
