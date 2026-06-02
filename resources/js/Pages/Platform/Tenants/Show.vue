<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type School = {
    id: string;
    name: string;
    slug: string;
    email: string;
    phone: string | null;
    address: string | null;
    status: string;
    trial_ends_at: string | null;
    users_count: number;
    students_count: number;
};

type Subscription = {
    plan: string;
    period: string;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    amount: string | number;
} | null;

defineProps<{
    school: School;
    subscription: Subscription;
    admins: { id: string; name: string; email: string; created_at: string | null }[];
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });

function formatDate(value: string | null): string {
    if (!value) return '-';

    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

function formatCurrency(value: string | number | null | undefined): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0));
}

function statusClass(status: string): string {
    if (status === 'active') return 'bg-sky-100 text-sky-700';
    if (status === 'trial') return 'bg-brand-100 text-brand-700';
    if (status === 'suspended') return 'bg-amber-100 text-amber-700';

    return 'bg-slate-100 text-slate-600';
}

function updateStatus(school: School, status: string): void {
    router.patch(route('platform.tenants.status', school.id), { status }, { preserveScroll: true });
}

function resetPassword(school: School): void {
    if (!confirm(`Reset password admin untuk ${school.name}?`)) return;
    router.post(route('platform.tenants.reset-password', school.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="school.name" />

    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Platform admin</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Detail tenant</h1>
        </template>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#2563EB] via-[#3B82F6] to-[#1A1D20] p-6 text-white shadow-xl shadow-blue-200 dark:shadow-slate-950/50">
                <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-100">{{ school.slug }}.platformsekolah.id</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight">{{ school.name }}</h2>
                        <p class="mt-2 text-sm text-blue-50">{{ school.email }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a :href="`/t/${school.slug}/login`" class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20">Buka tenant</a>
                        <Link :href="route('platform.tenants.index')" class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20">Kembali</Link>
                    </div>
                </div>
            </section>

            <div v-if="flash?.success" class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-semibold text-sky-700">{{ flash.success }}</div>
            <div v-if="flash?.error" class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">{{ flash.error }}</div>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="app-card p-5"><p class="text-sm text-slate-500">Status</p><span class="app-badge mt-3 capitalize" :class="statusClass(school.status)">{{ school.status }}</span></div>
                <div class="app-card p-5"><p class="text-sm text-slate-500">Trial berakhir</p><p class="mt-3 text-xl font-bold text-ink dark:text-white">{{ formatDate(school.trial_ends_at) }}</p></div>
                <div class="app-card p-5"><p class="text-sm text-slate-500">User</p><p class="mt-3 text-3xl font-bold text-ink dark:text-white">{{ school.users_count }}</p></div>
                <div class="app-card p-5"><p class="text-sm text-slate-500">Siswa</p><p class="mt-3 text-3xl font-bold text-ink dark:text-white">{{ school.students_count }}</p></div>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <section class="app-card p-5 lg:col-span-2">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Profil sekolah</h2>
                    <dl class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                        <div><dt class="text-slate-500">Nama</dt><dd class="font-semibold text-ink dark:text-white">{{ school.name }}</dd></div>
                        <div><dt class="text-slate-500">Slug</dt><dd class="font-mono font-semibold">{{ school.slug }}</dd></div>
                        <div><dt class="text-slate-500">Email</dt><dd>{{ school.email }}</dd></div>
                        <div><dt class="text-slate-500">Telepon</dt><dd>{{ school.phone ?? '-' }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-slate-500">Alamat</dt><dd>{{ school.address ?? '-' }}</dd></div>
                    </dl>
                </section>

                <section class="app-card p-5">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Aksi platform</h2>
                    <div class="mt-5 space-y-3">
                        <button v-if="school.status !== 'active'" type="button" class="app-button-primary w-full" @click="updateStatus(school, 'active')">Aktifkan tenant</button>
                        <button v-if="school.status !== 'suspended'" type="button" class="app-button-secondary w-full" @click="updateStatus(school, 'suspended')">Tangguhkan tenant</button>
                        <button type="button" class="app-button-secondary w-full" @click="resetPassword(school)">Reset password admin</button>
                    </div>
                </section>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="app-card p-5">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Subscription</h2>
                    <dl v-if="subscription" class="mt-5 grid gap-4 text-sm md:grid-cols-2">
                        <div><dt class="text-slate-500">Paket</dt><dd class="font-semibold capitalize">{{ subscription.plan }}</dd></div>
                        <div><dt class="text-slate-500">Periode</dt><dd class="font-semibold capitalize">{{ subscription.period }}</dd></div>
                        <div><dt class="text-slate-500">Status</dt><dd class="font-semibold capitalize">{{ subscription.status }}</dd></div>
                        <div><dt class="text-slate-500">Nominal</dt><dd class="font-semibold">{{ formatCurrency(subscription.amount) }}</dd></div>
                    </dl>
                    <p v-else class="mt-5 text-sm text-slate-500">Subscription belum ada.</p>
                </section>

                <section class="app-card overflow-hidden">
                    <div class="border-b border-line p-5 dark:border-slate-800">
                        <h2 class="font-bold text-slate-900 dark:text-slate-100">User tenant</h2>
                        <p class="text-sm text-slate-500">Ringkasan akun, bukan data operasional.</p>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        <div v-for="admin in admins" :key="admin.id" class="p-5">
                            <p class="font-semibold text-ink dark:text-white">{{ admin.name }}</p>
                            <p class="text-sm text-slate-500">{{ admin.email }}</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
