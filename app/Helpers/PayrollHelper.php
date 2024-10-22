<?php

namespace App\Helpers;

use App\Models\AdditionItem;
use App\Models\DeductionItem;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class PayrollHelper
{
    public static function calculateAll(PayrollItem $item): void
    {
        self::calculateBasePay($item);
        self::calculatePagibig($item);
        self::calculatePhilhealth($item);
        self::calculateSss($item);
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

        $bracket = collect(self::$taxBrackets)
            ->where('bracket', '<', $yearEstimate)
            ->sortByDesc('bracket')
            ->first()
            ?? self::$taxBrackets[0];

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
        $pagibigDeduction = $payrollItem->deductionItems
            ->where('deduction_id', 4)
            ->first();

        if (is_null($pagibigDeduction)) {
            return;
        }

        $pagibigDeduction->amount = $payrollItem->user
            ->userVariableItems
            ->where('user_variable_id', 2)
            ->first()
            ->value;

        $pagibigDeduction->save();
        $payrollItem->load('deductionItems');
    }

    private static function calculatePhilhealth(PayrollItem $payrollItem): void
    {
        $philhealthDeduction = $payrollItem->deductionItems
            ->where('deduction_id', 3)
            ->first();

        if (is_null($philhealthDeduction)) {
            return;
        }

        $thisPay = $payrollItem->user
            ->userVariableItems
            ->where('user_variable_id', 1) // base pay
            ->first()
            ->value;

        $lastPay = self::lastCutoff($payrollItem)
            // try to use last cutoff
            ?->additionItems
            ->where('addition_id', 1)
            ->first()
            ?->amount
            // if it doesn't exist or is too far back,
            // estimate by doubling current
            ?? $thisPay;

        $monthPay = $thisPay + $lastPay;
        $contribution = round($monthPay * 0.05, 2);

        if ($contribution < 500) {
            $contribution = 500;
        } elseif ($contribution > 5000) {
            $contribution = 5000;
        }

        $philhealthDeduction->amount = $contribution;
        $philhealthDeduction->save();
        $payrollItem->load('deductionItems');
    }

    private static function calculateSss(PayrollItem $payrollItem): void
    {
        $sssDeduction = $payrollItem->deductionItems
            ->where('deduction_id', 2)
            ->first();

        if (is_null($sssDeduction)) {
            return;
        }

        $thisPay = $payrollItem->additionItems
            ->whereIn('addition_id', [
                1,  // salary
                2,  // deminimis
                5,  // honorarium
            ])
            ->reduce(function (?int $carry, ?AdditionItem $item) {
                return $carry + $item->amount;
            });

        $lastPay = self::lastCutoff($payrollItem)
            // try to use last cutoff
            ?->whereIn('addition_id', [
                1,  // salary
                2,  // deminimis
                5,  // honorarium
            ])
            ->reduce(function (?int $carry, ?AdditionItem $item) {
                return $carry + $item->amount;
            })
            // if it doesn't exist or is too far back,
            // estimate by doubling current
            ?? $thisPay;

        $monthPay = $thisPay + $lastPay;

        $bracket = collect(self::$sssBrackets)
            ->where('bracket', '<', $monthPay)
            ->sortByDesc('employee_contribution')
            ->first()
            ?? self::$sssBrackets[0];

        $sssDeduction->amount = $bracket['employee_contribution'];
        $sssDeduction->save();
        $payrollItem->load('deductionItems');
    }

    private static function lastCutoff(PayrollItem $payrollItem): ?PayrollItem
    {
        return PayrollItem::where('user_id', $payrollItem->user_id)
            ->whereHas('payrollPeriod', function (Builder $query) use ($payrollItem) {
                // limit search to 1 month
                $query->where('end_date', '>=', $payrollItem->payrollPeriod->start_date)
                    // get a previous cutoff
                    ->where('end_date', '<', $payrollItem->payrollPeriod->end_date)
                    ->orderBy('end_date');
            })
            ->first();
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

    private static $taxBrackets = [
        ['bracket' => 0, 'baseTax' => 0, 'excessRate' => 0],
        ['bracket' => 250000, 'baseTax' => 0, 'excessRate' => 0.15],
        ['bracket' => 400000, 'baseTax' => 22500, 'excessRate' => 0.20],
        ['bracket' => 800000, 'baseTax' => 102500, 'excessRate' => 0.25],
        ['bracket' => 2000000, 'baseTax' => 402500, 'excessRate' => 0.30],
        ['bracket' => 8000000, 'baseTax' => 2202500, 'excessRate' => 0.35],
    ];

    private static $sssBrackets = [
        ['bracket' => 1000.00, 'employer_contribution' => 390.00, 'employee_contribution' => 180.00, 'total' => 570.00],
        ['bracket' => 4250.00, 'employer_contribution' => 437.50, 'employee_contribution' => 202.50, 'total' => 640.00],
        ['bracket' => 4750.00, 'employer_contribution' => 485.00, 'employee_contribution' => 225.00, 'total' => 710.00],
        ['bracket' => 5250.00, 'employer_contribution' => 532.50, 'employee_contribution' => 247.50, 'total' => 780.00],
        ['bracket' => 5750.00, 'employer_contribution' => 580.00, 'employee_contribution' => 270.00, 'total' => 850.00],
        ['bracket' => 6250.00, 'employer_contribution' => 627.50, 'employee_contribution' => 292.50, 'total' => 920.00],
        ['bracket' => 6750.00, 'employer_contribution' => 675.00, 'employee_contribution' => 315.00, 'total' => 990.00],
        ['bracket' => 7250.00, 'employer_contribution' => 722.50, 'employee_contribution' => 337.50, 'total' => 1060.00],
        ['bracket' => 7750.00, 'employer_contribution' => 770.00, 'employee_contribution' => 360.00, 'total' => 1130.00],
        ['bracket' => 8250.00, 'employer_contribution' => 817.50, 'employee_contribution' => 382.50, 'total' => 1200.00],
        ['bracket' => 8750.00, 'employer_contribution' => 865.00, 'employee_contribution' => 405.00, 'total' => 1270.00],
        ['bracket' => 9250.00, 'employer_contribution' => 912.50, 'employee_contribution' => 427.50, 'total' => 1340.00],
        ['bracket' => 9750.00, 'employer_contribution' => 960.00, 'employee_contribution' => 450.00, 'total' => 1410.00],
        ['bracket' => 10250.00, 'employer_contribution' => 1007.50, 'employee_contribution' => 472.50, 'total' => 1480.00],
        ['bracket' => 10750.00, 'employer_contribution' => 1055.00, 'employee_contribution' => 495.00, 'total' => 1550.00],
        ['bracket' => 11250.00, 'employer_contribution' => 1102.50, 'employee_contribution' => 517.50, 'total' => 1620.00],
        ['bracket' => 11750.00, 'employer_contribution' => 1150.00, 'employee_contribution' => 540.00, 'total' => 1690.00],
        ['bracket' => 12250.00, 'employer_contribution' => 1197.50, 'employee_contribution' => 562.50, 'total' => 1760.00],
        ['bracket' => 12750.00, 'employer_contribution' => 1245.00, 'employee_contribution' => 585.00, 'total' => 1830.00],
        ['bracket' => 13250.00, 'employer_contribution' => 1292.50, 'employee_contribution' => 607.50, 'total' => 1900.00],
        ['bracket' => 13750.00, 'employer_contribution' => 1340.00, 'employee_contribution' => 630.00, 'total' => 1970.00],
        ['bracket' => 14250.00, 'employer_contribution' => 1387.50, 'employee_contribution' => 652.50, 'total' => 2040.00],
        ['bracket' => 14750.00, 'employer_contribution' => 1435.00, 'employee_contribution' => 675.00, 'total' => 2110.00],
        ['bracket' => 15250.00, 'employer_contribution' => 1482.50, 'employee_contribution' => 697.50, 'total' => 2180.00],
        ['bracket' => 15750.00, 'employer_contribution' => 1550.00, 'employee_contribution' => 720.00, 'total' => 2270.00],
        ['bracket' => 16250.00, 'employer_contribution' => 1597.50, 'employee_contribution' => 742.50, 'total' => 2340.00],
        ['bracket' => 16750.00, 'employer_contribution' => 1645.00, 'employee_contribution' => 765.00, 'total' => 2410.00],
        ['bracket' => 17250.00, 'employer_contribution' => 1692.50, 'employee_contribution' => 787.50, 'total' => 2480.00],
        ['bracket' => 17750.00, 'employer_contribution' => 1740.00, 'employee_contribution' => 810.00, 'total' => 2550.00],
        ['bracket' => 18250.00, 'employer_contribution' => 1787.50, 'employee_contribution' => 832.50, 'total' => 2620.00],
        ['bracket' => 18750.00, 'employer_contribution' => 1835.00, 'employee_contribution' => 855.00, 'total' => 2690.00],
        ['bracket' => 19250.00, 'employer_contribution' => 1882.50, 'employee_contribution' => 877.50, 'total' => 2760.00],
        ['bracket' => 19750.00, 'employer_contribution' => 1930.00, 'employee_contribution' => 900.00, 'total' => 2830.00],
        ['bracket' => 20250.00, 'employer_contribution' => 1977.50, 'employee_contribution' => 922.50, 'total' => 2900.00],
        ['bracket' => 20750.00, 'employer_contribution' => 2025.00, 'employee_contribution' => 945.00, 'total' => 2970.00],
        ['bracket' => 21250.00, 'employer_contribution' => 2072.50, 'employee_contribution' => 967.50, 'total' => 3040.00],
        ['bracket' => 21750.00, 'employer_contribution' => 2120.00, 'employee_contribution' => 990.00, 'total' => 3110.00],
        ['bracket' => 22250.00, 'employer_contribution' => 2167.50, 'employee_contribution' => 1012.50, 'total' => 3180.00],
        ['bracket' => 22750.00, 'employer_contribution' => 2215.00, 'employee_contribution' => 1035.00, 'total' => 3250.00],
        ['bracket' => 23250.00, 'employer_contribution' => 2262.50, 'employee_contribution' => 1057.50, 'total' => 3320.00],
        ['bracket' => 23750.00, 'employer_contribution' => 2310.00, 'employee_contribution' => 1080.00, 'total' => 3390.00],
        ['bracket' => 24250.00, 'employer_contribution' => 2357.50, 'employee_contribution' => 1102.50, 'total' => 3460.00],
        ['bracket' => 24750.00, 'employer_contribution' => 2405.00, 'employee_contribution' => 1125.00, 'total' => 3530.00],
        ['bracket' => 25250.00, 'employer_contribution' => 2452.50, 'employee_contribution' => 1147.50, 'total' => 3600.00],
        ['bracket' => 25750.00, 'employer_contribution' => 2500.00, 'employee_contribution' => 1170.00, 'total' => 3670.00],
        ['bracket' => 26250.00, 'employer_contribution' => 2547.50, 'employee_contribution' => 1192.50, 'total' => 3740.00],
        ['bracket' => 26750.00, 'employer_contribution' => 2595.00, 'employee_contribution' => 1215.00, 'total' => 3810.00],
        ['bracket' => 27250.00, 'employer_contribution' => 2642.50, 'employee_contribution' => 1237.50, 'total' => 3880.00],
        ['bracket' => 27750.00, 'employer_contribution' => 2690.00, 'employee_contribution' => 1260.00, 'total' => 3950.00],
        ['bracket' => 28250.00, 'employer_contribution' => 2737.50, 'employee_contribution' => 1282.50, 'total' => 4020.00],
        ['bracket' => 28750.00, 'employer_contribution' => 2785.00, 'employee_contribution' => 1305.00, 'total' => 4090.00],
        ['bracket' => 29250.00, 'employer_contribution' => 2832.50, 'employee_contribution' => 1327.50, 'total' => 4160.00],
        ['bracket' => 29750.00, 'employer_contribution' => 2880.00, 'employee_contribution' => 1350.00, 'total' => 4230.00],
    ];
}
