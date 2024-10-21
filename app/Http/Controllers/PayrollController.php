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
use Illuminate\Database\Eloquent\Builder;
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
        $currentPeriod->save();

        return self::getItem($currentPeriod, $user);
    }

    public function getItem(PayrollPeriod $cutoff, User $user): Response
    {
        $payrollItem = PayrollItem::firstOrCreate([
            'user_id' => $user->id,
            'payroll_period_id' => $cutoff->id,
        ]);

        if (!$payrollItem->payrollPeriod->hasEnded()) {
            self::calculateAll($payrollItem);
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

        return redirect(route('payroll.get', [
            'cutoff' => $payrollItem->payrollPeriod->id,
            'user' => $payrollItem->user->id,
        ]));
    }

    public function updateAdditionItem(Request $request, AdditionItem $additionItem): RedirectResponse
    {
        if ($additionItem->payrollItem->payrollPeriod->hasEnded()
            || $additionItem->addition->calculated) {
            abort(403);
        }

        $additionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.get', [
            'cutoff' => $additionItem->payrollItem->payrollPeriod->id,
            'user' => $additionItem->payrollItem->user->id,
        ]));
    }

    public function deleteAdditionItem(AdditionItem $additionItem): RedirectResponse
    {
        if ($additionItem->payrollItem->payrollPeriod->hasEnded()
            || $additionItem->addition->required) {
            abort(403);
        }

        $cutoff_id = $additionItem->payrollItem->id;
        $user_id = $additionItem->payrollItem->user->id;
        $additionItem->delete();

        return redirect(route('payroll.get', [
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

        return redirect(route('payroll.get', [
            'cutoff' => $payrollItem->payrollPeriod->id,
            'user' => $payrollItem->user->id,
        ]));
    }

    public function updateDeductionItem(Request $request, DeductionItem $deductionItem): RedirectResponse
    {
        if ($deductionItem->payrollItem->payrollPeriod->hasEnded()
            || $deductionItem->deduction->calculated) {
            abort(403);
        }

        $deductionItem->update($request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]));

        return redirect(route('payroll.get', [
            'cutoff' => $deductionItem->payrollItem->payrollPeriod->id,
            'user' => $deductionItem->payrollItem->user->id,
        ]));
    }

    public function deleteDeductionItem(DeductionItem $deductionItem): RedirectResponse
    {
        if ($deductionItem->payrollItem->payrollPeriod->hasEnded()
            || $deductionItem->deduction->required) {
            abort(403);
        }

        $cutoff_id = $deductionItem->payrollItem->id;
        $user_id = $deductionItem->payrollItem->user->id;
        $deductionItem->delete();

        return redirect(route('payroll.get', [
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

            $currentPeriod = new PayrollPeriod;
            $currentPeriod->start_date = $start->toDateString();
            $currentPeriod->cutoff_date = $cutoff->toDateString();
            $currentPeriod->end_date = $end->toDateString();
        }

        return $currentPeriod;
    }

    private static function calculateAll(PayrollItem $item): void
    {
         self::calculateBasePay($item);
         self::calculatePagibig($item);
         self::calculatePhilhealth($item);
         self::calculateTax($item);

        $totalAdditions = $item->additionItems
            ->reduce(function (?int $carry, ?AdditionItem $item) {
                return $carry + $item->amount;
            });

        $totalDeductions = $item->deductionItems
            ->reduce(function (?int $carry, ?deductionItem $item) {
                return $carry + $item->amount;
            });

        $item->amount = $totalAdditions - $totalDeductions;
        $item->save();
    }

    private static function calculateBasePay(PayrollItem $payrollItem): void
    {
        $user = $payrollItem->user;
        $user->load('userVariableItems');
        AdditionItem::updateOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'addition_id' => 1,
        ], [
            'amount' => $user
                ->userVariableItems
                ->where('user_variable_id', 1)
                ->first()
                ->value,
        ]);
    }

    private static function calculateTax(PayrollItem $payrollItem): void
    {
        $totalAdditions = $payrollItem->additionItems
            ->whereIn('addition_id', [
                1,  // salary
                10, // salary adjustment
            ])
            ->reduce(function (?int $carry, ?AdditionItem $item) {
                return $carry + $item->amount;
            });

        $totalDeductionsBeforeTax = $payrollItem->deductionItems
            ->whereIn('deduction_id', [
                2, // SSS
                3, // PhilHealth
                4, // Pag-IBIG
                5, // salary adjustment
            ])
            ->reduce(function (?int $carry, ?DeductionItem $item) {
                return $carry + $item->amount;
            });

        $netBeforeTax = $totalAdditions - $totalDeductionsBeforeTax;
        $yearEstimate = $netBeforeTax * 24; // 24 cutoffs in a year

        $brackets = collect([
            [
                'bracket' => 0,
                'baseTax' => 0,
                'excessRate' => 0,
            ],
            [
                'bracket' => 250000,
                'baseTax' => 0,
                'excessRate' => 0.15,
            ],
            [
                'bracket' => 400000,
                'baseTax' => 22500,
                'excessRate' => 0.20,
            ],
            [
                'bracket' => 800000,
                'baseTax' => 102500,
                'excessRate' => 0.25,
            ],
            [
                'bracket' => 2000000,
                'baseTax' => 402500,
                'excessRate' => 0.30,
            ],
            [
                'bracket' => 8000000,
                'baseTax' => 2202500,
                'excessRate' => 0.35,
            ],
        ]);

        $bracket = $brackets->where('bracket', '<', $yearEstimate)
            ->sortByDesc('bracket')
            ->first()
            ?? $brackets[0];

        $excess = $yearEstimate - $bracket['bracket'];
        $excessTax = $excess * $bracket['excessRate'];

        $tax = ($bracket['baseTax'] + $excessTax) / 24;
        $tax = round($tax, 2);

        DeductionItem::updateOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => 1,
        ], [
            'amount' => $tax,
        ]);

        $payrollItem->load('deductionItems');
    }

    private static function calculatePagibig(PayrollItem $payrollItem): void
    {
        DeductionItem::updateOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => 4,
        ], [
            'amount' => $payrollItem->user
                ->userVariableItems
                ->where('user_variable_id', 2)
                ->first()
                ->value
        ]);

        $payrollItem->load('deductionItems');
    }

    private static function calculatePhilhealth(PayrollItem $payrollItem): void
    {
        $thisPay = $payrollItem->user
            ->userVariableItems
            ->where('user_variable_id', 1) // base pay
            ->first()
            ->value;

        $lastPay = PayrollItem::where('user_id', $payrollItem->user_id)
            ->whereHas('payrollPeriod', function (Builder $query) use ($payrollItem) {
                $query->where('end_date', '<', $payrollItem->payrollPeriod->end_date)
                    ->orderBy('end_date');
            })
            ->first();

        $lastPay = is_null($lastPay)
            ? $thisPay
            : $lastPay->additionItems
                ->where('addition_id', 1)
                ->first()
                ->amount;

        error_log($lastPay);

        $monthPay = $thisPay + $lastPay;

        DeductionItem::updateOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => 3,
        ], [
            'amount' => $monthPay < 10000 ? 500 : ($monthPay > 100000 ? 5000 : $monthPay * 0.05)
        ]);

        $payrollItem->load('deductionItems');
    }
}
