<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountUpdateRequest;
use App\Models\PayrollItem;
use App\Models\Cutoff;
use App\Models\User;
use App\Models\Variable;
use App\Models\UserVariable;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Accounts', [
            'accounts' => User::orderBy('name')->get(),
        ]);
    }

    public function getFromCutoff(Cutoff $cutoff): Response
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

    public function store(AccountUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = User::create($validated);

        UserVariable::updateOrCreate([
            'user_id' => $user->id,
            'variable_id' => 1,
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

        return Inertia::render('Account/Edit', [
            'targetAccount' => $user->load('userVariables.variable'),
            'userVariables' => Variable::all(),
        ]);
    }

    public function getOwn(): Response
    {
        return $this->edit(User::find(Auth::user()->id));
    }

    public function add(): Response
    {
        return Inertia::render('Account/Add');
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
                ->where('cutoff.end_date', '>=', Carbon::now()->toDateString())
                ->each(function (?PayrollItem $item) {
                    $item->delete();
                });
        }
    }

    public function addVariable(User $user, Variable $variable): void
    {
        UserVariable::updateOrCreate([
            'user_id' => $user->id,
            'variable_id' => $variable->id,
        ], [
            'value' => 0,
        ]);
    }

    public function updateVariable(UserVariable $variableItem, Request $request): void
    {
        if ($variableItem->variable->id == 1
            && ! in_array(Auth::user()->email, config('roles.payroll_accounts'))) {
            abort(403);
        }

        $variableItem->update(
            $request->validate([
                'value' => ['required', 'numeric', 'min:0'],
            ])
        );
    }

    public function deleteVariable(UserVariable $variableItem): void
    {
        if ($variableItem->variable->required) {
            abort(403);
        }

        $variableItem->delete();
    }
}
