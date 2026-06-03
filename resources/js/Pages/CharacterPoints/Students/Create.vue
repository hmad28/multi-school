<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { CatalogType, Student } from '@/types/domain';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

defineProps<{ students: Student[]; types: CatalogType[] }>();
const form = useForm({ student_id: '', character_point_type_id: '', date: new Date().toISOString().slice(0, 10), note: '' });
const submit = () => form.post(route('tenant.student-character-points.store', tenantParams()));
</script>
<template>
    <Head title="Input Poin Karakter" />
    <AuthenticatedLayout>
        <template #header><p class="page-kicker">Apresiasi siswa</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Input Poin Karakter</h1></template>
        <form class="app-card max-w-3xl space-y-5 p-6" @submit.prevent="submit">
            <div><h2 class="text-lg font-bold text-slate-900">Catat perilaku positif</h2><p class="mt-1 text-sm text-slate-500">Poin tersimpan sebagai riwayat semester dan tidak mengurangi poin pelanggaran.</p></div>
            <label class="block text-sm font-semibold text-slate-700">Siswa<select v-model="form.student_id" class="app-input mt-2 w-full"><option value="">Pilih siswa</option><option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }} · {{ student.nis }} · {{ student.school_class?.display_name }}</option></select><p class="mt-1 text-sm text-red-600">{{ form.errors.student_id }}</p></label>
            <label class="block text-sm font-semibold text-slate-700">Jenis Poin<select v-model="form.character_point_type_id" class="app-input mt-2 w-full"><option value="">Pilih jenis poin</option><option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }} · {{ type.category }} · +{{ type.points }} poin</option></select><p class="mt-1 text-sm text-red-600">{{ form.errors.character_point_type_id }}</p></label>
            <label class="block text-sm font-semibold text-slate-700">Tanggal<input v-model="form.date" type="date" class="app-input mt-2 w-full" /><p class="mt-1 text-sm text-red-600">{{ form.errors.date }}</p></label>
            <label class="block text-sm font-semibold text-slate-700">Catatan<textarea v-model="form.note" class="app-input mt-2 w-full" rows="4" placeholder="Contoh: membantu teman, menjaga kebersihan, hafalan baik" /><p class="mt-1 text-sm text-red-600">{{ form.errors.note }}</p></label>
            <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-5"><button class="app-button-primary" :disabled="form.processing">Simpan Poin</button><Link :href="route('tenant.student-character-points.index', tenantParams())" class="app-button-secondary">Kembali</Link></div>
        </form>
    </AuthenticatedLayout>
</template>
