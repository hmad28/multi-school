# Marketing Layout Port Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Port the existing marketing layout language into Laravel/Inertia app pages so `/platform/tenants`, `/dashboard`, and `/t/demo/dashboard` feel copied from the reference instead of Breeze-recolored.

**Architecture:** Frontend-only refactor. Tailwind gets marketing utility equivalents; `AuthenticatedLayout.vue` becomes a marketing-style shell; page components reuse the same section/container/card rhythm and HeroVisual-like panels. Existing props, routes, and actions stay unchanged.

**Tech Stack:** Laravel 13, Inertia Vue 3, TypeScript, Tailwind CSS, Vite.

---

## Tasks

### Task 1: Add marketing utility tokens to app Tailwind

**Files:**
- Modify: `tailwind.config.js`

- [ ] Replace `theme.extend` with marketing parity tokens: brand colors, radius `card/btn`, boxShadow `card/featured`, font remains Figtree.
- [ ] Run `npm run build` and expect success.

### Task 2: Rework authenticated shell to marketing style

**Files:**
- Modify: `resources/js/Layouts/AuthenticatedLayout.vue`

- [ ] Replace `min-h-screen bg-gray-100` with `min-h-screen bg-canvas text-ink`.
- [ ] Replace nav/header borders/colors with marketing tokens: `border-border`, `bg-canvas/95`, `text-ink`, `text-ink-muted`, `shadow-[0_1px_2px_rgba(15,23,42,0.04)]`.
- [ ] Make header slot section use `border-b border-border bg-canvas`, not Breeze shadow.
- [ ] Keep all route computations and dropdown behavior unchanged.
- [ ] Run `npm run build` and expect success.

### Task 3: Rework Dashboard.vue as marketing homepage pattern

**Files:**
- Modify: `resources/js/Pages/Dashboard.vue`

- [ ] Use page wrapper `section-pad` equivalent: `px-5 py-20 sm:px-8 sm:py-24`.
- [ ] Use `mx-auto w-full max-w-5xl` container.
- [ ] Use two-column hero like marketing `index.astro`: label, large heading, paragraph, action pills left; HeroVisual-like card right.
- [ ] Use feature card grid like marketing feature modules.
- [ ] Keep existing props only.
- [ ] Run `npm run build` and expect success.

### Task 4: Rework Platform Tenants page as marketing section pattern

**Files:**
- Modify: `resources/js/Pages/Platform/Tenants/Index.vue`

- [ ] Use same section/container rhythm.
- [ ] Use marketing-style header: SectionLabel, `text-hero` equivalent, muted paragraph.
- [ ] Use HeroVisual-like right panel for tenant stats.
- [ ] Use rounded card table with `border-border`, `bg-canvas`, `bg-surface` header.
- [ ] Keep activate/suspend/reset behavior unchanged.
- [ ] Run `npm run build` and expect success.

### Task 5: Update docs and verify

**Files:**
- Modify: `docs/plans/06-frontend-plan.md`

- [ ] Add note that app shell now ports marketing layout utilities into Inertia app.
- [ ] Run `npm run build`.
- [ ] Run `php artisan test`.
- [ ] Smoke `http://127.0.0.1:8888/platform/login`.

## Self-review notes

Spec coverage: Tailwind utilities, app shell, dashboard, platform tenants, docs, and verification are covered.

Placeholder scan: clean.

Type consistency: no backend/data type changes; existing Vue props stay unchanged.
