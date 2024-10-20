<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\UserVariableItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'accounts' => $cutoff->hasEnded()
                ? User::whereHas('payrollItems', function (Builder $query) use ($cutoff) {
                    $query->where('payroll_period_id', $cutoff->id);
                })->orderBy('name')->get()
                : User::orderBy('name')->get(),
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
            'targetAccount' => $user->load('userVariableItems.userVariable'),
            'userVariables' => UserVariable::all(),
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

    public function addVariable(User $user, UserVariable $variable): RedirectResponse
    {
        UserVariableItem::updateOrCreate([
            'user_id' => $user->id,
            'user_variable_id' => $variable->id,
        ], [
            'user_id' => $user->id,
            'user_variable_id' => $variable->id,
            'value' => 0,
        ]);

        return Redirect::route('profile.edit', $user->id);
    }

    public function updateVariable(UserVariableItem $variableItem, Request $request): RedirectResponse
    {
        if ($variableItem->userVariable->id == 1
            && ! in_array(Auth::user()->email, config('roles.payroll_accounts'))) {
            abort(403);
        }

        $variableItem->update(
            $request->validate([
                'value' => ['required', 'numeric', 'min:0'],
            ])
        );

        return Redirect::route('profile.edit', $variableItem->user->id);
    }

    public function deleteVariable(UserVariableItem $variableItem): RedirectResponse
    {
        // base pay should be protected
        if ($variableItem->userVariable->id == 1) {
            abort(403);
        }

        $user = $variableItem->user;
        $variableItem->delete();

        return Redirect::route('profile.edit', $user->id);
    }
}
