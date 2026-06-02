<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            setPermissionsTeamId(null);

            if (! $user->hasRole('super-admin')) {
                abort(403, 'Hanya super-admin yang dapat mengakses halaman ini.');
            }
        }

        return $next($request);
    }
}
