<?php

namespace App\Http\Middleware;

use App\Helpers\AuthHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'isHr' => AuthHelper::isHr(),
                'isPayroll' => AuthHelper::isPayroll(),
                'isAuthorized' => AuthHelper::isAuthorized(),
            ],
            'date' => Carbon::now()->toDateString(),
        ];
    }
}
