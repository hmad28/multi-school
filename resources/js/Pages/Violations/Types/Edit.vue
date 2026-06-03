<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { CatalogType } from '@/types/domain';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ type: CatalogType }>();
const form = useForm({ category: props.type.category, name: props.type.name, points: props.type.points, status: props.type.status, sort_order: props.type.sort_order });
const submit = () => form.put(route('tenant.violation-types.update', { ...tenantParams(), violationType: props.type.id }));
const destroy = () => { if (confirm('Hapus jenis pelanggaran ini?')) router.delete(route('tenant.violation-types.destroy', { ...tenantParams(), violationType: props.type.id })); };
const categoryClasses: Record<string, string> = {
    ringan: 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200',
    sedang: 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
    berat: 'bg-red-50 text-red-700 ring-1 ring-red-200',
};
const statusLabel = (value: string) => value === 'active' ? 'Aktif' : 'Nonaktif';
</script>
<template>
    <Head title="Detail Jenis Pelanggaran" />
    <AuthenticatedLayout>
        <template #header><p class="page-kicker">Detail kedisiplinan</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Detail Jenis Pelanggaran</h1></template>
        <div class="space-y-5">
            <section class="app-card p-5"><div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start"><div><p class="page-kicker">Jenis Pelanggaran</p><h2 class="mt-1 text-2xl font-bold text-slate-900">{{ type.name }}</h2><p class="mt-1 text-sm text-slate-500">{{ type.points }} poin · Urutan {{ type.sort_order }}</p></div><div class="flex flex-wrap gap-2"><span class="app-badge capitalize" :class="categoryClasses[type.category] ?? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'">{{ type.category }}</span><span class="app-badge" :class="type.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ statusLabel(type.status) }}</span></div></div></section>
            <form @submit.prevent="submit" class="app-card space-y-5 p-5">
                <div><h2 class="text-lg font-bold text-slate-900">Data jenis pelanggaran</h2><p class="mt-1 text-sm text-slate-500">Ubah kategori, nama, poin, status, atau urutan lalu simpan.</p></div>
                <div class="grid gap-4 md:grid-cols-2"><label class="text-sm font-semibold text-slate-700">Kategori<select v-model="form.category" class="app-input mt-2 w-full"><option value="ringan">Ringan</option><option value="sedang">Sedang</option><option value="berat">Berat</option></select><p class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p></label><label class="text-sm font-semibold text-slate-700">Nama<input v-model="form.name" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p></label><label class="text-sm font-semibold text-slate-700">Poin<input v-model="form.points" type="number" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.points }}</p></label><label class="text-sm font-semibold text-slate-700">Status<select v-model="form.status" class="app-input mt-2 w-full"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><p class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p></label><label class="text-sm font-semibold text-slate-700">Urutan<input v-model="form.sort_order" type="number" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p></label></div>
                <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-5"><button class="app-button-primary gap-2" :disabled="form.processing"><Icon name="save" class="h-4 w-4" />Simpan</button><button type="button" @click="destroy" class="app-button-danger gap-2"><Icon name="trash" class="h-4 w-4" />Remove</button><Link :href="route('tenant.violation-types.index', tenantParams())" class="app-button-secondary gap-2"><Icon name="arrow-left" class="h-4 w-4" />Kembali</Link></div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
