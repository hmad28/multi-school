# Dashboard Brand Polish Design

## Goal

Improve the Laravel/Inertia app dashboard so it matches the existing marketing site's clean SaaS visual language, establishes the product brand palette, and gives P0/P1 users a more polished tenant/platform landing experience.

## Approved visual direction

Use **A. SaaS clean** as the baseline:

- Light canvas, subtle slate borders, rounded cards, generous spacing.
- Blue primary accents matching the marketing site.
- Calm admin-first UI, not playful or heavy.
- Slight friendly school tone only in the hero copy, not in the whole system.

## Brand palette

Use the existing marketing palette as the app brand baseline:

| Token | Hex | Usage |
| --- | --- | --- |
| `brand-950` | `#0F172A` | Ink, dark hero accents |
| `brand-900` | `#1E293B` | Secondary dark surfaces |
| `brand-800` | `#1D4ED8` | Primary hover/strong blue |
| `brand-700` | `#2563EB` | Primary action/accent |
| `brand-600` | `#3B82F6` | Chart/accent tint |
| `brand-100` | `#DBEAFE` | Blue badges/backgrounds |
| `brand-50` | `#F8FAFC` | Page canvas |
| `surface` | `#F1F5F9` | Section/card background |
| `border` | `#E2E8F0` | Card and divider border |

Keep the app font as `Figtree` for this pass to avoid layout churn. Revisit `Plus Jakarta Sans` later if the app and marketing need exact typography parity.

## Dashboard structure

Replace the current plain Breeze dashboard card with a polished dashboard shell.

### Tenant dashboard

When `tenantMode` and `school` are present:

1. Hero card
   - Small label: tenant school slug/name.
   - Heading: welcome message for the school admin.
   - Description: short sentence about managing attendance, discipline, and school operations.
   - Status badge using `school.status`.

2. Summary metric cards
   - `Status tenant` from `school.status`.
   - `Mode akses` showing tenant path route, e.g. `/t/{slug}`.
   - `Modul aktif` showing P1 foundation / tenancy ready.
   - `Langkah berikutnya` pointing to P2 master data.

3. Operational overview card
   - Static preview bars/chips for P0/P1, because real attendance/master data metrics are not implemented yet.
   - Copy must not imply unavailable modules are live.

4. Next steps card
   - Shows P2 master data tasks: students, teachers, classes, academic year.
   - CTA-style visual only; no new routes unless existing routes are already available.

### Central dashboard

When not tenant mode:

1. Hero card for Platform Sekolah.
2. Short copy confirming authenticated central access.
3. Cards pointing users toward platform/tenant context without adding new backend behavior.

## Scope

In scope:

- `resources/js/Pages/Dashboard.vue` visual redesign.
- Tailwind brand colors in app config if needed by the dashboard.
- Docs updates for frontend brand direction and development status.

Out of scope:

- Changing marketing site visuals.
- Adding real P2 metrics before P2 exists.
- Refactoring `AuthenticatedLayout.vue` beyond what dashboard needs.
- Creating new routes or backend endpoints.
- Changing platform tenants index styling in this pass.

## Data flow

Use existing dashboard props only:

- `school?: { id: string; name: string; slug: string; status: string }`
- `tenantMode?: boolean`

No new backend data is required.

## Testing

Run after implementation:

- `npm run build` to validate Vue/TypeScript.
- `php artisan test` to ensure P0/P1 backend regressions did not appear.
- Run the app and visually check:
  - `/dashboard`
  - `/t/demo/dashboard`

## Documentation updates

Update:

- `docs/plans/06-frontend-plan.md` with the app brand palette and dashboard direction.
- `docs/plans/04-development-plan.md` with P1 dashboard brand polish status.

## Decisions

- Brand source of truth for now is the marketing palette, mirrored into the Laravel app.
- Dashboard should be honest about current product state: P0/P1 foundation complete, P2 modules upcoming.
- Prefer clean SaaS admin UI over heavy navy UI for daily school operator comfort.
