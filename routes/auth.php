<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use App\Models\Variable;
use App\Models\UserVariable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

Route::middleware('guest')->group(function () {
    Route::get('auth/redirect', function () {
        return Inertia::location(Socialite::driver('azure')->redirect());
    })->name('auth.redirect');

    Route::get('auth/callback', function () {
        $azureUser = Socialite::driver('azure')->user();

        $user = User::updateOrCreate([
            'email' => $azureUser->mail,
        ], [
            'name' => $azureUser->name,
        ]);

        UserVariable::firstOrCreate([
            'user_id' => $user->id,
            'variable_id' => 1,
        ], [
            'value' => 0,
        ]);

        UserVariable::firstOrCreate([
            'user_id' => $user->id,
            'variable_id' => 2,
        ], [
            'value' => Variable::find(2)->min,
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    });
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
