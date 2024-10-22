<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\UserVariableItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                // if past, only related accounts
                ? $cutoff->users->sort('name')
                // if current/future, only active accounts
                : User::where('active', true)
                    ->orderBy('name')
                    ->get(),
            'cutoff' => $cutoff,
        ]);
    }

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = User::create($validated);

        UserVariableItem::updateOrCreate([
            'user_id' => $user->id,
            'user_variable_id' => 1,
        ], [
            'value' => 0,
        ]);

        return redirect(route('accounts'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(User $user): Response
    {
        $user ??= User::find(Auth::user()->id);

        return Inertia::render('Profile/Edit', [
            'targetAccount' => $user->load('userVariableItems.userVariable'),
            'userVariables' => UserVariable::all(),
        ]);
    }

    public function getOwn(): Response
    {
        return $this->edit(User::find(Auth::user()->id));
    }

    public function add(): Response
    {
        return Inertia::render('Profile/Add');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request, User $user): void
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'active' => ['required', 'boolean'],
        ]);

        $user->update($validated);

        if (! $validated['active']) {
            $user->payrollItems
                ->where('payrollPeriod.end_date', '>=', Carbon::now()->toDateString())
                ->each(function (?PayrollItem $item) {
                    $item->delete();
                });
        }
    }

    public function addVariable(User $user, UserVariable $variable): void
    {
        UserVariableItem::updateOrCreate([
            'user_id' => $user->id,
            'user_variable_id' => $variable->id,
        ], [
            'value' => 0,
        ]);
    }

    public function updateVariable(UserVariableItem $variableItem, Request $request): void
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
    }

    public function deleteVariable(UserVariableItem $variableItem): void
    {
        if ($variableItem->userVariable->required) {
            abort(403);
        }

        $variableItem->delete();
    }
}
