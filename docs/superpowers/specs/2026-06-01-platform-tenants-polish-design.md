# Platform Tenants Polish Design

## Goal

Update `/platform/tenants` so the platform admin page matches the approved blue-slate SaaS clean brand used by the dashboard and marketing site.

## Scope

In scope:

- Redesign `resources/js/Pages/Platform/Tenants/Index.vue`.
- Use existing `schools` prop only.
- Keep current platform tenant actions: activate, suspend, reset password.
- Add derived UI stats from existing rows.
- Add a short docs note that platform admin UI follows the same brand baseline.

Out of scope:

- Backend changes.
- New routes or APIs.
- New tenant detail pages.
- Changing auth, reset password, or status behavior.

## Visual direction

Use the same **SaaS clean** direction approved for dashboard:

- Light brand canvas (`brand-50`) and white cards.
- Navy/blue hero card.
- Rounded cards, subtle slate borders, calm admin UI.
- Status badges instead of plain status text.
- Actions grouped in a clear right-side action cluster.

## Page structure

1. Header slot
   - Small uppercase label: `Platform admin`.
   - Title: `Kelola sekolah`.

2. Hero section
   - Navy/blue gradient card.
   - Copy explaining this page manages tenant lifecycle.
   - Shows total tenant count.

3. Summary cards
   - Total sekolah.
   - Aktif + trial.
   - Suspended.
   - Total pengguna/siswa.

4. Flash messages
   - Rounded alert cards using green/red semantic colors, visually aligned with brand cards.

5. Tenant list
   - Modern table inside rounded card.
   - School name + email.
   - Slug as pill.
   - Status as colored badge.
   - Users/students count.
   - Trial date text.
   - Actions: Aktifkan, Tangguhkan, Reset password.

6. Dev tenant card
   - Small card with `/t/{firstSlug}/login` link if a school exists.

## Data flow

Use existing prop:

```ts
schools: SchoolRow[]
```

Add computed values in the Vue file:

- `totalSchools`
- `activeSchools`
- `suspendedSchools`
- `totalUsers`
- `totalStudents`

No backend data changes.

## Testing

Run:

- `npm run build`
- `php artisan test`

Manual/smoke:

- Open `http://127.0.0.1:8888/platform/tenants` as super-admin.
- Confirm page has SaaS clean layout and actions still render.
