<?php

namespace App\Http\Middleware;

use App\Helpers\AuthHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$params): Response
    {
        $params = collect($params);
        if ($params->contains('admin')
            && AuthHelper::isAdmin()) {
            return $next($request);
        }

        if ($params->contains('payroll')
            && AuthHelper::isPayroll()) {
            return $next($request);
        }

        if ($params->contains('hr')
            && AuthHelper::isHr()) {
            return $next($request);
        }

        return redirect(route('dashboard'));
    }
}
