<script setup lang="ts">
import Pagination from '@/Components/App/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatDate } from '@/lib/datetime';
import { useDebouncedSearch } from '@/lib/useDebouncedSearch';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ points: any; students: any[]; filters: { student_id?: string; category?: string }; categories: string[]; totals: Record<string, number>; canInput: boolean; canManageTypes: boolean }>();
const student_id = ref(props.filters.student_id ?? '');
const category = ref(props.filters.category ?? '');
const apply = () => router.get(route('tenant.student-character-points.index', tenantParams()), { student_id: student_id.value, category: category.value }, { preserveState: true, preserveScroll: true, replace: true });
useDebouncedSearch([student_id, category], apply);
</script>
<template>
    <Head title="Poin Karakter" />
    <AuthenticatedLayout>
        <template #header><p class="page-kicker">Apresiasi siswa</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Poin Karakter</h1></template>
        <div class="space-y-5">
            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end"><div><h2 class="text-lg font-bold text-slate-900">Catatan poin kebaikan</h2><p class="mt-1 text-sm text-slate-500">Pantau perilaku positif siswa per semester aktif.</p></div><div class="flex flex-wrap gap-2"><Link v-if="canInput" :href="route('tenant.student-character-points.create', tenantParams())" class="app-button-primary">Input Poin</Link><Link v-if="canManageTypes" :href="route('tenant.character-point-types.index', tenantParams())" class="app-button-secondary">Jenis Poin</Link></div></div>
                <div class="mt-5 grid gap-3 md:grid-cols-[1fr_220px_auto]"><select v-model="student_id" class="app-input"><option value="">Semua siswa</option><option v-for="student in students" :key="student.id" :value="student.id">{{ student.full_name }}</option></select><select v-model="category" class="app-input"><option value="">Semua kategori</option><option v-for="item in categories" :key="item" :value="item">{{ item }}</option></select><button type="button" class="app-button-secondary" @click="apply">Terapkan</button></div>
            </section>
            <section class="app-card overflow-hidden">
                <div class="space-y-3 p-4 md:hidden"><div v-for="item in points.data" :key="item.id" class="mobile-list-card"><div class="flex items-start justify-between gap-3"><div><p class="font-bold text-slate-900">{{ item.student?.full_name }}</p><p class="mt-1 text-sm text-slate-500">{{ item.student?.school_class?.display_name }} · {{ formatDate(item.date) }}</p></div><span class="app-badge bg-emerald-50 text-emerald-700">+{{ item.points_snapshot }}</span></div><p class="mt-4 text-sm font-semibold text-slate-800">{{ item.character_point_type?.name }}</p><p v-if="item.note" class="mt-2 text-sm text-slate-500">{{ item.note }}</p><p class="mt-3 text-xs font-semibold text-slate-500">Total semester: {{ totals[item.student_id] ?? 0 }} poin</p></div><div v-if="!points.data.length" class="py-8 text-center text-sm text-slate-500">Data poin karakter belum ditemukan.</div></div>
                <div class="hidden overflow-x-auto md:block"><table class="app-table"><thead><tr><th>Siswa</th><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Poin</th><th>Total Semester</th><th>Dicatat oleh</th></tr></thead><tbody><tr v-for="item in points.data" :key="item.id"><td class="font-semibold text-slate-900">{{ item.student?.full_name }}<div class="text-xs font-normal text-slate-500">{{ item.student?.school_class?.display_name }}</div></td><td>{{ formatDate(item.date) }}</td><td>{{ item.character_point_type?.name }}</td><td>{{ item.category_snapshot }}</td><td><span class="app-badge bg-emerald-50 text-emerald-700">+{{ item.points_snapshot }}</span></td><td>{{ totals[item.student_id] ?? 0 }}</td><td>{{ item.recorder?.name ?? '-' }}</td></tr><tr v-if="!points.data.length"><td colspan="7" class="py-8 text-center text-slate-500">Data poin karakter belum ditemukan.</td></tr></tbody></table></div>
            </section>
            <Pagination :links="points.links" />
        </div>
    </AuthenticatedLayout>
</template>
