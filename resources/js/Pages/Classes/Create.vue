<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { AcademicLevel, Teacher } from '@/types/domain';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{ levels: AcademicLevel[]; teachers: Pick<Teacher, 'id' | 'full_name'>[] }>();
const page = usePage();
const school = page.props.school as { slug: string };
const form = useForm({ academic_level_id: '', name: '', homeroom_teacher_id: '', status: 'active', sort_order: 0 });

function submit(): void { form.post(route('tenant.classes.store', { tenant: school.slug })); }
</script>

<template>
    <Head title="Tambah Kelas" />
    <AuthenticatedLayout>
        <template #header><div><p class="page-kicker">Master Data</p><h1 class="text-2xl font-bold text-ink dark:text-white">Tambah Kelas</h1><p class="text-sm text-slate-500">Lengkapi data kelas.</p></div></template>
        <form class="app-card max-w-3xl space-y-5 p-6" @submit.prevent="submit">
            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2"><span class="text-sm font-semibold">Tingkat</span><select v-model="form.academic_level_id" class="app-input w-full"><option value="">Pilih tingkat</option><option v-for="level in levels" :key="level.id" :value="level.id">{{ level.name }}</option></select><span class="text-sm text-rose-600">{{ form.errors.academic_level_id }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Nama kelas</span><input v-model="form.name" class="app-input w-full" placeholder="A" /><span class="text-sm text-rose-600">{{ form.errors.name }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Wali kelas</span><select v-model="form.homeroom_teacher_id" class="app-input w-full"><option value="">Tanpa wali</option><option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">{{ teacher.full_name }}</option></select><span class="text-sm text-rose-600">{{ form.errors.homeroom_teacher_id }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Urutan</span><input v-model="form.sort_order" type="number" min="0" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.sort_order }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Status</span><select v-model="form.status" class="app-input w-full"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><span class="text-sm text-rose-600">{{ form.errors.status }}</span></label>
            </div>
            <div class="flex gap-3"><button class="app-button-primary" type="submit" :disabled="form.processing">Simpan</button><Link :href="route('tenant.classes.index', { tenant: school.slug })" class="app-button-secondary">Batal</Link></div>
        </form>
    </AuthenticatedLayout>
</template>
