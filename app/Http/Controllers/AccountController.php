<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index() {
        return Inertia::render('Accounts', [
            'accounts' => User::orderBy('name')->get(),
        ]);
    }
}
