<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccountsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect(route('dashboard'));
        }

        $user = Auth::user();

        if (in_array($user->email, config('roles.hr_accounts'))) {
            return $next($request);
        }

        if (in_array($user->email, config('roles.payroll_accounts'))) {
            return $next($request);
        }

        return redirect(route('dashboard'));
    }
}
