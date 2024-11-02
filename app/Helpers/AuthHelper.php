<?php

namespace App\Helpers;

use App\Enums\RoleId;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function isAuthorized(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = User::find(Auth::user()->id);
        return $user->userRoles
            ->map(function (UserRole $userRole) {
                return $userRole->role_id;
            })
            ?->intersect([
                RoleId::Payroll->value,
                RoleId::Hr->value,
            ])
            ->isNotEmpty()
            ?? false;
    }

    public static function isAdmin(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return in_array(Auth::user()->email, config('roles.admin_accounts'));
    }

    public static function isPayroll(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = User::find(Auth::user()->id);
        return $user->userRoles->contains('role_id', RoleId::Payroll->value);
    }

    public static function isHr(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = User::find(Auth::user()->id);
        return $user->userRoles->contains('role_id', RoleId::Hr->value);
    }
}
