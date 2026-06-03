<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Subscription = {
    id: string;
    school_name: string;
    school_slug: string;
    plan: string;
    period: string;
    starts_at: string | null;
    ends_at: string | null;
    status: string;
    amount: string | number;
    payment_reference: string | null;
};

const props = defineProps<{
    subscriptions: Subscription[];
    filters: { status?: string; plan?: string };
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string });

function formatCurrency(value: string | number | null | undefined): string {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value ?? 0));
}

function formatDate(value: string | null): string {
    if (!value) return '-';
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

function statusClass(status: string): string {
    if (status === 'active') return 'bg-sky-100 text-sky-700';
    if (status === 'past_due') return 'bg-amber-100 text-amber-700';
    if (status === 'canceled') return 'bg-rose-100 text-rose-700';
    return 'bg-slate-100 text-slate-600';
}

function updateStatus(sub: Subscription, status: string): void {
    router.patch(route('platform.billing.status', sub.id), { status }, { preserveScroll: true });
}

function applyFilter(key: string, value: string): void {
    router.get(route('platform.billing.index'), { ...props.filters, [key]: value }, { preserveState: true });
}
</script>

<template>
    <Head title="Billing" />

    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Platform admin</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Billing</h1>
        </template>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#2563EB] via-[#3B82F6] to-[#1A1D20] p-6 text-white shadow-xl shadow-blue-200 dark:shadow-slate-950/50">
                <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-100">Subscription management</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight">Kelola subscription tenant</h2>
                        <p class="mt-2 text-sm text-blue-50">Pantau status pembayaran dan aktivasi subscription. Manual ops sebelum payment gateway.</p>
                    </div>
                </div>
            </section>

            <div v-if="flash?.success" class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-semibold text-sky-700">{{ flash.success }}</div>

            <section class="app-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line p-5 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Daftar subscription</h2>
                    <div class="flex gap-2">
                        <select
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900"
                            :value="filters.status ?? ''"
                            @change="applyFilter('status', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">Semua status</option>
                            <option value="active">Active</option>
                            <option value="past_due">Past Due</option>
                            <option value="canceled">Canceled</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <select
                            class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 dark:border-slate-700 dark:bg-slate-900"
                            :value="filters.plan ?? ''"
                            @change="applyFilter('plan', ($event.target as HTMLSelectElement).value)"
                        >
                            <option value="">Semua paket</option>
                            <option value="standar">Standar</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Sekolah</th>
                                <th>Paket</th>
                                <th>Periode</th>
                                <th>Mulai</th>
                                <th>Sampai</th>
                                <th>Status</th>
                                <th class="text-right">Nominal</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in subscriptions" :key="sub.id">
                                <td>
                                    <Link :href="route('platform.tenants.show', sub.school_slug ?? sub.school_name)" class="font-semibold text-slate-900 transition hover:text-brand-700 dark:text-slate-100">
                                        {{ sub.school_name }}
                                    </Link>
                                </td>
                                <td class="capitalize">{{ sub.plan }}</td>
                                <td class="capitalize">{{ sub.period }}</td>
                                <td>{{ formatDate(sub.starts_at) }}</td>
                                <td>{{ formatDate(sub.ends_at) }}</td>
                                <td>
                                    <span class="app-badge capitalize" :class="statusClass(sub.status)">{{ sub.status }}</span>
                                </td>
                                <td class="text-right font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(sub.amount) }}</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <button
                                            v-if="sub.status !== 'active'"
                                            type="button"
                                            class="rounded-xl bg-sky-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-sky-700"
                                            @click="updateStatus(sub, 'active')"
                                        >
                                            Aktifkan
                                        </button>
                                        <button
                                            v-if="sub.status !== 'past_due'"
                                            type="button"
                                            class="rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600"
                                            @click="updateStatus(sub, 'past_due')"
                                        >
                                            Past Due
                                        </button>
                                        <button
                                            v-if="sub.status !== 'canceled'"
                                            type="button"
                                            class="rounded-xl bg-rose-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-rose-600"
                                            @click="updateStatus(sub, 'canceled')"
                                        >
                                            Batalkan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="!subscriptions.length" class="p-5 text-center text-sm text-slate-500">
                    Belum ada data subscription.
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
