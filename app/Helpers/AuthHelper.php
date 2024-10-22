<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class AuthHelper
{
    public static function isAuthorized(): bool
    {
        return self::isRole('hr') || self::isRole('payroll');
    }

    public static function isRole(string $role): bool
    {
        return Auth::check() ? in_array(Auth::user()->email, config('roles.'.$role.'_accounts')) : false;
    }
}
