<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import Pagination from '@/Components/App/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

defineProps<{ types: any; filters: { category?: string } }>();
const openDetail = (type: any) => router.visit(route('tenant.violation-types.edit', { ...tenantParams(), violationType: type.id }));

const categoryClasses: Record<string, string> = {
    ringan: 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
    sedang: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    berat: 'bg-red-50 text-red-700 ring-1 ring-red-200',
};
</script>
<template><Head title="Jenis Pelanggaran" /><AuthenticatedLayout><template #header><p class="page-kicker">Kedisiplinan</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Jenis Pelanggaran</h1></template><div class="space-y-5"><section class="app-card p-5"><div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end"><div><h2 class="text-lg font-bold text-slate-900">Daftar jenis pelanggaran</h2><p class="mt-1 text-sm text-slate-500">Klik jenis pelanggaran untuk melihat detail, edit, atau remove.</p></div><Link :href="route('tenant.violation-types.create', tenantParams())" class="app-button-primary gap-2"><Icon name="plus" class="h-4 w-4" />Tambah Jenis</Link></div></section><section class="app-card overflow-hidden"><div class="space-y-3 p-4 md:hidden"><div v-for="type in types.data" :key="type.id" role="button" tabindex="0" class="mobile-list-card cursor-pointer transition hover:border-brand-700/40 hover:shadow-md" @click="openDetail(type)" @keydown.enter.prevent="openDetail(type)" @keydown.space.prevent="openDetail(type)"><div class="flex items-start justify-between gap-3"><div><p class="font-bold text-slate-900">{{ type.name }}</p><p class="mt-1 text-sm text-slate-500">{{ type.points }} poin · Urutan {{ type.sort_order }}</p></div><span class="app-badge capitalize" :class="categoryClasses[type.category] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'">{{ type.category }}</span></div><div class="mt-4 border-t border-slate-100 pt-3"><span class="app-badge" :class="type.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ type.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></div></div><div v-if="!types.data.length" class="py-8 text-center text-sm text-slate-500">Belum ada jenis pelanggaran.</div></div><div class="hidden overflow-x-auto md:block"><table class="app-table"><thead><tr><th>Nama</th><th>Kategori</th><th>Poin</th><th>Status</th><th>Urutan</th></tr></thead><tbody><tr v-for="type in types.data" :key="type.id" role="button" tabindex="0" class="cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-700" @click="openDetail(type)" @keydown.enter.prevent="openDetail(type)" @keydown.space.prevent="openDetail(type)"><td class="font-semibold text-slate-900">{{ type.name }}</td><td><span class="app-badge capitalize" :class="categoryClasses[type.category] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'">{{ type.category }}</span></td><td>{{ type.points }}</td><td><span class="app-badge" :class="type.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ type.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td><td>{{ type.sort_order }}</td></tr><tr v-if="!types.data.length"><td colspan="5" class="py-8 text-center text-slate-500">Belum ada jenis pelanggaran.</td></tr></tbody></table></div></section><Pagination :links="types.links" /></div></AuthenticatedLayout></template>
