<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Student } from '@/types/domain';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps<{ student: Student }>();
const page = usePage();
const school = page.props.school as { slug: string };
</script>

<template>
    <Head title="Detail Siswa" />
    <AuthenticatedLayout>
        <template #header><div><p class="page-kicker">Master Data</p><h1 class="text-2xl font-bold text-ink dark:text-white">Detail Siswa</h1><p class="text-sm text-slate-500">{{ student.name }}</p></div></template>
        <section class="app-card max-w-3xl p-6">
            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">Nama</dt><dd class="font-semibold text-ink dark:text-white">{{ student.name }}</dd></div>
                <div><dt class="text-slate-500">NIS</dt><dd>{{ student.nis }}</dd></div>
                <div><dt class="text-slate-500">NISN</dt><dd>{{ student.nisn ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Kelas</dt><dd>{{ student.school_class?.display_name ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Gender</dt><dd>{{ student.gender === 'male' ? 'Laki-laki' : student.gender === 'female' ? 'Perempuan' : '-' }}</dd></div>
                <div><dt class="text-slate-500">Status</dt><dd>{{ student.status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd></div>
                <div><dt class="text-slate-500">Nama wali</dt><dd>{{ student.guardian_name ?? '-' }}</dd></div>
                <div><dt class="text-slate-500">Telepon wali</dt><dd>{{ student.guardian_phone ?? '-' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">Alamat</dt><dd>{{ student.address ?? '-' }}</dd></div>
            </dl>
            <div class="mt-6 flex gap-3"><Link :href="route('tenant.students.edit', { tenant: school.slug, student: student.id })" class="app-button-primary">Edit</Link><Link :href="route('tenant.students.index', { tenant: school.slug })" class="app-button-secondary">Kembali</Link></div>
        </section>
    </AuthenticatedLayout>
</template>
