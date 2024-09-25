<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        User::create($validated);
        return redirect(route('accounts'));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, int $id): Response
    {
        $targetAccount = User::findOrFail($id);

        return Inertia::render('Profile/Edit', [
            'targetAccount' => $targetAccount,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $targetAccount = User::findOrFail($id);

        $targetAccount->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($targetAccount->id)],
        ]));

        return Redirect::route('accounts');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        $user->delete();

        return Redirect::to('/');
    }
}
