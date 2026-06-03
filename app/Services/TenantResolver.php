<?php

namespace App\Services;

use App\Enums\SchoolStatus;
use App\Models\School;
use Illuminate\Http\Request;

class TenantResolver
{
    public function resolve(Request $request): ?School
    {
        $slug = $this->resolveSlug($request);

        if ($slug === null) {
            return null;
        }

        return School::query()
            ->where('slug', $slug)
            ->whereIn('status', [
                SchoolStatus::Trial,
                SchoolStatus::Active,
                SchoolStatus::Suspended,
                SchoolStatus::Expired,
            ])
            ->first();
    }

    protected function resolveSlug(Request $request): ?string
    {
        if ($tenant = $request->route('tenant')) {
            return (string) $tenant;
        }

        $host = $request->getHost();
        $base = config('platform.tenant_base_domain');

        if (! str_ends_with($host, '.'.$base) && $host !== $base) {
            return null;
        }

        if ($host === $base) {
            return null;
        }

        $slug = str_replace('.'.$base, '', $host);

        if (in_array($slug, ['www', 'admin', ''], true)) {
            return null;
        }

        return $slug;
    }
}
