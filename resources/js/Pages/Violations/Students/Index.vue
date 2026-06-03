<script setup lang="ts">
import ThresholdBadge from '@/Components/App/ThresholdBadge.vue';
import Icon from '@/Components/App/Icon.vue';
import Pagination from '@/Components/App/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Paginated, Student, ViolationThreshold } from '@/types/domain';
import { formatDate } from '@/lib/datetime';
import { useDebouncedSearch } from '@/lib/useDebouncedSearch';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ violations: Paginated<any>; students: Student[]; filters: { status?: string; student_id?: string }; thresholds: ViolationThreshold[] }>();
const status = ref(props.filters.status ?? '');
const student_id = ref(props.filters.student_id ?? '');
const apply = () => router.get(route('tenant.student-violations.index', tenantParams()), { status: status.value, student_id: student_id.value }, { preserveState: true, preserveScroll: true, replace: true });
useDebouncedSearch([status, student_id], apply);
const statusLabels: Record<string, string> = { pending: 'Menunggu validasi', validated: 'Tervalidasi', rejected: 'Ditolak' };
const statusClasses: Record<string, string> = { pending: 'bg-amber-50 text-amber-700', validated: 'bg-emerald-50 text-emerald-700', rejected: 'bg-rose-50 text-rose-700' };
</script>
<template>
    <Head title="Pelanggaran Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Kedisiplinan</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Pelanggaran Siswa</h1>
        </template>
        <div class="space-y-5">
            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Pantau dan input pelanggaran</h2>
                        <p class="mt-1 text-sm text-slate-500">Filter berdasarkan status validasi atau nama siswa.</p>
                    </div>
                    <div class="flex flex-wrap gap-2"><Link :href="route('tenant.student-violations.pending', tenantParams())" class="app-button-secondary">Validasi Pending</Link><Link :href="route('tenant.student-violations.create', tenantParams())" class="app-button-primary">Input Pelanggaran</Link></div>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-[220px_1fr_auto]">
                    <select v-model="status" class="app-input"><option value="">Semua status</option><option value="pending">Menunggu validasi</option><option value="validated">Tervalidasi</option><option value="rejected">Ditolak</option></select>
                    <select v-model="student_id" class="app-input"><option value="">Semua siswa</option><option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }}</option></select>
                    <button type="button" class="app-button-secondary" @click="apply">Terapkan</button>
                </div>
            </section>
            <section class="app-card overflow-hidden">
                <div class="space-y-3 p-4 md:hidden"><div v-for="item in violations.data" :key="item.id" class="mobile-list-card"><div class="flex items-start justify-between gap-3"><div class="min-w-0"><p class="font-bold text-slate-900">{{ item.student?.name }}</p><p class="mt-1 text-sm text-slate-500">{{ item.student?.school_class?.display_name }} · {{ formatDate(item.date) }}</p></div><span class="app-badge shrink-0" :class="statusClasses[item.status] ?? 'bg-slate-100 text-slate-600'">{{ statusLabels[item.status] ?? item.status }}</span></div><p class="mt-4 text-sm font-semibold text-slate-800">{{ item.violation_type?.name }}</p><div class="mt-3 flex gap-2 border-t border-slate-100 pt-3 dark:border-slate-800"><ThresholdBadge :points="item.points_snapshot" :thresholds="thresholds" /></div></div><div v-if="!violations.data.length" class="py-8 text-center text-sm text-slate-500">Data pelanggaran belum ditemukan.</div></div>
                <div class="hidden overflow-x-auto md:block"><table class="app-table"><thead><tr><th>Siswa</th><th>Tanggal</th><th>Pelanggaran</th><th>Poin</th><th>Status</th></tr></thead><tbody><tr v-for="item in violations.data" :key="item.id"><td class="font-semibold text-slate-900">{{ item.student?.name }}<div class="text-xs font-normal text-slate-500">{{ item.student?.school_class?.display_name }}</div></td><td>{{ formatDate(item.date) }}</td><td>{{ item.violation_type?.name }}</td><td><ThresholdBadge :points="item.points_snapshot" :thresholds="thresholds" /></td><td><span class="app-badge" :class="statusClasses[item.status] ?? 'bg-slate-100 text-slate-600'">{{ statusLabels[item.status] ?? item.status }}</span></td></tr><tr v-if="!violations.data.length"><td colspan="5" class="py-8 text-center text-slate-500">Data pelanggaran belum ditemukan.</td></tr></tbody></table></div>
            </section>
            <Pagination :links="violations.links" />
        </div>
    </AuthenticatedLayout>
</template>
