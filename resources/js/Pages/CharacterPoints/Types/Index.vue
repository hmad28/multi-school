<script setup lang="ts">
import Pagination from '@/Components/App/Pagination.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { CatalogType, Paginated } from '@/types/domain';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ types: Paginated<CatalogType>; filters: { category?: string } }>();
const category = ref(props.filters.category ?? '');
const apply = () => router.get(route('tenant.character-point-types.index', tenantParams()), { category: category.value }, { preserveState: true, preserveScroll: true, replace: true });
</script>
<template>
    <Head title="Jenis Poin Karakter" />
    <AuthenticatedLayout>
        <template #header><p class="page-kicker">Master apresiasi</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Jenis Poin Karakter</h1></template>
        <div class="space-y-5">
            <section class="app-card p-5"><div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end"><div><h2 class="text-lg font-bold text-slate-900">Kategori dan nilai poin</h2><p class="mt-1 text-sm text-slate-500">Dipakai saat guru mencatat perilaku positif siswa.</p></div><Link :href="route('tenant.character-point-types.create', tenantParams())" class="app-button-primary">Tambah Jenis</Link></div><div class="mt-5 grid gap-3 md:grid-cols-[240px_auto]"><input v-model="category" class="app-input" placeholder="Filter kategori" /><button type="button" class="app-button-secondary" @click="apply">Terapkan</button></div></section>
            <section class="app-card overflow-hidden"><div class="hidden overflow-x-auto md:block"><table class="app-table"><thead><tr><th>Nama</th><th>Kategori</th><th>Poin</th><th>Status</th><th></th></tr></thead><tbody><tr v-for="type in types.data" :key="type.id"><td class="font-semibold text-slate-900">{{ type.name }}</td><td>{{ type.category }}</td><td>+{{ type.points }}</td><td><span class="app-badge" :class="type.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ type.status }}</span></td><td class="text-right"><Link :href="route('tenant.character-point-types.edit', { ...tenantParams(), characterPointType: type.id })" class="font-semibold text-brand-700 hover:text-brand-800">Edit</Link></td></tr><tr v-if="!types.data.length"><td colspan="5" class="py-8 text-center text-slate-500">Jenis poin belum ditemukan.</td></tr></tbody></table></div><div class="space-y-3 p-4 md:hidden"><div v-for="type in types.data" :key="type.id" class="mobile-list-card"><div class="flex items-start justify-between gap-3"><div><p class="font-bold text-slate-900">{{ type.name }}</p><p class="mt-1 text-sm text-slate-500">{{ type.category }} · +{{ type.points }} poin</p></div><Link :href="route('tenant.character-point-types.edit', { ...tenantParams(), characterPointType: type.id })" class="font-semibold text-brand-700">Edit</Link></div></div><div v-if="!types.data.length" class="py-8 text-center text-sm text-slate-500">Jenis poin belum ditemukan.</div></div></section>
            <Pagination :links="types.links" />
        </div>
    </AuthenticatedLayout>
</template>
