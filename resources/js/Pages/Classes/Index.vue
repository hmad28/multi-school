<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps<{ classes: any[] }>();
const page = usePage();
const school = page.props.school as { slug: string };

function destroyClass(id: string): void {
    if (confirm('Hapus data kelas ini?')) {
        router.delete(route('tenant.classes.destroy', { tenant: school.slug, class: id }));
    }
}

function statusClass(status: string): string {
    return status === 'active' ? 'bg-sky-100 text-sky-700' : 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head title="Data Kelas" />
    <AuthenticatedLayout>
        <template #header><div><p class="page-kicker">Master Data</p><h1 class="text-2xl font-bold text-ink dark:text-white">Data Kelas</h1><p class="text-sm text-slate-500">Kelola rombel dan wali kelas.</p></div></template>
        <section class="app-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 border-b border-line p-5 dark:border-slate-800"><p class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ classes.length }} kelas</p><Link :href="route('tenant.classes.create', { tenant: school.slug })" class="app-button-primary">Tambah Kelas</Link></div>
            <div class="overflow-x-auto">
                <table class="app-table">
                    <thead><tr><th>Kelas</th><th>Tingkat</th><th>Wali kelas</th><th>Siswa</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody v-if="classes.length">
                        <tr v-for="item in classes" :key="item.id">
                            <td class="font-semibold">{{ item.display_name }}</td>
                            <td>{{ item.academic_level?.name ?? '-' }}</td>
                            <td>{{ item.homeroom_teacher?.full_name ?? '-' }}</td>
                            <td>{{ item.students_count }}</td>
                            <td><span class="app-badge" :class="statusClass(item.status)">{{ item.status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
                            <td class="space-x-3"><Link :href="route('tenant.classes.edit', { tenant: school.slug, class: item.id })" class="font-semibold text-brand-700">Edit</Link><button type="button" class="font-semibold text-rose-600" @click="destroyClass(item.id)">Hapus</button></td>
                        </tr>
                    </tbody>
                    <tbody v-else><tr><td colspan="6" class="text-center text-slate-500">Belum ada data kelas.</td></tr></tbody>
                </table>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
