<?php

namespace App\Support;

use App\Models\User;

class PostAuthRedirect
{
    /**
     * Resolve the post-authentication / post-verification destination for a user.
     *
     * - Super-admin → platform tenants list
     * - School admin with incomplete onboarding → onboarding wizard
     * - School user otherwise → tenant dashboard
     * - Fallback → generic dashboard
     */
    public static function for(?User $user): string
    {
        if ($user === null) {
            return route('dashboard', absolute: false);
        }

        if ($user->isSuperAdmin()) {
            return route('platform.tenants.index', absolute: false);
        }

        $school = $user->school;

        if ($school === null) {
            return route('dashboard', absolute: false);
        }

        if (! $school->hasCompletedOnboarding()) {
            return route('tenant.onboarding.show', ['tenant' => $school->slug], absolute: false);
        }

        return route('tenant.dashboard', ['tenant' => $school->slug], absolute: false);
    }
}
