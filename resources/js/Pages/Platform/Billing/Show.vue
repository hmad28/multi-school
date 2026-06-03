<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string; name: string });

const props = defineProps<{
    subscription: {
        id: string;
        school_name: string;
        school_slug: string;
        school_id: string;
        plan: string;
        period: string;
        starts_at: string | null;
        ends_at: string | null;
        status: string;
        amount: number | null;
        payment_reference: string | null;
    };
}>();

const statusForm = useForm({ status: props.subscription.status });
const statusLabels: Record<string, string> = {
    active: 'Aktif',
    inactive: 'Nonaktif',
    past_due: 'Menunggak',
    canceled: 'Dibatalkan',
};
const statusClasses: Record<string, string> = {
    active: 'bg-emerald-50 text-emerald-700',
    inactive: 'bg-slate-100 text-slate-600',
    past_due: 'bg-amber-50 text-amber-700',
    canceled: 'bg-rose-50 text-rose-700',
};

const updateStatus = () => {
    if (!confirm('Ubah status subscription ini?')) return;
    statusForm.patch(route('platform.billing.status', { subscription: props.subscription.id }), {
        preserveScroll: true,
        onSuccess: () => router.reload(),
    });
};
</script>

<template>
    <Head title="Detail Subscription" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('platform.billing.index')" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Kembali</Link>
                <span class="text-slate-300">/</span>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Detail Subscription</h1>
            </div>
        </template>

        <div class="max-w-3xl space-y-5">
            <section class="app-card p-6">
                <h2 class="text-lg font-bold text-slate-900">{{ subscription.school_name }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ subscription.school_slug }}</p>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paket</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 capitalize">{{ subscription.plan }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900 capitalize">{{ subscription.period }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mulai</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ subscription.starts_at ? new Date(subscription.starts_at).toLocaleDateString('id-ID') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Berakhir</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ subscription.ends_at ? new Date(subscription.ends_at).toLocaleDateString('id-ID') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</dt>
                        <dd><span class="app-badge mt-1" :class="statusClasses[subscription.status] ?? 'bg-slate-100 text-slate-600'">{{ statusLabels[subscription.status] ?? subscription.status }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jumlah</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-900">{{ subscription.amount ? 'Rp ' + Number(subscription.amount).toLocaleString('id-ID') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Referensi Pembayaran</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ subscription.payment_reference ?? '-' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="app-card p-6">
                <h2 class="text-lg font-bold text-slate-900">Ubah Status</h2>
                <p class="mt-1 text-sm text-slate-500">Ubah status subscription secara manual.</p>
                <div class="mt-4 flex items-end gap-3">
                    <select v-model="statusForm.status" class="app-input w-48">
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="past_due">Menunggak</option>
                        <option value="canceled">Dibatalkan</option>
                    </select>
                    <button type="button" class="app-button-primary" :disabled="statusForm.processing" @click="updateStatus">Simpan</button>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
