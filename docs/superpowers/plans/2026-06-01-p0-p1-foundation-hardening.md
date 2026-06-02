# P0/P1 Foundation Hardening Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Harden P0/P1 tenancy, routing, Inertia props, platform boundaries, and tests before P2 porting work.

**Architecture:** Keep Laravel + Inertia + Vue monolith. Use `TenantContext` and route namespaces to select tenant/platform/central routes, expose documented shared props, and keep platform admin separate from tenant app.

**Tech Stack:** Laravel 13, PHP 8.3+, Inertia Laravel, Vue 3, TypeScript, Ziggy, Spatie Permission teams, PHPUnit/Pest-compatible Laravel feature tests.

---

## File Structure

- Modify `app/Http/Middleware/HandleInertiaRequests.php`: share `school`, `auth.roles`, `auth.permissions`, `appName`, and flash messages.
- Modify `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: compute password reset availability and post-login redirects based on route context.
- Modify `app/Http/Controllers/Auth/ConfirmablePasswordController.php`: redirect tenant users back to tenant dashboard.
- Modify `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`: redirect tenant users back to tenant dashboard.
- Modify `app/Http/Controllers/Auth/EmailVerificationPromptController.php`: redirect tenant users back to tenant dashboard after verification check.
- Modify `app/Http/Controllers/Auth/RegisteredUserController.php`: keep central registration behavior; avoid tenant registration creating users without `school_id`.
- Modify `app/Http/Controllers/Auth/VerifyEmailController.php`: redirect tenant users back to tenant dashboard.
- Modify `app/Http/Controllers/Platform/TenantController.php`: reset tenant admin password using explicit Spatie team context for target school.
- Modify `resources/js/Layouts/AuthenticatedLayout.vue`: compute dashboard/profile/logout links from current route context and `school.slug`.
- Modify `resources/js/Pages/Auth/Login.vue`: avoid `route('password.request')` without required tenant param.
- Modify `resources/js/types/index.d.ts`: align page props with `school`, roles, permissions, flash, appName.
- Modify `tests/Feature/Platform/TenantIsolationTest.php`: strengthen cross-tenant and shared prop assertions.
- Modify `tests/Feature/Platform/PlatformPathRoutesTest.php`: add tenant login redirect and route-render tests.
- Modify `tests/Feature/Platform/SuperAdminCanListTenantsTest.php`: add platform login rejection test for tenant admin.
- Modify `docs/plans/04-development-plan.md`: update P0/P1 status notes after hardening.

---

### Task 1: Shared Inertia Props

**Files:**
- Modify: `app/Http/Middleware/HandleInertiaRequests.php`
- Test: `tests/Feature/Platform/TenantIsolationTest.php`

- [ ] **Step 1: Add failing shared-props test**

Add this method to `tests/Feature/Platform/TenantIsolationTest.php`:

```php
public function test_tenant_dashboard_receives_school_shared_prop(): void
{
    $this->seed();

    $demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();

    $response = $this->actingAs($demoAdmin)->get('/t/demo/dashboard');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('school.slug', 'demo')
        ->where('school.name', 'SD Demo Platform')
        ->where('appName', config('platform.name'))
        ->has('auth.user')
        ->has('auth.roles')
        ->has('auth.permissions'));
}
```

- [ ] **Step 2: Run test to verify it fails**

Run:

```bash
php artisan test --filter=TenantIsolationTest::test_tenant_dashboard_receives_school_shared_prop
```

Expected: FAIL because `school`, roles, permissions, or appName are missing.

- [ ] **Step 3: Update shared props**

Replace the `share()` method in `app/Http/Middleware/HandleInertiaRequests.php` with:

```php
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
```

- [ ] **Step 4: Run test to verify it passes**

Run:

```bash
php artisan test --filter=TenantIsolationTest::test_tenant_dashboard_receives_school_shared_prop
```

Expected: PASS.

---

### Task 2: Tenant-Aware Auth Redirects

**Files:**
- Modify: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Modify: `app/Http/Controllers/Auth/ConfirmablePasswordController.php`
- Modify: `app/Http/Controllers/Auth/EmailVerificationNotificationController.php`
- Modify: `app/Http/Controllers/Auth/EmailVerificationPromptController.php`
- Modify: `app/Http/Controllers/Auth/VerifyEmailController.php`
- Modify: `app/Http/Controllers/Auth/RegisteredUserController.php`
- Test: `tests/Feature/Platform/PlatformPathRoutesTest.php`

- [ ] **Step 1: Add failing tenant login redirect test**

Add this method to `tests/Feature/Platform/PlatformPathRoutesTest.php`:

```php
public function test_tenant_login_redirects_to_tenant_dashboard(): void
{
    $this->seed();

    $response = $this->post('/t/demo/login', [
        'email' => 'admin@demo.test',
        'password' => 'password',
    ]);

    $response->assertRedirect('/t/demo/dashboard');
}
```

- [ ] **Step 2: Add failing tenant cannot register test**

Add this method to `tests/Feature/Platform/PlatformPathRoutesTest.php`:

```php
public function test_tenant_register_route_is_not_available(): void
{
    $this->seed();

    $this->get('/t/demo/register')->assertNotFound();
}
```

- [ ] **Step 3: Run tests to verify failures**

Run:

```bash
php artisan test --filter=PlatformPathRoutesTest
```

Expected: at least one FAIL because tenant auth is still fully exposing central register or redirect handling is incomplete.

- [ ] **Step 4: Remove tenant registration routes from tenant auth group**

In `routes/tenant.php`, replace:

```php
require __DIR__.'/auth.php';
```

with explicit tenant auth routes:

```php
Route::middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])
        ->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)
        ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');
    Route::post('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

- [ ] **Step 5: Add helper methods to AuthenticatedSessionController**

In `app/Http/Controllers/Auth/AuthenticatedSessionController.php`, add these protected methods before `destroy()`:

```php
protected function dashboardRoute(Request $request): string
{
    if ($request->routeIs('tenant.*')) {
        return route('tenant.dashboard', ['tenant' => $this->tenantSlug($request)], absolute: false);
    }

    if ($request->routeIs('platform.*')) {
        return route('platform.tenants.index', absolute: false);
    }

    return route('dashboard', absolute: false);
}

protected function tenantSlug(Request $request): string
{
    return (string) ($request->route('tenant') ?? TenantContext::school()?->slug);
}
```

Then replace the body after `$request->session()->regenerate();` in `store()` with:

```php
if ($request->user()?->isSuperAdmin()) {
    return redirect()->intended(route('platform.tenants.index', absolute: false));
}

return redirect()->intended($this->dashboardRoute($request));
```

- [ ] **Step 6: Run auth route tests**

Run:

```bash
php artisan test --filter=PlatformPathRoutesTest
```

Expected: PASS.

---

### Task 3: Tenant-Aware Vue Route Links

**Files:**
- Modify: `resources/js/Layouts/AuthenticatedLayout.vue`
- Modify: `resources/js/Pages/Auth/Login.vue`
- Modify: `resources/js/types/index.d.ts`
- Test: `tests/Feature/Platform/PlatformPathRoutesTest.php`

- [ ] **Step 1: Add route render smoke tests**

Add this method to `tests/Feature/Platform/PlatformPathRoutesTest.php`:

```php
public function test_tenant_dashboard_renders_without_missing_route_parameters(): void
{
    $this->seed();

    $admin = \App\Models\User::query()->where('email', 'admin@demo.test')->firstOrFail();

    $this->actingAs($admin)->get('/t/demo/dashboard')->assertOk();
}
```

- [ ] **Step 2: Update Inertia page props types**

In `resources/js/types/index.d.ts`, make sure `PageProps` includes:

```ts
export type School = {
    id: string;
    name: string;
    slug: string;
    status: string;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    appName: string;
    auth: {
        user: User;
        roles: string[];
        permissions: string[];
    };
    school: School | null;
    flash: {
        success?: string;
        error?: string;
    };
};
```

Keep existing `User` type fields in the same file.

- [ ] **Step 3: Update AuthenticatedLayout route computation**

In `resources/js/Layouts/AuthenticatedLayout.vue`, update imports:

```ts
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
```

Add after `showingNavigationDropdown`:

```ts
const page = usePage();
const school = computed(() => page.props.school);
const isTenantRoute = computed(() => route().current('tenant.*'));
const isPlatformRoute = computed(() => route().current('platform.*'));

const dashboardHref = computed(() => {
    if (isTenantRoute.value && school.value?.slug) {
        return route('tenant.dashboard', { tenant: school.value.slug });
    }

    if (isPlatformRoute.value) {
        return route('platform.tenants.index');
    }

    return route('dashboard');
});

const profileHref = computed(() => {
    if (isTenantRoute.value && school.value?.slug) {
        return route('tenant.profile.edit', { tenant: school.value.slug });
    }

    return route('profile.edit');
});

const logoutHref = computed(() => {
    if (isTenantRoute.value && school.value?.slug) {
        return route('tenant.logout', { tenant: school.value.slug });
    }

    if (isPlatformRoute.value) {
        return route('platform.logout');
    }

    return route('logout');
});
```

Replace every `route('dashboard')` in the template with `dashboardHref`. Replace every `route('profile.edit')` with `profileHref`. Replace every `route('logout')` with `logoutHref`.

- [ ] **Step 4: Update Login reset-password link**

In `resources/js/Pages/Auth/Login.vue`, add:

```ts
const resetPasswordHref = computed(() => {
    if (!route().has('password.request')) {
        return null;
    }

    if (route().current('tenant.*') && page.props.school?.slug) {
        return route('tenant.password.request', { tenant: page.props.school.slug });
    }

    return route('password.request');
});
```

Update imports to include `computed` from Vue. Change the forgot password link condition and href:

```vue
<Link
    v-if="canResetPassword && resetPasswordHref"
    :href="resetPasswordHref"
    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
>
    Forgot your password?
</Link>
```

- [ ] **Step 5: Run frontend build**

Run:

```bash
npm run build
```

Expected: PASS with Vite build output.

- [ ] **Step 6: Run render test**

Run:

```bash
php artisan test --filter=PlatformPathRoutesTest::test_tenant_dashboard_renders_without_missing_route_parameters
```

Expected: PASS.

---

### Task 4: Platform Boundary and Reset Admin Hardening

**Files:**
- Modify: `app/Http/Controllers/Platform/TenantController.php`
- Test: `tests/Feature/Platform/SuperAdminCanListTenantsTest.php`

- [ ] **Step 1: Add platform login rejection test**

Add this method to `tests/Feature/Platform/SuperAdminCanListTenantsTest.php`:

```php
public function test_tenant_admin_cannot_login_to_platform(): void
{
    $this->seed();

    $response = $this->post('/platform/login', [
        'email' => 'admin@demo.test',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
}
```

- [ ] **Step 2: Add reset password target-school test**

Add this method to `tests/Feature/Platform/SuperAdminCanListTenantsTest.php`:

```php
public function test_super_admin_resets_only_target_school_admin_password(): void
{
    $this->seed();

    $super = User::query()->where('email', 'super@platformsekolah.test')->firstOrFail();
    $demoAdmin = User::query()->where('email', 'admin@demo.test')->firstOrFail();
    $alfaAdmin = User::query()->where('email', 'admin@alfa.test')->firstOrFail();
    $oldAlfaPassword = $alfaAdmin->password;

    $response = $this->actingAs($super)->post('/platform/tenants/'.$demoAdmin->school_id.'/reset-password');

    $response->assertRedirect();
    $this->assertNotSame($demoAdmin->password, $demoAdmin->fresh()->password);
    $this->assertSame($oldAlfaPassword, $alfaAdmin->fresh()->password);
}
```

- [ ] **Step 3: Run tests to verify current behavior**

Run:

```bash
php artisan test --filter=SuperAdminCanListTenantsTest
```

Expected: platform login rejection should PASS; reset test may PASS but does not prove team-aware lookup yet.

- [ ] **Step 4: Harden resetPassword lookup**

In `app/Http/Controllers/Platform/TenantController.php`, replace admin lookup in `resetPassword()` with:

```php
$previousTeam = getPermissionsTeamId();
setPermissionsTeamId($school->id);

try {
    $admin = User::query()
        ->where('school_id', $school->id)
        ->whereHas('roles', fn ($q) => $q
            ->where('name', 'admin-sekolah')
            ->where('school_id', $school->id))
        ->first();
} finally {
    setPermissionsTeamId($previousTeam);
}
```

Keep the existing null check, password generation, update, and flash response.

- [ ] **Step 5: Run platform tests**

Run:

```bash
php artisan test --filter=SuperAdminCanListTenantsTest
```

Expected: PASS.

---

### Task 5: Documentation and Full Verification

**Files:**
- Modify: `docs/plans/04-development-plan.md`

- [ ] **Step 1: Update development plan status note**

In `docs/plans/04-development-plan.md`, update the P1 status note line from:

```markdown
| **P1** Tenancy | ✅ Selesai | schools, resolver, middleware, super-admin tenants UI, seed demo+alfa |
```

to:

```markdown
| **P1** Tenancy | ✅ Selesai | schools, resolver, middleware, tenant-aware auth/routes, super-admin tenants UI, seed demo+alfa |
```

- [ ] **Step 2: Update P1 tasks list**

Under P1 tasks, add these checked items after dev routes path:

```markdown
- [x] Tenant-aware shared Inertia props (`school`, roles, permissions)
- [x] Tenant/platform-safe dashboard, profile, logout, and auth redirects
```

- [ ] **Step 3: Run backend suite**

Run:

```bash
php artisan test
```

Expected: PASS.

- [ ] **Step 4: Run frontend build**

Run:

```bash
npm run build
```

Expected: PASS.

- [ ] **Step 5: Summarize final status**

Report changed files, tests run, and any follow-up risks. Do not claim browser/manual UI verification unless the app was actually launched.

---

## Self-Review

Spec coverage: all spec sections map to tasks. Architecture/shared props covered by Task 1. Routing/auth covered by Tasks 2 and 3. Platform boundaries covered by Task 4. Tests/docs covered by Task 5.

Placeholder scan: no TBD/TODO placeholders remain. Each code-changing step includes exact file and code.

Type consistency: shared prop name is `school` across backend, Vue, and tests. Route names use existing Laravel group names: `tenant.*`, `platform.*`, and central route names.
