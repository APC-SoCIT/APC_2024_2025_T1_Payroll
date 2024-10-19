<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Accounts', [
            'accounts' => User::orderBy('name')->get(),
        ]);
    }

    public function getFromCutoff(PayrollPeriod $cutoff): Response
    {
        return Inertia::render('Accounts', [
            'accounts' => User::whereHas('payrollItems', function (Builder $query) use($cutoff) {
                $query->where('payroll_period_id', $cutoff->id);
            })->orderBy('name')->get(),
            'cutoff' => $cutoff,
        ]);
    }

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        User::create($validated);
        return redirect(route('accounts'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Profile/Edit', [
            'targetAccount' => $user,
        ]);
    }

    public function add(): Response
    {
        return Inertia::render('Profile/Add');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $user->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'active' => ['required', 'boolean'],
        ]));

        return Redirect::route('accounts');
    }
}
