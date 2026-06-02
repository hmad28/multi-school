<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Holiday = {
    id: string;
    date: string;
    name: string;
    description: string | null;
    status: string;
};

const props = defineProps<{
    holidays: Holiday[];
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string;
    calendarStart: string;
    calendarEnd: string;
}>();

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (params: Record<string, string> = {}) => ({ tenant: school.value.slug, ...params });

function formatDate(value: string): string {
    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(value));
}

function destroyHoliday(holiday: Holiday): void {
    if (!confirm(`Hapus hari libur ${holiday.name}?`)) return;

    router.delete(route('tenant.academic-calendar.holidays.destroy', tenantParams({ holiday: holiday.id })), { preserveScroll: true });
}
</script>

<template>
    <Head title="Kalender Akademik" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="page-kicker">Operasional</p>
                <h1 class="text-2xl font-bold text-ink dark:text-white">Kalender Akademik</h1>
                <p class="text-sm text-slate-500">Kelola hari libur dan agenda non-belajar tenant.</p>
            </div>
        </template>

        <div class="space-y-6">
            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-brand-700">{{ monthLabel }}</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">Hari libur kalender akademik</h2>
                        <p class="text-sm text-slate-500">Periode kalender: {{ formatDate(calendarStart) }} — {{ formatDate(calendarEnd) }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('tenant.academic-calendar.holidays.index', tenantParams({ month: previousMonth }))" class="app-button-secondary">Bulan sebelumnya</Link>
                        <Link :href="route('tenant.academic-calendar.holidays.index', tenantParams({ month: nextMonth }))" class="app-button-secondary">Bulan berikutnya</Link>
                        <Link :href="route('tenant.academic-calendar.holidays.create', tenantParams())" class="app-button-primary">Tambah hari libur</Link>
                    </div>
                </div>
            </section>

            <section class="app-card overflow-hidden">
                <div class="border-b border-line p-5 dark:border-slate-800">
                    <h2 class="font-bold text-slate-900 dark:text-slate-100">Daftar hari libur</h2>
                    <p class="text-sm text-slate-500">Tanggal libur hanya berlaku untuk tenant ini.</p>
                </div>
                <table class="app-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!holidays.length">
                            <td colspan="4" class="text-center text-sm text-slate-500">Belum ada hari libur pada periode ini.</td>
                        </tr>
                        <tr v-for="holiday in holidays" :key="holiday.id">
                            <td class="font-semibold text-slate-900 dark:text-slate-100">{{ formatDate(holiday.date) }}</td>
                            <td>
                                <div class="font-semibold text-slate-900 dark:text-slate-100">{{ holiday.name }}</div>
                                <div class="text-xs text-slate-500">{{ holiday.description ?? '-' }}</div>
                            </td>
                            <td><span class="app-badge capitalize" :class="holiday.status === 'active' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600'">{{ holiday.status }}</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('tenant.academic-calendar.holidays.edit', tenantParams({ holiday: holiday.id }))" class="app-button-secondary px-3 py-2 text-xs">Edit</Link>
                                    <button type="button" class="app-button-danger px-3 py-2 text-xs" @click="destroyHoliday(holiday)">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
