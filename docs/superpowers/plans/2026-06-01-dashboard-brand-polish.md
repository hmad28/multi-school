# Dashboard Brand Polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a polished SaaS-clean dashboard that matches the marketing site's blue-slate brand and document the app brand baseline.

**Architecture:** Keep this as a frontend-only polish pass. `Dashboard.vue` renders tenant and central dashboard states from existing props; `tailwind.config.js` mirrors marketing brand tokens; docs record the approved palette and P1 dashboard polish status. No backend routes, controllers, migrations, or new data props are added.

**Tech Stack:** Laravel 13, Inertia Vue 3, TypeScript, Tailwind CSS, Vite.

---

## File structure

- Modify `resources/js/Pages/Dashboard.vue`
  - Owns the complete dashboard visual redesign.
  - Uses existing `school` and `tenantMode` props only.
  - Adds local computed values and local arrays for static P0/P1 dashboard cards.

- Modify `tailwind.config.js`
  - Adds app brand colors matching `marketing/src/styles/global.css`.
  - Keeps existing `Figtree` app font.

- Modify `docs/plans/06-frontend-plan.md`
  - Adds approved brand palette and dashboard visual direction.

- Modify `docs/plans/04-development-plan.md`
  - Updates P1 status note and task checklist with dashboard brand polish.

---

### Task 1: Add app brand tokens to Tailwind

**Files:**
- Modify: `tailwind.config.js:12-18`

- [ ] **Step 1: Update Tailwind theme colors**

Replace the current `theme.extend` block in `tailwind.config.js` with:

```js
theme: {
    extend: {
        colors: {
            brand: {
                50: '#f8fafc',
                100: '#dbeafe',
                600: '#3b82f6',
                700: '#2563eb',
                800: '#1d4ed8',
                900: '#1e293b',
                950: '#0f172a',
            },
            canvas: '#ffffff',
            surface: '#f1f5f9',
            ink: {
                DEFAULT: '#0f172a',
                muted: '#475569',
                faint: '#94a3b8',
            },
            line: {
                DEFAULT: '#e2e8f0',
                strong: '#cbd5e1',
            },
        },
        fontFamily: {
            sans: ['Figtree', ...defaultTheme.fontFamily.sans],
        },
    },
},
```

Keep imports and plugins unchanged.

- [ ] **Step 2: Verify Tailwind config syntax**

Run:

```bash
npm run build
```

Expected: build reaches `vue-tsc && vite build`. If it fails, error should point to syntax/type issue in edited Vue/Tailwind files.

---

### Task 2: Replace the plain dashboard with SaaS-clean dashboard

**Files:**
- Modify: `resources/js/Pages/Dashboard.vue:1-39`

- [ ] **Step 1: Replace the entire file**

Replace `resources/js/Pages/Dashboard.vue` with:

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type School = {
    id: string;
    name: string;
    slug: string;
    status: string;
};

const props = defineProps<{
    school?: School;
    tenantMode?: boolean;
}>();

const isTenantDashboard = computed(() => Boolean(props.tenantMode && props.school));

const statusLabel = computed(() => {
    const status = props.school?.status ?? 'active';

    return status.charAt(0).toUpperCase() + status.slice(1);
});

const tenantPath = computed(() => (props.school ? `/t/${props.school.slug}` : '/dashboard'));

const summaryCards = computed(() => {
    if (isTenantDashboard.value && props.school) {
        return [
            {
                label: 'Status tenant',
                value: statusLabel.value,
                detail: 'Tenant siap dipakai untuk fondasi P1.',
            },
            {
                label: 'Mode akses',
                value: tenantPath.value,
                detail: 'Path routing aktif untuk development lokal.',
            },
            {
                label: 'Modul aktif',
                value: 'P0/P1',
                detail: 'Bootstrap, auth, tenancy, dan platform admin.',
            },
            {
                label: 'Berikutnya',
                value: 'P2',
                detail: 'Master data siswa, guru, kelas, tahun ajaran.',
            },
        ];
    }

    return [
        {
            label: 'Area',
            value: 'Central',
            detail: 'Dashboard default untuk akun non-tenant.',
        },
        {
            label: 'Platform',
            value: 'Siap',
            detail: 'Gunakan panel platform untuk kelola tenant.',
        },
        {
            label: 'Routing',
            value: 'Hybrid',
            detail: 'Path routing lokal, subdomain untuk production.',
        },
        {
            label: 'Berikutnya',
            value: 'P2',
            detail: 'Port master data dari pilot.',
        },
    ];
});

const readinessItems = [
    { label: 'Tenancy foundation', value: '100%', width: '100%' },
    { label: 'Platform admin v1', value: '100%', width: '100%' },
    { label: 'Master data port', value: 'Next', width: '32%' },
];

const nextSteps = [
    'Port data siswa, guru, kelas, tahun ajaran, dan semester.',
    'Tambah validasi unik per sekolah untuk data master.',
    'Siapkan import Excel siswa dengan validasi per baris.',
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-700">
                    Platform Sekolah
                </p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-brand-950">
                    Dashboard
                </h2>
            </div>
        </template>

        <div class="bg-brand-50 py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="overflow-hidden rounded-3xl border border-line bg-canvas shadow-sm">
                    <div class="grid gap-0 lg:grid-cols-[1.35fr_0.65fr]">
                        <div class="p-6 sm:p-8 lg:p-10">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-brand-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-brand-800">
                                    {{ isTenantDashboard && school ? school.slug : 'central' }}
                                </span>
                                <span class="rounded-full border border-line bg-surface px-3 py-1 text-xs font-semibold text-ink-muted">
                                    {{ isTenantDashboard ? statusLabel : 'Authenticated' }}
                                </span>
                            </div>

                            <h1 class="mt-6 max-w-3xl text-3xl font-black tracking-tight text-brand-950 sm:text-4xl">
                                <template v-if="isTenantDashboard && school">
                                    Selamat datang di {{ school.name }}.
                                </template>
                                <template v-else>
                                    Selamat datang di Platform Sekolah.
                                </template>
                            </h1>

                            <p class="mt-4 max-w-2xl text-base leading-7 text-ink-muted">
                                <template v-if="isTenantDashboard && school">
                                    Kelola fondasi sekolah multi-tenant dari satu dashboard bersih. P0/P1 sudah siap; master data menjadi langkah berikutnya.
                                </template>
                                <template v-else>
                                    Akses pusat untuk mengelola konteks platform dan masuk ke area tenant sesuai kebutuhan operasional.
                                </template>
                            </p>

                            <div class="mt-7 flex flex-wrap gap-3">
                                <Link
                                    v-if="!isTenantDashboard && route().has('platform.tenants.index')"
                                    :href="route('platform.tenants.index')"
                                    class="inline-flex items-center rounded-xl bg-brand-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800"
                                >
                                    Buka platform admin
                                </Link>
                                <span class="inline-flex items-center rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-bold text-brand-950">
                                    Brand blue-slate aktif
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-line bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 p-6 text-white lg:border-l lg:border-t-0 sm:p-8">
                            <p class="text-sm font-semibold text-brand-100">Ringkasan kesiapan</p>
                            <div class="mt-6 space-y-5">
                                <div
                                    v-for="item in readinessItems"
                                    :key="item.label"
                                >
                                    <div class="mb-2 flex items-center justify-between text-sm">
                                        <span class="font-semibold text-blue-50">{{ item.label }}</span>
                                        <span class="text-blue-100">{{ item.value }}</span>
                                    </div>
                                    <div class="h-2 overflow-hidden rounded-full bg-white/15">
                                        <div
                                            class="h-full rounded-full bg-brand-600"
                                            :style="{ width: item.width }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="rounded-2xl border border-line bg-canvas p-5 shadow-sm"
                    >
                        <p class="text-sm font-semibold text-ink-muted">{{ card.label }}</p>
                        <p class="mt-3 text-2xl font-black text-brand-950">{{ card.value }}</p>
                        <p class="mt-2 text-sm leading-6 text-ink-muted">{{ card.detail }}</p>
                    </article>
                </section>

                <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <article class="rounded-3xl border border-line bg-canvas p-6 shadow-sm sm:p-8">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-700">
                                    Operasional
                                </p>
                                <h3 class="mt-2 text-xl font-black text-brand-950">
                                    Fondasi siap untuk modul sekolah
                                </h3>
                            </div>
                            <span class="rounded-full bg-surface px-3 py-1 text-xs font-bold text-ink-muted">
                                P0/P1
                            </span>
                        </div>

                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl bg-surface p-4">
                                <p class="text-sm font-semibold text-ink-muted">Auth</p>
                                <p class="mt-2 text-3xl font-black text-brand-950">OK</p>
                            </div>
                            <div class="rounded-2xl bg-surface p-4">
                                <p class="text-sm font-semibold text-ink-muted">Tenant</p>
                                <p class="mt-2 text-3xl font-black text-brand-700">OK</p>
                            </div>
                            <div class="rounded-2xl bg-surface p-4">
                                <p class="text-sm font-semibold text-ink-muted">Data</p>
                                <p class="mt-2 text-3xl font-black text-ink-faint">P2</p>
                            </div>
                        </div>
                    </article>

                    <article class="rounded-3xl border border-line bg-canvas p-6 shadow-sm sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-700">
                            Langkah berikutnya
                        </p>
                        <h3 class="mt-2 text-xl font-black text-brand-950">
                            Mulai P2 master data
                        </h3>
                        <ul class="mt-6 space-y-4">
                            <li
                                v-for="step in nextSteps"
                                :key="step"
                                class="flex gap-3 text-sm leading-6 text-ink-muted"
                            >
                                <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-700" />
                                <span>{{ step }}</span>
                            </li>
                        </ul>
                    </article>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

- [ ] **Step 2: Run frontend build**

Run:

```bash
npm run build
```

Expected: `vue-tsc && vite build` completes successfully.

- [ ] **Step 3: Fix any TypeScript or Tailwind class issues**

If `npm run build` fails, fix only the reported issue in `resources/js/Pages/Dashboard.vue` or `tailwind.config.js`, then run:

```bash
npm run build
```

Expected: build succeeds.

---

### Task 3: Document brand direction in frontend plan

**Files:**
- Modify: `docs/plans/06-frontend-plan.md:8-18`

- [ ] **Step 1: Insert brand section after UX principles**

In `docs/plans/06-frontend-plan.md`, insert this section after the UX principles list and before `## 2. Tiga shell UI`:

```markdown
---

## 1.1 Brand baseline aplikasi

Aplikasi Laravel/Inertia mengikuti arah visual **SaaS clean** dari marketing Astro: canvas terang, border slate halus, kartu rounded, dan aksen biru untuk status/aksi utama.

| Token | Hex | Penggunaan |
|-------|-----|------------|
| `brand-950` | `#0F172A` | Teks utama, aksen gelap |
| `brand-900` | `#1E293B` | Surface gelap sekunder |
| `brand-800` | `#1D4ED8` | Hover/strong primary |
| `brand-700` | `#2563EB` | Primary action/accent |
| `brand-600` | `#3B82F6` | Chart/accent tint |
| `brand-100` | `#DBEAFE` | Badge/background biru |
| `brand-50` | `#F8FAFC` | Page canvas |
| `surface` | `#F1F5F9` | Background section/card |
| `border` | `#E2E8F0` | Border/divider |

Font app tetap `Figtree` pada P1 untuk menghindari churn layout. `Plus Jakarta Sans` bisa dievaluasi ulang saat app dan marketing perlu parity tipografi penuh.

Dashboard P1 memakai visual ringkas dan jujur: fondasi P0/P1 sudah siap, modul operasional P2+ ditampilkan sebagai langkah berikutnya, bukan metrik live.
```

- [ ] **Step 2: Verify docs still read in order**

Read the first 80 lines of `docs/plans/06-frontend-plan.md`.

Expected: headings flow as `## 1. UX principles`, `## 1.1 Brand baseline aplikasi`, `## 2. Tiga shell UI`.

---

### Task 4: Update development plan P1 status

**Files:**
- Modify: `docs/plans/04-development-plan.md:11-18`
- Modify: `docs/plans/04-development-plan.md:98-113`

- [ ] **Step 1: Update P1 status note**

In the status table, change the P1 note from:

```markdown
| **P1** Tenancy | ✅ Selesai | schools, resolver, middleware, tenant-aware auth/routes, super-admin tenants UI, seed demo+alfa |
```

to:

```markdown
| **P1** Tenancy | ✅ Selesai | schools, resolver, middleware, tenant-aware auth/routes, dashboard brand polish, super-admin tenants UI, seed demo+alfa |
```

- [ ] **Step 2: Add checked P1 task**

Under P1 tasks, after:

```markdown
- [x] Tenant/platform-safe dashboard, profile, logout, and auth redirects
```

add:

```markdown
- [x] Dashboard brand baseline mengikuti marketing Astro (blue-slate SaaS clean)
```

- [ ] **Step 3: Verify the P1 section**

Read lines 96-116 of `docs/plans/04-development-plan.md`.

Expected: P1 task list includes the new dashboard brand baseline checked item.

---

### Task 5: Verify full app

**Files:**
- Test: frontend build and Laravel feature suite
- Manual: app dashboard routes

- [ ] **Step 1: Run full frontend build**

Run:

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 2: Run backend test suite**

Run:

```bash
php artisan test
```

Expected: all tests pass or existing skipped tests remain skipped.

- [ ] **Step 3: Start app if not already running**

Run:

```bash
php artisan serve --host=127.0.0.1 --port=8888
```

Expected: Laravel server listens at `http://127.0.0.1:8888`.

- [ ] **Step 4: Manual visual check central dashboard**

Open:

```text
http://127.0.0.1:8888/dashboard
```

Expected: central dashboard shows SaaS-clean hero, four summary cards, operational card, and next steps card.

- [ ] **Step 5: Manual visual check tenant dashboard**

Open:

```text
http://127.0.0.1:8888/t/demo/dashboard
```

Login if needed:

```text
admin@demo.test / password
```

Expected: tenant dashboard shows school slug/name, tenant status badge, tenant path card `/t/demo`, P0/P1 readiness, and P2 next steps. No fake live P2 metrics appear.

---

## Self-review notes

Spec coverage:

- Brand palette: Task 1 and Task 3.
- Tenant dashboard visual redesign: Task 2.
- Central dashboard state: Task 2.
- No backend data additions: Task 2 uses only existing props.
- Docs updates: Task 3 and Task 4.
- Verification: Task 5.

Placeholder scan: clean. No placeholder instructions remain.

Type consistency:

- `School` type uses `id`, `name`, `slug`, `status`, matching existing dashboard props.
- `isTenantDashboard`, `statusLabel`, `tenantPath`, `summaryCards`, `readinessItems`, and `nextSteps` are all defined before template use.
- Tailwind class names use tokens added in Task 1: `brand`, `canvas`, `surface`, `ink`, and `line`.
