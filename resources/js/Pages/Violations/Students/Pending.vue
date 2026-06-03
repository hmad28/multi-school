<script setup lang="ts">
import Pagination from '@/Components/App/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Paginated } from '@/types/domain';
import { formatDate } from '@/lib/datetime';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

defineProps<{ violations: Paginated<any> }>();
const validateViolation = (id: string) => router.patch(route('tenant.student-violations.validate', { ...tenantParams(), studentViolation: id }), {}, { preserveScroll: true });
const rejectViolation = (id: string) => {
    const rejection_reason = prompt('Alasan penolakan?');
    if (rejection_reason) router.patch(route('tenant.student-violations.reject', { ...tenantParams(), studentViolation: id }), { rejection_reason }, { preserveScroll: true });
};
</script>
<template>
    <Head title="Validasi Pelanggaran" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Kedisiplinan</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Validasi Pelanggaran</h1>
        </template>
        <div class="space-y-5">
            <section class="app-card p-5">
                <h2 class="text-lg font-bold text-slate-900">Pelanggaran menunggu validasi</h2>
                <p class="mt-1 text-sm text-slate-500">BK/Admin dapat memvalidasi atau menolak data pelanggaran sebelum poin dihitung.</p>
            </section>
            <section class="app-card overflow-hidden">
                <div class="space-y-3 p-4 md:hidden"><div v-for="item in violations.data" :key="item.id" class="mobile-list-card"><div class="flex items-start justify-between gap-3"><div><p class="font-bold text-slate-900">{{ item.student?.name }}</p><p class="mt-1 text-sm text-slate-500">{{ item.student?.school_class?.display_name }} · {{ formatDate(item.date) }}</p></div><span class="app-badge bg-amber-50 text-amber-700">{{ item.points_snapshot }} poin</span></div><p class="mt-4 text-sm font-semibold text-slate-800">{{ item.violation_type?.name }}</p><div class="mt-4 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3"><button @click="validateViolation(item.id)" class="rounded-xl bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700">Validasi</button><button @click="rejectViolation(item.id)" class="rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700">Tolak</button></div></div><div v-if="!violations.data.length" class="py-8 text-center text-sm text-slate-500">Tidak ada pelanggaran menunggu validasi.</div></div>
                <div class="hidden overflow-x-auto md:block"><table class="app-table"><thead><tr><th>Siswa</th><th>Tanggal</th><th>Pelanggaran</th><th>Poin</th><th></th></tr></thead><tbody><tr v-for="item in violations.data" :key="item.id"><td class="font-semibold text-slate-900">{{ item.student?.name }}<div class="text-xs font-normal text-slate-500">{{ item.student?.school_class?.display_name }}</div></td><td>{{ formatDate(item.date) }}</td><td>{{ item.violation_type?.name }}</td><td><span class="app-badge bg-amber-50 text-amber-700">{{ item.points_snapshot }} poin</span></td><td class="space-x-3 text-right"><button @click="validateViolation(item.id)" class="font-semibold text-emerald-700 hover:text-emerald-800">Validasi</button><button @click="rejectViolation(item.id)" class="font-semibold text-rose-700 hover:text-rose-800">Tolak</button></td></tr><tr v-if="!violations.data.length"><td colspan="5" class="py-8 text-center text-slate-500">Tidak ada pelanggaran menunggu validasi.</td></tr></tbody></table></div>
            </section>
            <Pagination :links="violations.links" />
        </div>
    </AuthenticatedLayout>
</template>
