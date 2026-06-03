<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type SchoolRow = {
    id: string;
    name: string;
    slug: string;
    email: string;
    status: string;
    trial_ends_at: string | null;
    onboarding_completed: boolean;
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

const statCards = computed(() => [
    { label: 'Total sekolah', value: String(totalSchools.value), icon: 'graduation-cap', caption: 'Tenant terdaftar' },
    { label: 'Aktif + trial', value: String(activeSchools.value), icon: 'check-circle', caption: 'Bisa akses aplikasi' },
    { label: 'Suspended', value: String(suspendedSchools.value), icon: 'alert-circle', caption: 'Akses sedang ditahan' },
    { label: 'User / siswa', value: `${totalUsers.value} / ${totalStudents.value}`, icon: 'users', caption: 'Ringkasan data seed' },
]);

function statusBadgeClass(status: string): string {
    if (status === 'active') return 'bg-sky-100 text-sky-700';
    if (status === 'trial') return 'bg-[#DBEAFE] text-[#2563EB]';
    if (status === 'suspended') return 'bg-amber-100 text-amber-700';

    return 'bg-slate-100 text-slate-600';
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
            <p class="page-kicker">Platform admin</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Kelola sekolah</h1>
        </template>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#2563EB] via-[#3B82F6] to-[#1A1D20] p-6 text-white shadow-xl shadow-blue-200 dark:shadow-slate-950/50">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-100">Tenant lifecycle</p>
                        <h2 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight">Pantau sekolah dari satu panel platform.</h2>
                        <p class="mt-2 text-sm text-blue-50">Kelola status trial, active, suspended, dan akses admin sekolah dengan pola UI Platform Sekolah.</p>
                    </div>
                    <a
                        v-if="firstTenantLogin"
                        :href="firstTenantLogin"
                        class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-semibold text-white ring-1 ring-white/20 transition hover:bg-white/20"
                    >
                        Buka tenant demo
                    </a>
                </div>
            </section>

            <div class="grid gap-4 md:grid-cols-4">
                <div v-for="card in statCards" :key="card.label" class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ card.label }}</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ card.value }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#2563EB]/10 text-[#2563EB]">
                            <Icon :name="card.icon" class="h-6 w-6" />
                        </div>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-500">{{ card.caption }}</p>
                </div>
            </div>

            <div
                v-if="flash?.success"
                class="rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-semibold text-sky-700"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash?.error"
                class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700"
            >
                {{ flash.error }}
            </div>

            <section class="app-card overflow-hidden">
                <div class="flex flex-col justify-between gap-3 border-b border-slate-100 px-5 py-4 dark:border-slate-800 sm:flex-row sm:items-center">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-slate-100">Tenant terdaftar</h2>
                        <p class="text-sm text-slate-500">Kelola status dan reset password admin sekolah.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th>Sekolah</th>
                                <th>Slug</th>
                                <th>Status</th>
                                <th>Onboarding</th>
                                <th>Trial</th>
                                <th>User / siswa</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="school in schools" :key="school.id">
                                <td>
                                    <Link :href="route('platform.tenants.show', school.id)" class="font-semibold text-slate-900 transition hover:text-brand-700 dark:text-slate-100 dark:hover:text-brand-100">
                                        {{ school.name }}
                                    </Link>
                                    <div class="mt-1 text-xs text-slate-500">{{ school.email }}</div>
                                </td>
                                <td>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 font-mono text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        {{ school.slug }}
                                    </span>
                                </td>
                                <td>
                                    <span class="app-badge capitalize" :class="statusBadgeClass(school.status)">
                                        {{ school.status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="app-badge" :class="school.onboarding_completed ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                                        {{ school.onboarding_completed ? 'Selesai' : 'Belum' }}
                                    </span>
                                </td>
                                <td>{{ formatDate(school.trial_ends_at) }}</td>
                                <td class="font-semibold text-slate-900 dark:text-slate-100">{{ school.users_count }} / {{ school.students_count }}</td>
                                <td>
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <PrimaryButton
                                            v-if="school.status !== 'active'"
                                            type="button"
                                            class="!rounded-xl !bg-[#2563EB] !px-3 !py-2 !text-xs !font-semibold !text-white hover:!bg-[#1D4ED8]"
                                            @click="updateStatus(school, 'active')"
                                        >
                                            Aktifkan
                                        </PrimaryButton>
                                        <PrimaryButton
                                            v-if="school.status !== 'suspended'"
                                            type="button"
                                            class="!rounded-xl !bg-amber-500 !px-3 !py-2 !text-xs !font-semibold hover:!bg-amber-600"
                                            @click="updateStatus(school, 'suspended')"
                                        >
                                            Tangguhkan
                                        </PrimaryButton>
                                        <PrimaryButton
                                            type="button"
                                            class="!rounded-xl !bg-[#2563EB] !px-3 !py-2 !text-xs !font-semibold hover:!bg-[#1D4ED8]"
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
        </div>
    </AuthenticatedLayout>
</template>
