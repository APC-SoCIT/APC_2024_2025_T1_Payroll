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

        $lookup = collect([
            [0, 0, 0, 0],
            [1000, 390, 180, 570],
            [4250, 437.50, 202.50, 640],
            [4750, 485, 225, 710],
            [5250, 532.50, 247.50, 780],
            [5750, 580, 270, 850],
            [6250, 627.50, 292.50, 920],
            [6750, 675, 315, 990],
            [7250, 722.50, 337.50, 1060],
            [7750, 770, 360, 1130],
            [8250, 817.50, 382.50, 1200],
            [8750, 865, 405, 1270],
            [9250, 912.50, 427.50, 1340],
            [9750, 960, 450, 1410],
            [10250, 1007.50, 472.50, 1480],
            [10750, 1055, 495, 1550],
            [11250, 1102.50, 517.50, 1620],
            [11750, 1150, 540, 1690],
            [12250, 1197.50, 562.50, 1760],
            [12750, 1245, 585, 1830],
            [13250, 1292.50, 607.50, 1900],
            [13750, 1340, 630, 1970],
            [14250, 1387.50, 652.50, 2040],
            [14750, 1435, 675, 2110],
            [15250, 1482.50, 697.50, 2180],
            [15750, 1550, 720, 2270],
            [16250, 1597.50, 742.50, 2340],
            [16750, 1645, 765, 2410],
            [17250, 1692.50, 787.50, 2480],
            [17750, 1740, 810, 2550],
            [18250, 1787.50, 832.50, 2620],
            [18750, 1835, 855, 2690],
            [19250, 1882.50, 877.50, 2760],
            [19750, 1930, 900, 2830],
            [20250, 1977.50, 922.50, 2900],
            [20750, 2025, 945, 2970],
            [21250, 2072.50, 967.50, 3040],
            [21750, 2120, 990, 3110],
            [22250, 2167.50, 1012.50, 3180],
            [22750, 2215, 1035, 3250],
            [23250, 2262.50, 1057.50, 3320],
            [23750, 2310, 1080, 3390],
            [24250, 2357.50, 1102.50, 3460],
            [24750, 2405, 1125, 3530],
            [25250, 2452.50, 1147.50, 3600],
            [25750, 2500, 1170, 3670],
            [26250, 2547.50, 1192.50, 3740],
            [26750, 2595, 1215, 3810],
            [27250, 2642.50, 1237.50, 3880],
            [27750, 2690, 1260, 3950],
            [28250, 2737.50, 1282.50, 4020],
            [28750, 2785, 1305, 4090],
            [29250, 2832.50, 1327.50, 4160],
            [29750, 2880, 1350, 4230],
        ]);

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

        $bracket = $lookup->where('bracket', '<', $monthPay)
            ->sortByDesc(0)
            ->first()
            ?? $lookup[0];

        $sssDeduction->amount = $bracket[2];
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
}
