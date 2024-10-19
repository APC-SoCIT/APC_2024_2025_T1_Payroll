<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class PayrollPeriodController extends Controller
{
    public function index(): Response {
        return Inertia::render('Payroll/Periods', [
            'cutoffs' => PayrollPeriod::orderBy('end_date')->get(),
        ]);
    }

    public function add(): Response {
        return Inertia::render('Payroll/CreatePeriod');
    }

    public function store(Request $request): RedirectResponse {
        $validator = self::makeCutoffValidator($request);

        PayrollPeriod::create($validator->validate());
        return redirect(route('cutoffs'));
    }

    public function get(PayrollPeriod $cutoff): Response {
        return Inertia::render('Payroll/EditPeriod', [
            'cutoff' => $cutoff,
        ]);
    }

    public function update(PayrollPeriod $cutoff, Request $request): RedirectResponse {
        $validator = self::makeCutoffValidator($request);

        $cutoff->update($validator->validate());
        return redirect(route('cutoffs'));
    }

    public function delete(PayrollPeriod $cutoff): RedirectResponse {
        if ($cutoff->end_date < Carbon::now()->toDateString()) {
            abort(403);
        }

        $cutoff->delete();
        return redirect(route('cutoffs'));
    }

    private static function makeCutoffValidator(Request $request): \Illuminate\Validation\Validator {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'cutoff_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $validator->after(function($validator) {
            $start = $validator->getValue('start_date');
            $end = $validator->getValue('end_date');
            $cutoff = $validator->getValue('cutoff_date');
            $now = Carbon::now()->toDateString();

            if ($end < $now) {
                $validator->errors()->add('end_date', 'End date must be in the future');
            }
            if ($end < $start) {
                $validator->errors()->add('end_date', 'End date must be after the start date');
            }
            if ($cutoff < $start || $cutoff > $end) {
                $validator->errors()->add('cutoff_date', 'Cutoff date must be in between start and end dates');
            }
        });

        return $validator;
    }
}
