<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const form = useForm({ category: '', name: '', points: 5, status: 'active', sort_order: 0 });
const submit = () => form.post(route('tenant.character-point-types.store', tenantParams()));
</script>
<template>
    <Head title="Tambah Jenis Poin Karakter" />
    <AuthenticatedLayout>
        <template #header><p class="page-kicker">Master apresiasi</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Tambah Jenis Poin Karakter</h1></template>
        <form class="app-card max-w-3xl space-y-5 p-6" @submit.prevent="submit">
            <div><h2 class="text-lg font-bold text-slate-900">Jenis poin baru</h2><p class="mt-1 text-sm text-slate-500">Contoh kategori: akhlak, ibadah, kedisiplinan, sosial, akademik.</p></div>
            <div class="grid gap-4 sm:grid-cols-2"><label class="text-sm font-semibold text-slate-700">Kategori<input v-model="form.category" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p></label><label class="text-sm font-semibold text-slate-700">Nama<input v-model="form.name" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p></label></div>
            <div class="grid gap-4 sm:grid-cols-3"><label class="text-sm font-semibold text-slate-700">Poin<input v-model="form.points" type="number" min="1" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.points }}</p></label><label class="text-sm font-semibold text-slate-700">Status<select v-model="form.status" class="app-input mt-2 w-full"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><p class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p></label><label class="text-sm font-semibold text-slate-700">Urutan<input v-model="form.sort_order" type="number" min="0" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.sort_order }}</p></label></div>
            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-5"><button class="app-button-primary" :disabled="form.processing">Simpan</button><Link :href="route('tenant.character-point-types.index', tenantParams())" class="app-button-secondary">Kembali</Link></div>
        </form>
    </AuthenticatedLayout>
</template>
