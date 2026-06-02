# P0/P1 Foundation Hardening Design

## Scope

Harden completed P0/P1 work before P2 master-data port. Focus on tenancy, route context, Inertia shared props, platform admin boundaries, and tests. Do not add P2 domain features.

## Architecture

Keep Laravel + Inertia + Vue monolith. Preserve default local path routing:

- `/platform/*` for super-admin platform UI.
- `/t/{tenant}/*` for tenant UI.
- central `/` and Breeze routes for base app fallback.

Use `TenantContext` as source of truth for active school on tenant requests. Expose shared Inertia props that match docs:

- `school`: active tenant school `{ id, name, slug, status }` or `null`.
- `auth.user`: authenticated user.
- `auth.roles`: current user's role names in active team/platform context.
- `auth.permissions`: current user's permission names in active team/platform context.
- `flash`: success/error messages.
- `appName`: configured platform name.

## Routing and auth

Tenant auth reuses Breeze controllers but redirects and links must respect current route context. Tenant logins redirect to `tenant.dashboard` with route tenant param. Platform logins redirect to `platform.tenants.index`. Central logins keep default `dashboard` behavior.

Shared Vue layouts must not hardcode central route names for dashboard, profile, or logout. They should compute route targets from current route namespace and available `school.slug`.

## Platform and tenant boundaries

Super-admin routes run without tenant context and require `super-admin`. Tenant routes set `TenantContext` and Spatie team id. Tenant users cannot access platform routes. Cross-tenant model access remains denied even with known UUID.

Reset password admin sekolah should select an admin belonging to the target school and role/team, rather than relying on role name alone.

## Tests

Add or update feature tests for:

- tenant login redirects to `/t/demo/dashboard`.
- tenant shared props include `school`.
- tenant dashboard/profile/logout route links can render without missing route parameters.
- tenant admin cannot access or login platform.
- super-admin can access platform tenant list.
- cross-tenant student access denied.

Run backend tests after changes. Run frontend build/typecheck because Vue and TypeScript links may change.

## Non-goals

- No P2 master-data CRUD.
- No onboarding, billing, or register-school backend.
- No production Cloudflare or mini-PC deployment changes.
- No broad UI redesign.
