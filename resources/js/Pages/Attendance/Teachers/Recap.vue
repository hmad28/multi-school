<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { AttendanceStatus } from '@/types/domain';
import { formatDateRange } from '@/lib/datetime';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ statuses: AttendanceStatus[]; rows: any[]; filters: { from: string; to: string } }>();
const from = ref(props.filters.from);
const to = ref(props.filters.to);
const apply = () => router.get(route('tenant.attendance.teachers.recap', tenantParams()), { from: from.value, to: to.value }, { preserveState: true, preserveScroll: true, replace: true });
</script>

<template>
    <Head title="Rekap Absensi Guru" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Rekap</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Rekap Absensi Guru</h1>
        </template>

        <div class="space-y-5">
            <section class="app-card p-5">
                <div class="flex flex-wrap items-end gap-3">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Dari<input v-model="from" type="date" class="app-input mt-2" /></label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Sampai<input v-model="to" type="date" class="app-input mt-2" /></label>
                    <button @click="apply" class="app-button-secondary">Tampilkan</button>
                </div>
            </section>

            <section class="app-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Periode: {{ formatDateRange(from, to) }}</div>
                <div class="overflow-x-auto">
                    <table class="app-table">
                        <thead><tr><th>Guru</th><th>NIP</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                            <tr v-for="row in rows" :key="`${row.teacher_id}-${row.attendance_status_id}`">
                                <td class="font-semibold text-slate-900 dark:text-white">{{ row.teacher?.full_name }}</td>
                                <td>{{ row.teacher?.nip ?? '-' }}</td>
                                <td>{{ row.status?.code }} - {{ row.status?.name }}</td>
                                <td>{{ row.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
