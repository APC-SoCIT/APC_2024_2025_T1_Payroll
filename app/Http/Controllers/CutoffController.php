<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Helpers\AuthHelper;
use App\Models\PayrollItem;
use App\Models\Cutoff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for dealing with payroll cutoffs
 *
 * @see { App\Http\Controllers\PayrollController } for payroll entries
 */
class CutoffController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Payroll/Periods', [
            'cutoffs' => Cutoff::latest('end_date')->get(),
        ]);
    }

    public function getFromUser(User $user): Response
    {
        // all the user is involved with (including past)
        $involved = $user->payrollItems
            ->map(function (?PayrollItem $item) {
                return $item->cutoff;
            });

        // if the user is active, include current and future too
        // (only authorized can see)
        if ($user->active
            && AuthHelper::isAuthorized()) {
            $involved->merge(
                Cutoff::where('end_date', '>=', Carbon::now()->toDateString())
            );
        }

        return Inertia::render('Payroll/Periods', [
            'cutoffs' => $involved,
            'account' => $user,
        ]);
    }

    public function getOwn(): Response
    {
        return $this->getFromUser(User::find(Auth::user()->id));
    }

    public function add(): Response
    {
        return Inertia::render('Payroll/CreatePeriod', [
            'cutoff' => PayrollHelper::currentPeriod(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = self::makeCutoffValidator($request);

        Cutoff::create($validator->validate());

        return redirect(route('cutoffs'));
    }

    public function get(Cutoff $cutoff): Response
    {
        return Inertia::render('Payroll/EditPeriod', [
            'cutoff' => $cutoff,
        ]);
    }

    public function update(Cutoff $cutoff, Request $request): void
    {
        $validator = self::makeCutoffValidator($request);

        $cutoff->update($validator->validate());
    }

    public function delete(Cutoff $cutoff): void
    {
        if ($cutoff->end_date < Carbon::now()->toDateString()) {
            abort(403);
        }

        $cutoff->delete();
    }

    private static function makeCutoffValidator(Request $request): \Illuminate\Validation\Validator
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'cutoff_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $validator->after(function ($validator) {
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