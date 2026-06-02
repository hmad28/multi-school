<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

type Stats = {
    total: number;
    trial: number;
    active: number;
    suspended: number;
    students: number;
    users: number;
    monthlyRevenue: number;
};

type SchoolRow = {
    id: string;
    name: string;
    slug: string;
    email: string;
    status?: string;
    trial_ends_at: string | null;
    users_count?: number;
    students_count?: number;
};

const props = defineProps<{
    stats: Stats;
    trialEndingSoon: SchoolRow[];
    recentSchools: SchoolRow[];
}>();

const statCards = [
    { label: 'Total tenant', key: 'total', icon: 'graduation-cap', caption: 'Sekolah terdaftar' },
    { label: 'Trial', key: 'trial', icon: 'clock', caption: 'Butuh follow-up sales' },
    { label: 'Aktif', key: 'active', icon: 'check-circle', caption: 'Tenant bisa akses app' },
    { label: 'Suspended', key: 'suspended', icon: 'alert-circle', caption: 'Akses ditahan' },
] as const;

function formatDate(value: string | null): string {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

function formatCurrency(value: number): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value);
}

function statusClass(status?: string): string {
    if (status === 'active') return 'bg-sky-100 text-sky-700';
    if (status === 'trial') return 'bg-brand-100 text-brand-700';
    if (status === 'suspended') return 'bg-amber-100 text-amber-700';

    return 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head title="Platform Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Platform admin</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Dashboard platform</h1>
        </template>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#2563EB] via-[#3B82F6] to-[#1A1D20] p-6 text-white shadow-xl shadow-blue-200 dark:shadow-slate-950/50">
                <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-100">Founder command center</p>
                        <h2 class="mt-3 max-w-3xl text-3xl font-bold tracking-tight">Pantau tenant, trial, dan onboarding dari satu tempat.</h2>
                        <p class="mt-2 max-w-2xl text-sm text-blue-50">Layer ini fokus platform: lifecycle pelanggan, bukan operasional siswa/guru milik sekolah.</p>
                    </div>
                    <Link :href="route('platform.tenants.index')" class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20">Kelola tenant</Link>
                </div>
            </section>

            <div class="grid gap-4 md:grid-cols-4">
                <div v-for="card in statCards" :key="card.label" class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ card.label }}</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ stats[card.key] }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-700/20 dark:text-brand-100">
                            <Icon :name="card.icon" class="h-6 w-6" />
                        </div>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-500">{{ card.caption }}</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <section class="app-card p-5">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Estimasi MRR</h2>
                    <p class="mt-2 text-3xl font-bold text-ink dark:text-white">{{ formatCurrency(stats.monthlyRevenue) }}</p>
                    <p class="mt-2 text-sm text-slate-500">Dari subscription aktif seed/manual. Payment gateway masuk tahap berikutnya.</p>
                </section>
                <section class="app-card p-5">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">User / siswa</h2>
                    <p class="mt-2 text-3xl font-bold text-ink dark:text-white">{{ stats.users }} / {{ stats.students }}</p>
                    <p class="mt-2 text-sm text-slate-500">Usage summary non-sensitif, tanpa masuk data operasional tenant.</p>
                </section>
                <section class="app-card p-5">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Onboarding</h2>
                    <p class="mt-2 text-sm text-slate-500">Slot berikutnya: tampilkan tenant yang belum selesai profil, akademik, import, dan undang user.</p>
                </section>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="app-card overflow-hidden">
                    <div class="border-b border-line p-5 dark:border-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-slate-100">Trial hampir habis</h2>
                        <p class="text-sm text-slate-500">7 hari ke depan.</p>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-if="!trialEndingSoon.length" class="p-5 text-sm text-slate-500">Tidak ada trial yang hampir habis.</div>
                        <div v-for="school in trialEndingSoon" :key="school.id" class="flex items-center justify-between gap-3 p-5">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ school.name }}</div>
                                <div class="text-sm text-slate-500">{{ school.email }}</div>
                            </div>
                            <span class="app-badge bg-amber-100 text-amber-700">{{ formatDate(school.trial_ends_at) }}</span>
                        </div>
                    </div>
                </section>

                <section class="app-card overflow-hidden">
                    <div class="border-b border-line p-5 dark:border-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-slate-100">Tenant terbaru</h2>
                        <p class="text-sm text-slate-500">Sekolah terbaru di platform.</p>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <Link v-for="school in recentSchools" :key="school.id" :href="route('platform.tenants.show', school.id)" class="flex items-center justify-between gap-3 p-5 transition hover:bg-brand-100/40 dark:hover:bg-slate-800">
                            <div>
                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ school.name }}</div>
                                <div class="text-sm text-slate-500">{{ school.slug }} · {{ school.users_count }} user · {{ school.students_count }} siswa</div>
                            </div>
                            <span class="app-badge capitalize" :class="statusClass(school.status)">{{ school.status }}</span>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
