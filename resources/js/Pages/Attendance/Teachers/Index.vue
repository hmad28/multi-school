<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { AttendanceStatus, Teacher } from '@/types/domain';
import { formatDate } from '@/lib/datetime';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const props = defineProps<{ date: string; statuses: AttendanceStatus[]; teachers: Teacher[]; attendances: Record<string, any>; submitted: boolean }>();
const present = computed(() => props.statuses.find((status) => status.code === 'H')?.id ?? props.statuses[0]?.id);
const form = useForm({ date: props.date, attendances: [] as any[] });

const resetRows = () => {
    form.attendances = props.teachers.map((teacher) => ({
        teacher_id: teacher.id,
        attendance_status_id: props.attendances[teacher.id]?.attendance_status_id ?? present.value,
        note: props.attendances[teacher.id]?.note ?? '',
    }));
};

watch(() => props.teachers, resetRows, { immediate: true });
const load = () => router.get(route('tenant.attendance.teachers.index', tenantParams()), { date: form.date }, { preserveState: true });
const submit = () => form.post(route('tenant.attendance.teachers.store', tenantParams()));
const correct = (row: any) => router.patch(route('tenant.attendance.teachers.correct', { ...tenantParams(), teacherAttendance: props.attendances[row.teacher_id].id }), { attendance_status_id: row.attendance_status_id, note: row.note }, { preserveScroll: true });
</script>

<template>
    <Head title="Absensi Guru" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Operasional harian</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Absensi Guru</h1>
        </template>

        <div class="space-y-5">
            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Pilih tanggal absensi</h2>
                        <p class="mt-1 text-sm text-slate-500">Tanggal dipilih: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ formatDate(form.date) }}</span></p>
                    </div>
                </div>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tanggal<input v-model="form.date" type="date" class="app-input mt-2 w-full sm:w-56" /></label>
                    <button type="button" @click="load" class="app-button-secondary">Tampilkan</button>
                    <button v-if="!submitted" type="button" @click="submit" class="app-button-primary" :disabled="form.processing">Kirim Absensi</button>
                    <span v-else class="app-badge bg-emerald-50 text-emerald-700">Sudah dikirim</span>
                </div>
            </section>

            <section class="app-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">Tanggal absensi: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ formatDate(date) }}</span></div>
                <div class="space-y-3 p-4 md:hidden">
                    <div v-for="(teacher, index) in teachers" :key="teacher.id" class="mobile-list-card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">{{ teacher.full_name }}</p>
                                <p class="mt-1 text-sm text-slate-500">NIP {{ teacher.nip ?? '-' }}</p>
                            </div>
                            <button v-if="submitted && attendances[teacher.id]" type="button" @click="correct(form.attendances[index])" class="rounded-full bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700">Koreksi</button>
                        </div>
                        <div class="mt-4 space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Status<select v-model="form.attendances[index].attendance_status_id" class="app-input mt-2 w-full"><option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.code }} - {{ status.name }}</option></select></label>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Catatan<input v-model="form.attendances[index].note" class="app-input mt-2 w-full" placeholder="Catatan opsional" /></label>
                        </div>
                    </div>
                </div>
                <div class="hidden overflow-x-auto md:block">
                    <table class="app-table">
                        <thead><tr><th>Guru</th><th>NIP</th><th>Status</th><th>Catatan</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="(teacher, index) in teachers" :key="teacher.id">
                                <td class="font-semibold text-slate-900 dark:text-white">{{ teacher.full_name }}</td>
                                <td>{{ teacher.nip ?? '-' }}</td>
                                <td><select v-model="form.attendances[index].attendance_status_id" class="app-input min-w-36"><option v-for="status in statuses" :key="status.id" :value="status.id">{{ status.code }} - {{ status.name }}</option></select></td>
                                <td><input v-model="form.attendances[index].note" class="app-input w-full min-w-48" placeholder="Catatan opsional" /></td>
                                <td class="text-right"><button v-if="submitted && attendances[teacher.id]" type="button" @click="correct(form.attendances[index])" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">Koreksi</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
