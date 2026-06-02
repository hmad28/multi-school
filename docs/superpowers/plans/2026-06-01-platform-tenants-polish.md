# Platform Tenants Polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign `/platform/tenants` so platform admin UI matches the approved blue-slate SaaS clean dashboard style.

**Architecture:** This is a frontend-only page polish. `resources/js/Pages/Platform/Tenants/Index.vue` keeps the existing `schools` prop and existing router actions, then adds local computed stats and brand-aligned markup. `docs/plans/06-frontend-plan.md` gets a short platform admin note.

**Tech Stack:** Laravel 13, Inertia Vue 3, TypeScript, Tailwind CSS, Vite.

---

## File structure

- Modify `resources/js/Pages/Platform/Tenants/Index.vue`
  - Owns platform tenants table/card redesign.
  - Keeps `updateStatus()` and `resetPassword()` behavior.
  - Adds computed UI stats and status badge helpers from existing `schools` rows.

- Modify `docs/plans/06-frontend-plan.md`
  - Records that platform admin pages use the same app brand baseline.

---

### Task 1: Redesign platform tenants page

**Files:**
- Modify: `resources/js/Pages/Platform/Tenants/Index.vue:1-117`

- [ ] **Step 1: Replace the entire file**

Replace `resources/js/Pages/Platform/Tenants/Index.vue` with this code:

```vue
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type SchoolRow = {
    id: string;
    name: string;
    slug: string;
    email: string;
    status: string;
    trial_ends_at: string | null;
    users_count: number;
    students_count: number;
};

const props = defineProps<{
    schools: SchoolRow[];
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });

const totalSchools = computed(() => props.schools.length);
const activeSchools = computed(() => props.schools.filter((school) => ['active', 'trial'].includes(school.status)).length);
const suspendedSchools = computed(() => props.schools.filter((school) => school.status === 'suspended').length);
const totalUsers = computed(() => props.schools.reduce((total, school) => total + school.users_count, 0));
const totalStudents = computed(() => props.schools.reduce((total, school) => total + school.students_count, 0));
const firstTenantLogin = computed(() => (props.schools[0] ? `/t/${props.schools[0].slug}/login` : null));

function statusBadgeClass(status: string): string {
    if (status === 'active') {
        return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
    }

    if (status === 'trial') {
        return 'bg-brand-100 text-brand-800 ring-brand-100';
    }

    if (status === 'suspended') {
        return 'bg-amber-50 text-amber-700 ring-amber-200';
    }

    return 'bg-surface text-ink-muted ring-line';
}

function formatDate(value: string | null): string {
    if (!value) return 'Tidak ada batas trial';

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(new Date(value));
}

function updateStatus(school: SchoolRow, status: string) {
    router.patch(route('platform.tenants.status', school.id), { status }, { preserveScroll: true });
}

function resetPassword(school: SchoolRow) {
    if (!confirm(`Reset password admin untuk ${school.name}?`)) return;
    router.post(route('platform.tenants.reset-password', school.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Kelola Sekolah" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-700">
                    Platform admin
                </p>
                <h2 class="mt-1 text-2xl font-bold tracking-tight text-brand-950">
                    Kelola sekolah
                </h2>
            </div>
        </template>

        <div class="bg-brand-50 py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <section class="overflow-hidden rounded-3xl border border-line bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 shadow-sm">
                    <div class="grid gap-6 p-6 text-white sm:p-8 lg:grid-cols-[1.3fr_0.7fr] lg:p-10">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-brand-100">
                                Tenant lifecycle
                            </p>
                            <h1 class="mt-4 max-w-3xl text-3xl font-black tracking-tight sm:text-4xl">
                                Pantau dan kelola sekolah dari satu panel platform.
                            </h1>
                            <p class="mt-4 max-w-2xl text-base leading-7 text-blue-100">
                                Aktifkan tenant, tangguhkan akses, dan reset password admin sekolah tanpa masuk ke area operasional tenant.
                            </p>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-white/10 p-5 backdrop-blur">
                            <p class="text-sm font-semibold text-blue-100">Total sekolah</p>
                            <p class="mt-3 text-5xl font-black">{{ totalSchools }}</p>
                            <p class="mt-3 text-sm leading-6 text-blue-100">
                                {{ activeSchools }} aktif/trial, {{ suspendedSchools }} ditangguhkan.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-2xl border border-line bg-canvas p-5 shadow-sm">
                        <p class="text-sm font-semibold text-ink-muted">Total sekolah</p>
                        <p class="mt-3 text-3xl font-black text-brand-950">{{ totalSchools }}</p>
                    </article>
                    <article class="rounded-2xl border border-line bg-canvas p-5 shadow-sm">
                        <p class="text-sm font-semibold text-ink-muted">Aktif + trial</p>
                        <p class="mt-3 text-3xl font-black text-brand-700">{{ activeSchools }}</p>
                    </article>
                    <article class="rounded-2xl border border-line bg-canvas p-5 shadow-sm">
                        <p class="text-sm font-semibold text-ink-muted">Suspended</p>
                        <p class="mt-3 text-3xl font-black text-amber-600">{{ suspendedSchools }}</p>
                    </article>
                    <article class="rounded-2xl border border-line bg-canvas p-5 shadow-sm">
                        <p class="text-sm font-semibold text-ink-muted">User / siswa</p>
                        <p class="mt-3 text-3xl font-black text-brand-950">{{ totalUsers }} / {{ totalStudents }}</p>
                    </article>
                </section>

                <div
                    v-if="flash?.success"
                    class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-sm"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash?.error"
                    class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-800 shadow-sm"
                >
                    {{ flash.error }}
                </div>

                <section class="overflow-hidden rounded-3xl border border-line bg-canvas shadow-sm">
                    <div class="border-b border-line px-5 py-4 sm:px-6">
                        <h3 class="text-lg font-black text-brand-950">Daftar sekolah</h3>
                        <p class="mt-1 text-sm text-ink-muted">
                            Data tenant seed dan tenant baru akan muncul di sini.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-line text-sm">
                            <thead class="bg-surface">
                                <tr>
                                    <th class="px-5 py-4 text-left font-bold text-ink-muted">Sekolah</th>
                                    <th class="px-5 py-4 text-left font-bold text-ink-muted">Slug</th>
                                    <th class="px-5 py-4 text-left font-bold text-ink-muted">Status</th>
                                    <th class="px-5 py-4 text-left font-bold text-ink-muted">Trial</th>
                                    <th class="px-5 py-4 text-left font-bold text-ink-muted">Pengguna / Siswa</th>
                                    <th class="px-5 py-4 text-right font-bold text-ink-muted">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line bg-white">
                                <tr
                                    v-for="school in schools"
                                    :key="school.id"
                                    class="transition hover:bg-brand-50/70"
                                >
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-brand-950">{{ school.name }}</div>
                                        <div class="mt-1 text-sm text-ink-muted">{{ school.email }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-surface px-3 py-1 font-mono text-xs font-bold text-brand-900 ring-1 ring-line">
                                            {{ school.slug }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-bold capitalize ring-1"
                                            :class="statusBadgeClass(school.status)"
                                        >
                                            {{ school.status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-ink-muted">
                                        {{ formatDate(school.trial_ends_at) }}
                                    </td>
                                    <td class="px-5 py-4 font-semibold text-brand-950">
                                        {{ school.users_count }} / {{ school.students_count }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <PrimaryButton
                                                v-if="school.status !== 'active'"
                                                type="button"
                                                class="!rounded-xl !bg-brand-700 !px-3 !py-2 !text-xs hover:!bg-brand-800"
                                                @click="updateStatus(school, 'active')"
                                            >
                                                Aktifkan
                                            </PrimaryButton>
                                            <PrimaryButton
                                                v-if="school.status !== 'suspended'"
                                                type="button"
                                                class="!rounded-xl !bg-amber-600 !px-3 !py-2 !text-xs hover:!bg-amber-700"
                                                @click="updateStatus(school, 'suspended')"
                                            >
                                                Tangguhkan
                                            </PrimaryButton>
                                            <PrimaryButton
                                                type="button"
                                                class="!rounded-xl !bg-brand-950 !px-3 !py-2 !text-xs hover:!bg-brand-900"
                                                @click="resetPassword(school)"
                                            >
                                                Reset password
                                            </PrimaryButton>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section
                    v-if="firstTenantLogin"
                    class="rounded-2xl border border-line bg-canvas p-5 shadow-sm"
                >
                    <p class="text-sm font-semibold text-ink-muted">Tenant dev cepat</p>
                    <a
                        class="mt-2 inline-flex text-sm font-bold text-brand-700 underline-offset-4 hover:text-brand-800 hover:underline"
                        :href="firstTenantLogin"
                    >
                        {{ firstTenantLogin }}
                    </a>
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

Expected: build succeeds.

---

### Task 2: Update frontend docs note

**Files:**
- Modify: `docs/plans/06-frontend-plan.md:52-59`

- [ ] **Step 1: Add platform brand note**

Under `### 3.3 UX rules`, after:

```markdown
- Tidak tampilkan data murid di v1.
```

add:

```markdown
- Platform admin mengikuti brand baseline aplikasi: hero blue-slate, cards rounded, status badge, dan table modern.
```

- [ ] **Step 2: Run docs verification read**

Read lines 52-82 of `docs/plans/06-frontend-plan.md`.

Expected: the new bullet appears under platform admin UX rules.

---

### Task 3: Verify page behavior

**Files:**
- Test: frontend build and Laravel test suite
- Manual: `/platform/tenants`

- [ ] **Step 1: Run frontend build**

Run:

```bash
npm run build
```

Expected: build succeeds.

- [ ] **Step 2: Run backend tests**

Run:

```bash
php artisan test
```

Expected: all tests pass or existing skipped tests remain skipped.

- [ ] **Step 3: Smoke login page**

Open:

```text
http://127.0.0.1:8888/platform/login
```

Expected: login page loads.

- [ ] **Step 4: Manual visual check platform tenants**

Login as:

```text
super@platformsekolah.test / password
```

Open:

```text
http://127.0.0.1:8888/platform/tenants
```

Expected: page shows blue-slate hero, stat cards, modern table, status badges, and the same activate/suspend/reset actions.

---

## Self-review notes

Spec coverage:

- Hero, summary cards, flash messages, modern tenant list, and dev tenant card are covered in Task 1.
- Existing actions are kept in Task 1.
- Docs note is covered in Task 2.
- Build/test/manual checks are covered in Task 3.

Placeholder scan: clean.

Type consistency:

- `SchoolRow` fields match existing controller payload.
- Computed values use only `props.schools`.
- `statusBadgeClass`, `formatDate`, `updateStatus`, and `resetPassword` are defined before template use.
