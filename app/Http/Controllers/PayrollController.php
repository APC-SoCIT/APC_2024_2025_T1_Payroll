<?php

namespace App\Http\Controllers;

use App\Enums\DeductionId;
use App\Helpers\AuthHelper;
use App\Helpers\PayrollHelper;
use App\Models\Addition;
use App\Models\Cutoff;
use App\Models\Deduction;
use App\Models\ItemAddition;
use App\Models\ItemDeduction;
use App\Models\PayrollItem;
use App\Models\User;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response as FacadesResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

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

        return self::getItem($currentPeriod, $user);
    }

    public function getItem(Cutoff $cutoff, User $user): Response
    {
        $payrollItem = null;
        if (AuthHelper::isPayroll()) {
            if (! $cutoff->hasStarted()) {
                abort(403);
            }

            if ($cutoff->hasEnded()) {
                $payrollItem = PayrollItem::whereUserId($user->id)
                    ->whereCutoffId($cutoff->id)
                    ->first();

                if (is_null($payrollItem)) {
                    abort(404);
                }
            } else {
                $payrollItem = PayrollItem::firstOrCreate([
                    'user_id' => $user->id,
                    'cutoff_id' => $cutoff->id,
                ]);
            }
        } else {
            if ($user->id != Auth::id()) {
                abort(403);
            }

            if ($cutoff->end >= Carbon::now()->toDateString()) {
                abort(403);
            }

            $payrollItem = PayrollItem::where('user_id', $user->id)
                ->where('cutoff_id', $cutoff->id)
                ->first();

            if (is_null($payrollItem)) {
                abort(404);
            }
        }

        if (! $payrollItem->cutoff->hasEnded()) {
            PayrollHelper::calculateAll($payrollItem);
        }

        // load relationships
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

    public function deleteItem(Cutoff $cutoff, User $user): RedirectResponse
    {
        if ($cutoff->start_date > Carbon::now()->toDateString()) {
            abort(403);
        }

        $payrollItem = PayrollItem::where([
                'user_id' => $user->id,
                'cutoff_id' => $cutoff->id,
            ])
            ->first();

        if (is_null($payrollItem)
            || $payrollItem->cutoff->hasEnded()) {
            abort(403);
        }

        $payrollItem->delete();
        return redirect(route('cutoffs'));
    }

    public function addItemAddition(Cutoff $cutoff, User $user, Addition $addition): void
    {
        $payrollItem = PayrollItem::whereCutoffId($cutoff->id)
            ->whereUserId($user->id)
            ->first();

        if (is_null($payrollItem)
            || $payrollItem->cutoff->hasEnded()
            || ! $payrollItem->cutoff->hasStarted()) {
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
            || ! $itemAddition->payrollItem->cutoff->hasStarted()
            || $itemAddition->addition->calculated) {
            abort(403);
        }

        $rules = [ 'amount' => ['required', 'numeric', 'min:0' ] ];
        if ($itemAddition->addition->hour_based) {
            $rules['hours'] = ['required', 'integer', 'min:0'];
            $rules['minutes'] = ['required', 'integer', 'min:0'];
        }

        $validated = $request->validate($rules);
        if ($itemAddition->addition->hour_based) {
            $itemAddition->hours = $validated['hours'];
            $itemAddition->minutes = $validated['minutes'];
        } else {
            $itemAddition->amount = round($validated['amount'], 2);
        }

        $itemAddition->save();
        PayrollHelper::calculateAll($itemAddition->payrollItem->load('itemAdditions'));
    }

    public function deleteItemAddition(ItemAddition $itemAddition): void
    {
        if ($itemAddition->payrollItem->cutoff->hasEnded()
            || ! $itemAddition->payrollItem->cutoff->hasStarted()
            || $itemAddition->addition->required) {
            abort(403);
        }

        $payrollItem = $itemAddition->payrollItem;
        $itemAddition->delete();

        PayrollHelper::calculateAll($payrollItem->load('itemAdditions'));
    }

    public function addItemDeduction(Cutoff $cutoff, User $user, Deduction $deduction): void
    {
        $payrollItem = PayrollItem::whereCutoffId($cutoff->id)
            ->whereUserId($user->id)
            ->first();

        if (is_null($payrollItem)
            || $payrollItem->cutoff->hasEnded()
            || ! $payrollItem->cutoff->hasStarted()) {
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
            || ! $itemDeduction->payrollItem->cutoff->hasStarted()
            || $itemDeduction->deduction->calculated) {
            abort(403);
        }

        $min = $itemDeduction->deduction_id == DeductionId::Pagibig->value ? '100' : '0';
        $rules = [ 'amount' => ['required', 'numeric', 'min:' .  $min] ];
        if ($itemDeduction->deduction->has_deadline) {
            $rules['remaining_payments'] = ['required', 'numeric', 'min:0'];
        }

        if ($itemDeduction->deduction->hour_based) {
            $rules['hours'] = ['required', 'integer', 'min:0'];
            $rules['minutes'] = ['required', 'integer', 'min:0'];
        }

        $validated = $request->validate($rules);

        if ($itemDeduction->deduction->has_deadline) {
            $itemDeduction->remaining_payments = $validated['remaining_payments'];
        }

        if ($itemDeduction->deduction->hour_based) {
            $itemDeduction->hours = $validated['hours'];
            $itemDeduction->minutes = $validated['minutes'];
        } else {
            $itemDeduction->amount = round($validated['amount'], 2);
        }

        $itemDeduction->save();
        PayrollHelper::calculateAll($itemDeduction->payrollItem->load('itemDeductions'));
    }

    public function deleteItemDeduction(ItemDeduction $itemDeduction): void
    {
        if ($itemDeduction->payrollItem->cutoff->hasEnded()
            || ! $itemDeduction->payrollItem->cutoff->hasStarted()
            || $itemDeduction->deduction->required) {
            abort(403);
        }

        $payrollItem = $itemDeduction->payrollItem;
        $itemDeduction->delete();

        PayrollHelper::calculateAll($payrollItem->load('itemDeductions'));
    }

    public function exportCutoffData(Cutoff $cutoff): HttpFoundationResponse
    {
        $fileName = "{$cutoff->end_date}_export.csv";
        $file = fopen($fileName, 'w');

        $additions = Addition::all()->pluck('name');
        $deductions = Deduction::all()->pluck('name');

        $additionCount = $additions->count();
        $deductionCount = $deductions->count();

        $headers = [
            'BDO Account Number',
            'Name',
            'Total',
            ...$additions,
            ...$deductions,
        ];
        fputcsv($file, $headers);

        foreach ($cutoff->payrollItems as $item) {
            $itemAdditions = array_fill(1, $additionCount, '');
            $itemDeductions = array_fill(1, $deductionCount, '');

            foreach ($item->itemAdditions as $itemAddition) {
                $itemAdditions[$itemAddition->addition->id] = $itemAddition->amount;
            }

            foreach ($item->itemDeductions as $itemDeduction) {
                $itemDeductions[$itemDeduction->deduction->id] = $itemDeduction->amount;
            }

            $row = [
                "'{$item->user->bank_account_number}",
                $item->user->name,
                $item->amount,
                ...$itemAdditions,
                ...$itemDeductions,
            ];

            fputcsv($file, $row);
        }

        fclose($file);
        return FacadesResponse::download(public_path($fileName))->deleteFileAfterSend(true);
    }

    public function exportPdf(Cutoff $cutoff, User $user): HttpResponse
    {
        $item = PayrollItem::whereCutoffId($cutoff->id)
            ->whereUserId($user->id)
            ->first();

        if (! AuthHelper::owns($item)
            && ! AuthHelper::isPayroll()) {
            abort(403);
        }

        $pdf = Pdf::loadView('payrollItem', [
            'items' =>  [
                $item->load([
                    'user',
                    'cutoff',
                    'itemAdditions.addition',
                    'itemDeductions.deduction',
                ]),
            ],
        ]);
        return $pdf->stream('payslip.pdf');
    }

    public function exportPdfs(Cutoff $cutoff): HttpResponse
    {
        $items = PayrollItem::with([
            'user',
            'cutoff',
            'itemAdditions.addition',
            'itemDeductions.deduction',
        ])
            ->whereCutoffId($cutoff->id)
            ->get();

        $pdf = Pdf::loadView('payrollItem', [
            'items' => $items->sortBy('user.name')
        ]);

        return $pdf->stream('payslip.pdf');
    }
}
