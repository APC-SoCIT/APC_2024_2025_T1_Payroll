<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(): Response {
        return Inertia::render('Accounts', [
            'accounts' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse {
        return redirect(route('accounts.index'));
    }
}
