<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const page = usePage();
const school = page.props.school as { slug: string };
const props = defineProps<{ students: any[] }>();

function destroyStudent(id: string): void {
    if (confirm('Hapus data siswa ini?')) {
        router.delete(route('tenant.students.destroy', { tenant: school.slug, student: id }));
    }
}

function statusClass(status: string): string {
    return status === 'active' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head title="Data Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="page-kicker">Master Data</p>
                <h1 class="text-2xl font-bold text-ink dark:text-white">Data Siswa</h1>
                <p class="text-sm text-slate-500">Kelola biodata siswa per sekolah.</p>
            </div>
        </template>

        <section class="app-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-line p-5 dark:border-slate-800">
                <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ students.length }} siswa</p>
                <Link :href="route('tenant.students.create', { tenant: school.slug })" class="app-button-primary">Tambah Siswa</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead><tr><th>Nama</th><th>NIS</th><th>NISN</th><th>Kelas</th><th>Wali</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody v-if="students.length">
                        <tr v-for="student in students" :key="student.id">
                            <td class="font-semibold">{{ student.name }}</td>
                            <td>{{ student.nis }}</td>
                            <td>{{ student.nisn ?? '-' }}</td>
                            <td>{{ student.school_class?.display_name ?? student.school_class?.name ?? '-' }}</td>
                            <td>{{ student.guardian_name ?? '-' }}</td>
                            <td><span class="app-badge" :class="statusClass(student.status)">{{ student.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="space-x-3">
                                <Link :href="route('tenant.students.show', { tenant: school.slug, student: student.id })" class="font-semibold text-slate-600">Detail</Link>
                                <Link :href="route('tenant.students.edit', { tenant: school.slug, student: student.id })" class="font-semibold text-brand-700">Edit</Link>
                                <button type="button" class="font-semibold text-rose-600" @click="destroyStudent(student.id)">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else><tr><td colspan="7" class="text-center text-slate-500">Belum ada data siswa.</td></tr></tbody>
                </table>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
