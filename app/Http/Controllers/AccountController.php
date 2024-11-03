<?php

namespace App\Http\Controllers;

use App\Helpers\PayrollHelper;
use App\Http\Requests\AccountUpdateRequest;
use App\Models\Addition;
use App\Models\Cutoff;
use App\Models\Deduction;
use App\Models\PayrollItem;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
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
                ? $cutoff->payrollItems
                    ->map(function (PayrollItem $item) {
                        return $item->user;
                    })
                    ->sortBy('name')
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
        User::create($validated);

        return redirect(route('accounts'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(User $user): Response
    {
        $user ??= User::find(Auth::user()->id);

        $currentPeriod = PayrollHelper::currentPeriod();
        $currentPeriod->save();

        $payrollItem = PayrollItem::firstOrCreate([
            'user_id' => $user->id,
            'cutoff_id' => $currentPeriod->id,
        ]);

        if (! $payrollItem->cutoff->hasEnded()) {
            PayrollHelper::calculateAll($payrollItem);
        }

        // upon first creation, it's not loaded
        $payrollItem->load([
            'cutoff',
            'itemAdditions.addition',
            'itemDeductions.deduction',
        ]);

        return Inertia::render('Account/Edit', [
            'targetAccount' => $user->load('userRoles.role'),
            'payrollItem' => $payrollItem,
            'additions' => Addition::all(),
            'deductions' => Deduction::all(),
            'roles' => Role::all(),
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
            $user->payrollItems()
                ->whereHas('cutoff', function (Builder $query) {
                    $query->where('end_date', '>=', Carbon::now()->toDateString());
                })
                ->each(function (?PayrollItem $item) {
                    $item->delete();
                });
        }
    }

    public function addRole(User $user, Role $role): void
    {
        UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);
    }

    public function removeRole(UserRole $userRole): void
    {
        $userRole->delete();
    }
}
