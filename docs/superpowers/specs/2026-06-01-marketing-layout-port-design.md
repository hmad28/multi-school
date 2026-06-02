# Marketing Layout Port Design

## Goal

Make the Laravel/Inertia app pages feel copied from the existing marketing design in `marketing/`, not merely recolored Breeze screens.

## Approved direction

Port the marketing layout language into the app:

- `container-main` rhythm: centered max width around `max-w-5xl` for primary pages.
- `section-pad` rhythm: generous `px-5 py-20 sm:px-8 sm:py-24` page sections.
- Marketing typography rhythm: small uppercase section label, large balanced heading, muted paragraph.
- Marketing cards: `rounded-card`, `border-border`, `bg-canvas`, subtle shadow.
- HeroVisual style: window card with `bg-surface` top bar, icon block, skeleton rows, chart bars, side cards.
- Keep app tech stack: Laravel + Inertia Vue + Tailwind.

## Scope

In scope:

- `AuthenticatedLayout.vue`: remove remaining Breeze feel by changing app shell background/header/nav styling to marketing colors/tokens.
- `Dashboard.vue`: rework page to follow marketing homepage hero + HeroVisual + feature card patterns.
- `Platform/Tenants/Index.vue`: rework page to follow marketing sections, cards, and table as a card inside section.
- Tailwind tokens already exist; add radius/shadow helpers only if needed.

Out of scope:

- Backend changes.
- New routes or data props.
- Changing marketing Astro files.

## Testing

Run:

- `npm run build`
- `php artisan test`

Manual check:

- `/platform/tenants`
- `/t/demo/dashboard`
- `/dashboard`
