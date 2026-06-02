<?php

namespace App\Http\Middleware;

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
        $user = $request->user();
        $school = \App\Support\TenantContext::school();

        return [
            ...parent::share($request),
            'appName' => config('platform.name'),
            'auth' => [
                'user' => $user,
                'roles' => fn () => $user?->getRoleNames()->values() ?? [],
                'permissions' => fn () => $user?->getAllPermissions()->pluck('name')->values() ?? [],
            ],
            'school' => fn () => $school?->only(['id', 'name', 'slug', 'status']),
            'flash' => fn () => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
