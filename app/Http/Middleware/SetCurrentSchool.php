<?php

namespace App\Http\Middleware;

use App\Enums\SchoolStatus;
use App\Services\TenantResolver;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentSchool
{
    public function __construct(
        protected TenantResolver $tenantResolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $school = $this->tenantResolver->resolve($request);

        if ($school === null) {
            if ($request->route('tenant') !== null) {
                abort(404, 'Sekolah tidak ditemukan.');
            }

            return $next($request);
        }

        if ($school->status === SchoolStatus::Suspended) {
            abort(403, 'Akses sekolah ditangguhkan. Hubungi Platform Sekolah.');
        }

        if (! $school->status->isAccessible() && $school->status !== SchoolStatus::Suspended) {
            abort(403, 'Langganan sekolah tidak aktif.');
        }

        TenantContext::set($school);
        setPermissionsTeamId($school->id);

        try {
            return $next($request);
        } finally {
            TenantContext::clear();
        }
    }
}
