<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { CatalogType } from '@/types/domain';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ type: CatalogType }>();
const form = useForm({ category: props.type.category, name: props.type.name, points: props.type.points, status: props.type.status, sort_order: props.type.sort_order ?? 0 });
const submit = () => form.put(route('tenant.character-point-types.update', { ...tenantParams(), characterPointType: props.type.id }));
const remove = () => { if (confirm('Hapus jenis poin karakter ini?')) router.delete(route('tenant.character-point-types.destroy', { ...tenantParams(), characterPointType: props.type.id })); };
</script>
<template>
    <Head title="Edit Jenis Poin Karakter" />
    <AuthenticatedLayout>
        <template #header><p class="page-kicker">Master apresiasi</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Edit Jenis Poin Karakter</h1></template>
        <form class="app-card max-w-3xl space-y-5 p-6" @submit.prevent="submit">
            <div><h2 class="text-lg font-bold text-slate-900">{{ type.name }}</h2><p class="mt-1 text-sm text-slate-500">Riwayat poin lama tetap memakai snapshot nilai saat dicatat.</p></div>
            <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-700">Kategori<input v-model="form.category" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p></label><label class="text-sm font-semibold text-slate-700">Nama<input v-model="form.name" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p></label></div>
            <div class="grid gap-4 sm:grid-cols-3"><label class="text-sm font-semibold text-slate-700">Poin<input v-model="form.points" type="number" min="1" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.points }}</p></label><label class="text-sm font-semibold text-slate-700">Status<select v-model="form.status" class="app-input mt-2 w-full"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><p class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p></label><label class="text-sm font-semibold text-slate-700">Urutan<input v-model="form.sort_order" type="number" min="0" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p></label></div>
            <div class="flex flex-wrap justify-between gap-2 border-t border-slate-100 pt-5"><div class="flex gap-2"><button class="app-button-primary" :disabled="form.processing">Simpan</button><Link :href="route('tenant.character-point-types.index', tenantParams())" class="app-button-secondary">Kembali</Link></div><button type="button" class="rounded-2xl bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700" @click="remove">Hapus</button></div>
        </form>
    </AuthenticatedLayout>
</template>
