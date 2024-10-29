<?php

namespace App\Helpers;

use App\Enums\AdditionId;
use App\Enums\DeductionId;
use App\Models\Addition;
use App\Models\Cutoff;
use App\Models\Deduction;
use App\Models\ItemAddition;
use App\Models\ItemDeduction;
use App\Models\PayrollItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Handles computations related to payroll items.
 * FIXME: Refactor using decimal maths (integers/BCMath/PHPMoney, etc.)
 */
class PayrollHelper
{
    public static function calculateAll(PayrollItem $item): void
    {
        $previous = self::lastCutoff($item);
        $previous?->load('cutoff');

        if ($item->itemAdditions->isEmpty()) {
            self::duplicateOrCreate($item, $previous);
        }

        self::calculateContributions($item, $previous);
        self::calculateTax($item);

        $totalAdditions = $item->itemAdditions
            ->where('addition_id', '!=', AdditionId::PreviousTaxable->value)
            ->sum('amount');

        $totalDeductions = $item->itemDeductions
            ->where('deduction_id', '!=', DeductionId::PreviousTaxWithheld->value)
            ->sum('amount');

        $item->amount = round($totalAdditions - $totalDeductions, 2);
        $item->save();
    }

    private static function duplicateOrCreate(PayrollItem $item, ?PayrollItem $previous)
    {
        if (is_null($previous)) {
            $requiredAdditions = Addition::whereRequired(true)->get()
                ->map(function (Addition $addition) {
                    return [
                        'addition_id' => $addition->id,
                        'amount' => 0,
                    ];
                });

            $item->itemAdditions()->createMany($requiredAdditions);

            $pagibigMin = $item->cutoff->month_end ? 200 : 100;
            $requiredDeductions = Deduction::whereRequired(true)->get()
                ->map(function (Deduction $deduction) use ($pagibigMin) {
                    return [
                        'deduction_id' => $deduction->id,
                        'amount' => $deduction->id == DeductionId::Pagibig->value ? $pagibigMin : 0,
                    ];
                });

            $item->itemDeductions()->createMany($requiredDeductions);
        } else {
            $previous->itemAdditions
                ->each(function (PayrollItem $previousItem) use ($item) {
                    $new_item = $previousItem->replicate();
                    $new_item->payroll_item_id = $item->id;
                    $new_item->save();
                });

            $previous->itemDeductions
                ->each(function (PayrollItem $previousItem) use ($item) {
                    $new_item = $previousItem->replicate();
                    $new_item->payroll_item_id = $item->id;
                    $new_item->save();
                });
        }

        $item->load(['itemAdditions.addition', 'itemDeductions.deduction']);
    }

    private static function calculateContributions(PayrollItem $item, ?PayrollItem $previous = null): void
    {
        $thisPay = $item->itemAdditions
            ->whereIn('addition_id', [
                AdditionId::Salary->value,
                AdditionId::Deminimis->value,
                AdditionId::Honorarium->value,
            ])
            ->sum('amount');

        $lastPay = $previous?->itemAdditions
            ->whereIn('addition_id', [
                AdditionId::Salary->value,
                AdditionId::Deminimis->value,
                AdditionId::Honorarium->value,
            ])
            ->sum('amount')
            ?? 0;

        self::calculateSssFromItems($item, $previous, $thisPay, $lastPay);
        self::calculatePhilhealthFromItems($item, $previous, $thisPay, $lastPay);
        self::calculatePeraaFromItems($item, $previous, $thisPay, $lastPay);
    }

    /**
     * Calculates the tax due.
     *
     * Takes into account variables that don't bump tax brackets and
     * total withheld taxes to provide a more accurate estimate.
     */
    private static function calculateTax(PayrollItem $payrollItem): void
    {
        $totalTaxableAdditions = $payrollItem->itemAdditions
            ->where('addition.taxable', true)
            ->sum('amount');

        $totalNonTaxableDeductions = $payrollItem->itemDeductions
            ->where('deduction.taxable', false)
            ->sum('amount');

        $previousIncome = $payrollItem->itemAdditions
            ->where('addition_id', AdditionId::PreviousTaxable->value)
            ->first()
            ?->amount
            ?? 0;

        // add the total taxable incomes
        $previousIncome += ItemAddition::where(function (Builder $query) {
                $query->whereHas('addition', function (Builder $query) {
                        $query->where('taxable', true);
                    })
                    // including retroactive variables
                    ->orWhere('addition_id', AdditionId::Merit->value)
                    ->orWhere('addition_id', AdditionId::SalaryAdjustment->value);
            })
            // that belong to entries
            ->whereHas('payrollItem', function (Builder $query) use ($payrollItem) {
                $query->where('user_id', $payrollItem->user_id)
                    // of previous cutoffs within the year
                    ->whereHas('cutoff', function (Builder $query) use ($payrollItem) {
                        $query->where('end_date', '<', $payrollItem->cutoff->end_date)
                            ->where('cutoff_date', '>', Carbon::createFromFormat('Y-m-d', $payrollItem->cutoff->cutoff_date)
                                ->startOfYear()
                                ->toDateString()
                        );
                    });
            })
            ->sum('amount');

        $totalTaxWithheld = $payrollItem->itemDeductions
            ->where('deduction_id', DeductionId::PreviousTaxWithheld->value)
            ->first()
            ?->amount
            ?? 0;

        // add the tax withheld
        $totalTaxWithheld += ItemDeduction::whereDeductionId(DeductionId::Tax->value)
            // that belong to entries
            ->whereHas('payrollItem', function (Builder $query) use ($payrollItem) {
                $query->where('user_id', $payrollItem->user_id)
                    // of previous cutoffs within the year
                    ->whereHas('cutoff', function (Builder $query) use ($payrollItem) {
                        $query->where('end_date', '<', $payrollItem->cutoff->end_date)
                            ->where('cutoff_date', '>', Carbon::createFromFormat('Y-m-d', $payrollItem->cutoff->cutoff_date)
                                ->startOfYear()
                                ->toDateString()
                        );
                    });
            })
            ->sum('amount');

        $remainingCutoffs = 0; // including this cutoff
        $month = Carbon::createFromFormat('Y-m-d', $payrollItem->cutoff->cutoff_date)->month;
        if ($payrollItem->cutoff->month_end) {
            $remainingCutoffs = 24 - (($month * 2) - 1);
        } else {
            $remainingCutoffs = 24 - (($month - 1) * 2);
        }

        $netBeforeTax = $totalTaxableAdditions - $totalNonTaxableDeductions;
        $yearEstimate = ($netBeforeTax * $remainingCutoffs) + $previousIncome;

        $bracket = collect(self::$taxBrackets)
            ->where('bracket', '<', $yearEstimate)
            ->sortByDesc('bracket')
            ->first()
            ?? self::$taxBrackets[0];

        $excess = $yearEstimate - $bracket['bracket'];
        // retroactive taxable variables that shouldn't bump your bracket
        $excess += $payrollItem->itemAdditions
            ->whereIn('addition_id', [
                AdditionId::Merit->value,
                AdditionId::SalaryAdjustment->value,
            ])
            ->sum('amount');

        $excessTax = $excess * $bracket['excessRate'];
        $tax = ($bracket['baseTax'] + $excessTax - $totalTaxWithheld)
            / $remainingCutoffs;
        $tax = max(0, round($tax, 2));

        ItemDeduction::updateOrCreate([
            'payroll_item_id' => $payrollItem->id,
            'deduction_id' => DeductionId::Tax->value,
        ], [
            'amount' => $tax,
        ]);

        $payrollItem->load('itemDeductions');
    }

    /**
     * Calculates the SSS contribution due.
     *
     * If at the start of the month, estimates the contribution by doubling the current pay.
     * If at the end of the month, calculates the remaining due.
     */
    private static function calculateSssFromItems(
        PayrollItem $currentItem,
        ?PayrollItem $lastItem,
        float $thisPay,
        float $lastPay
    ): void {
        $sssDeduction = $currentItem->itemDeductions
            ->where('deduction_id', DeductionId::Sss->value)
            ->first();

        $contributionDue = 0;

        if ($currentItem->cutoff->month_end) {
            $lastContribution = $lastItem
                ?->itemDeductions
                ->where('deduction_id', DeductionId::Philhealth->value)
                ->first()
                ?->amount
                ?? 0;

            $monthPay = $thisPay + $lastPay;
            $totalContribution = self::calculateSss($monthPay);
            $contributionDue = $totalContribution - $lastContribution;
        } else {
            $contributionDue = self::calculateSss($thisPay * 2) / 2;
        }

        $sssDeduction->amount = $contributionDue;
        $sssDeduction->save();
        $currentItem->load('itemDeductions');
    }

    /**
     * Calculates the PhilHealth contribution due.
     *
     * If at the start of the month, estimates the contribution by doubling the current pay.
     * If at the end of the month, calculates the remaining due.
     */
    private static function calculatePhilhealthFromItems(
        PayrollItem $currentItem,
        ?PayrollItem $lastItem,
        float $thisPay,
        float $lastPay
    ): void {
        $philhealthDeduction = $currentItem->itemDeductions
            ->where('deduction_id', DeductionId::Philhealth->value)
            ->first();

        $contributionDue = 0;
        if ($currentItem->cutoff->month_end) {
            $lastContribution = $lastItem
                ?->itemDeductions
                ->where('deduction_id', DeductionId::Philhealth->value)
                ->first()
                ?->amount
                ?? 0;

            $monthPay = $thisPay + $lastPay;
            $totalContribution = self::calculatePhilhealth($monthPay);
            $contributionDue = $totalContribution - $lastContribution;
        } else {
            $contributionDue = self::calculatePhilhealth($thisPay * 2) / 2;
        }

        $philhealthDeduction->amount = $contributionDue;
        $philhealthDeduction->save();
        $currentItem->load('itemDeductions');
    }

    /**
     * Calculates the PERAA contribution due.
     *
     * If at the start of the month, estimates the contribution by doubling the current pay.
     * If at the end of the month, calculates the remaining due.
     */
    private static function calculatePeraaFromItems(
        PayrollItem $currentItem,
        ?PayrollItem $lastItem,
        float $thisPay,
        float $lastPay
    ): void {
        $peraaDeduction = $currentItem->itemDeductions
            ->where('deduction_id', DeductionId::Peraa->value)
            ->first();

        if (is_null($peraaDeduction)) {
            return;
        }

        $contributionDue = 0;
        if ($currentItem->cutoff->month_end) {
            $lastContribution = $lastItem
                ?->itemDeductions
                ->where('deduction_id', DeductionId::Peraa->value)
                ->first()
                ?->amount
                ?? 0;

            $monthPay = $thisPay + $lastPay;
            $totalContribution = self::calculatePeraa($monthPay);
            $contributionDue = $totalContribution - $lastContribution;
        } else {
            $contributionDue = self::calculatePeraa($thisPay * 2) / 2;
        }

        $peraaDeduction->amount = $contributionDue;
        $peraaDeduction->save();
        $currentItem->load('itemDeductions');
    }

    private static function calculateSss(float $pay): float
    {
        $bracket = collect(self::$sssBrackets)
            ->where('bracket', '<', $pay)
            ->sortByDesc('employee_contribution')
            ->first()
            ?? self::$sssBrackets[0];

        return $bracket['employee_contribution'];
    }

    private static function calculatePhilhealth(float $pay): float
    {
        $contribution = round($pay * 0.025, 2);

        if ($contribution < 250) {
            return 250;
        }

        if ($contribution > 2500) {
            return 2500;
        }

        return $contribution;
    }

    private static function calculatePeraa(float $pay): float
    {
        return round($pay * 0.03, 2);
    }

    private static function lastCutoff(PayrollItem $payrollItem): ?PayrollItem
    {
        $endDate = $payrollItem->cutoff->end_date;
        $limit = Carbon::createFromFormat('Y-m-d', $endDate)->subMonth();

        return PayrollItem::where('user_id', $payrollItem->user_id)
            ->whereHas('cutoff', function (Builder $query) use ($limit, $endDate) {
                // limit search to 1 month
                $query->where('end_date', '>=', $limit)
                    // get a previous cutoff
                    ->where('end_date', '<', $endDate)
                    ->orderBy('end_date');
            })
            ->first();
    }

    /*
     * Get the current period.
     * If the latest period in the database is outdated, generate a new one.
     */
    public static function currentPeriod(): Cutoff
    {
        $now = Carbon::now();
        $nowString = $now->toDateString();

        $currentPeriod = Cutoff::where('start_date', '<=', $nowString)
            ->where('end_date', '>=', $nowString)
            ->oldest('end_date')
            ->first();

        if (is_null($currentPeriod)) {
            $start = null;
            $cutoff = null;
            $end = null;
            $month_end = false;

            if ($now->day < 16) {
                $start = $now->copy()
                    ->startOfMonth();

                $cutoff = $now->copy()
                    ->setDay(10);

                $end = $now->copy()
                    ->setDay(15);
            } else {
                $start = $now->copy()
                    ->setDay(16);

                $end = $now->copy()
                    ->endOfMonth();

                if ($now->month == 2) {
                    $cutoff = $now->copy()
                        ->setDay(23);
                } else {
                    $cutoff = $now->copy()
                        ->setDay(25);
                }

                $month_end = true;
            }

            $currentPeriod = new Cutoff;
            $currentPeriod->start_date = $start->toDateString();
            $currentPeriod->cutoff_date = $cutoff->toDateString();
            $currentPeriod->end_date = $end->toDateString();
            $currentPeriod->month_end = $month_end;
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
        ['bracket' => 0, 'employer_contribution' => 0, 'employee_contribution' => 0, 'total' => 0],
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
