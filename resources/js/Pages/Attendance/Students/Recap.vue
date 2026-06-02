<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });

const props = defineProps<{ classes: any[]; statuses: any[]; rows: any[]; filters: { from: string; to: string; class_id?: string } }>();

const from = ref(props.filters.from);
const to = ref(props.filters.to);
const class_id = ref(props.filters.class_id ?? '');

const apply = () => router.get(route('tenant.attendance.students.recap', { tenant: school.value.slug }), { from: from.value, to: to.value, class_id: class_id.value }, { preserveState: true, preserveScroll: true, replace: true });
</script>

<template>
    <Head title="Rekap Absensi Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Rekap absensi</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Rekap Absensi Siswa</h1>
        </template>
        <div class="space-y-5">
            <section class="app-card p-5">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Filter rekap</h2>
                <p class="mt-1 text-sm text-slate-500">Periode: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ from }} — {{ to }}</span></p>
                <div class="mt-5 grid gap-3 md:grid-cols-[200px_200px_220px_auto]">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Dari<input v-model="from" type="date" class="app-input mt-2 w-full" /></label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Sampai<input v-model="to" type="date" class="app-input mt-2 w-full" /></label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Kelas<select v-model="class_id" class="app-input mt-2 w-full"><option value="">Semua</option><option v-for="item in classes" :key="item.id" :value="item.id">{{ item.display_name }}</option></select></label>
                    <div class="flex items-end"><button @click="apply" class="app-button-primary w-full md:w-auto">Tampilkan</button></div>
                </div>
            </section>
            <section class="app-card overflow-hidden">
                <div v-if="!rows.length" class="py-8 text-center text-sm text-slate-500">Belum ada data rekap.</div>
                <div v-else class="overflow-x-auto">
                    <table class="app-table">
                        <thead><tr><th>Siswa</th><th>NIS</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                            <tr v-for="row in rows" :key="`${row.student_id}-${row.attendance_status_id}`">
                                <td class="font-semibold text-slate-900 dark:text-white">{{ row.student?.name }}</td>
                                <td>{{ row.student?.nis }}</td>
                                <td>{{ row.status?.code }} - {{ row.status?.name }}</td>
                                <td><span class="app-badge bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">{{ row.total }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
