<?php

namespace App\Helpers;

use App\Enums\RoleId;
use App\Models\PayrollItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function isAdmin(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = User::find(Auth::user()->id);
        return $user->userRoles->contains('role_id', RoleId::Admin->value)
            || in_array(Auth::user()->email, config('roles.admin_accounts'));
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

    public static function owns(PayrollItem $item): bool
    {
        if (! Auth::check()) {
            return false;
        }

        return $item?->user_id == Auth::user()->id;
    }
}
