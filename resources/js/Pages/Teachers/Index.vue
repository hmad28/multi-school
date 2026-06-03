<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Teacher } from '@/types/domain';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

defineProps<{ teachers: Teacher[] }>();

const page = usePage();
const school = page.props.school as { slug: string };

function destroyTeacher(id: string): void {
    if (confirm('Hapus data guru ini?')) {
        router.delete(route('tenant.teachers.destroy', { tenant: school.slug, teacher: id }));
    }
}

function statusClass(status: string): string {
    return status === 'active' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600';
}

function attendanceClass(value: boolean): string {
    return value ? 'bg-brand-100 text-brand-700' : 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head title="Data Guru" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="page-kicker">Master Data</p>
                <h1 class="text-2xl font-bold text-ink dark:text-white">Data Guru</h1>
                <p class="text-sm text-slate-500">Kelola data guru dan wali kelas.</p>
            </div>
        </template>

        <section class="app-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-line p-5 dark:border-slate-800">
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ teachers.length }} guru</p>
                <Link :href="route('tenant.teachers.create', { tenant: school.slug })" class="app-button-primary">Tambah Guru</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead>
                        <tr><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Telepon</th><th>Input Absensi</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody v-if="teachers.length">
                        <tr v-for="teacher in teachers" :key="teacher.id">
                            <td class="font-semibold">{{ teacher.full_name }}</td>
                            <td>{{ teacher.nip ?? '-' }}</td>
                            <td>{{ teacher.position }}</td>
                            <td>{{ teacher.phone ?? '-' }}</td>
                            <td><span class="app-badge" :class="attendanceClass(teacher.can_input_teacher_attendance)">{{ teacher.can_input_teacher_attendance ? 'Ya' : 'Tidak' }}</span></td>
                            <td><span class="app-badge" :class="statusClass(teacher.status)">{{ teacher.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="space-x-3">
                                <Link :href="route('tenant.teachers.edit', { tenant: school.slug, teacher: teacher.id })" class="font-semibold text-brand-700">Edit</Link>
                                <button class="font-semibold text-rose-600" type="button" @click="destroyTeacher(teacher.id)">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr><td colspan="7" class="text-center text-slate-500">Belum ada data guru.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
