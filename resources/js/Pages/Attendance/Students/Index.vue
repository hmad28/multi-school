<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { AttendanceStatus, SchoolClass } from '@/types/domain';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });

const props = defineProps<{
    classes: SchoolClass[];
    statuses: AttendanceStatus[];
    selectedClass: SchoolClass | null;
    date: string;
    attendances: any[];
    submitted: boolean;
    holiday: { id: string; name: string; date: string } | null;
    lateCutoffPassed: boolean;
    effectiveSchoolDay: boolean;
    filters: { class_id: string; date: string; status_id: string; scan_state: 'scanned' | 'unscanned' | 'all' };
}>();

const tenantParams = (extra = {}) => ({ tenant: school.value.slug, ...extra });

const form = useForm({
    class_id: props.filters.class_id ?? props.selectedClass?.id ?? '',
    date: props.filters.date ?? props.date,
    status_id: props.filters.status_id ?? '',
    scan_state: props.filters.scan_state ?? 'scanned',
});
const correctionForms = useForm({ rows: props.attendances.map((attendance) => ({ id: attendance.id, attendance_status_id: attendance.attendance_status_id, note: attendance.note ?? '' })) });

const load = () => router.get(route('tenant.attendance.students.index', tenantParams()), form.data(), { preserveState: true });
const finalize = () => form.post(route('tenant.attendance.students.finalize', tenantParams()), { preserveScroll: true });
const correct = (attendance: any, index: number) => {
    if (!attendance.scanned) return;
    router.patch(route('tenant.attendance.students.correct', { ...tenantParams(), studentAttendance: attendance.id }), {
        attendance_status_id: correctionForms.rows[index].attendance_status_id,
        note: correctionForms.rows[index].note,
    }, { preserveScroll: true });
};
const time = (value?: string | null) => value ? new Date(value).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '-';
const statusClass = (attendance: any) => {
    if (attendance.status?.color) return attendance.status.color;
    if (attendance.scanned) return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    if (!props.effectiveSchoolDay) return 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300';
    if (!props.lateCutoffPassed) return 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300';
    return 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300';
};
const statusLabel = (attendance: any) => attendance.status ? `${attendance.status.code} - ${attendance.status.name}` : attendance.scan_state_label;
</script>

<template>
    <Head title="Absensi Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Operasional harian</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Absensi Siswa</h1>
        </template>

        <div class="space-y-5">
            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Roster absensi siswa</h2>
                        <p class="mt-1 text-sm text-slate-500">Default menampilkan siswa yang sudah scan. Ubah ke Belum scan untuk mencari siswa yang belum absen.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link :href="route('tenant.attendance.students.qr.index', tenantParams())" class="app-button-secondary">QR Scanner</Link>
                        <Link :href="route('tenant.attendance.students.recap', tenantParams())" class="app-button-secondary">Rekap</Link>
                    </div>
                </div>
                <div v-if="holiday" class="mt-5 rounded-2xl bg-sky-50 p-4 text-sm text-sky-800 dark:bg-sky-500/15 dark:text-sky-200">
                    <b>Hari libur:</b> {{ holiday.name }}. Siswa yang belum scan tidak dihitung Alpha untuk tanggal ini.
                </div>
                <div v-else-if="!lateCutoffPassed" class="mt-5 rounded-2xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-500/15 dark:text-amber-200">
                    Batas terlambat belum lewat. Siswa yang belum scan masih ditampilkan sebagai Belum scan, bukan Alpha.
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-[220px_180px_220px_180px_auto]">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Kelas<select v-model="form.class_id" class="app-input mt-2 w-full"><option value="">Semua kelas</option><option v-for="item in classes" :key="item.id" :value="item.id">{{ item.display_name }}</option></select></label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tanggal<input v-model="form.date" type="date" class="app-input mt-2 w-full" /></label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Status<select v-model="form.status_id" class="app-input mt-2 w-full"><option value="">Semua status</option><option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.code }} - {{ s.name }}</option></select></label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Scan<select v-model="form.scan_state" class="app-input mt-2 w-full"><option value="scanned">Sudah scan</option><option value="unscanned">Belum scan</option><option value="all">Semua</option></select></label>
                    <div class="flex items-end"><button type="button" @click="load" class="app-button-primary w-full md:w-auto">Tampilkan</button></div>
                </div>
            </section>

            <section class="app-card overflow-hidden">
                <div class="flex flex-col justify-between gap-4 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center dark:border-slate-800">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ selectedClass?.display_name ?? 'Semua kelas' }}</h2>
                        <p class="text-sm text-slate-500">{{ date }} · {{ attendances.length }} siswa tercatat <span v-if="selectedClass && submitted" class="font-semibold text-emerald-600">· Terkunci</span></p>
                    </div>
                    <button v-if="selectedClass && !submitted" type="button" @click="finalize" class="app-button-primary" :disabled="form.processing || attendances.length === 0">Kunci / Finalisasi Absensi</button>
                    <span v-else-if="selectedClass" class="app-badge bg-emerald-50 text-emerald-700">Terkunci</span>
                </div>

                <div v-if="!attendances.length" class="p-8 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-3xl bg-orange-50 text-[#FF5A1E] dark:bg-orange-500/15 dark:text-orange-300"><Icon name="calendar-check" class="h-7 w-7" /></div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">Belum ada siswa tercatat</h3>
                    <p class="mt-2 text-sm text-slate-500">{{ holiday ? 'Tanggal ini hari libur, jadi siswa tidak dihitung Alpha.' : 'Pilih kelas dan tanggal, lalu input status absensi untuk setiap siswa.' }}</p>
                </div>

                <div v-else class="space-y-3 p-4 md:hidden">
                    <div v-for="(attendance, index) in attendances" :key="attendance.id" class="mobile-list-card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">{{ attendance.student.name }}</p>
                                <p class="mt-1 text-sm text-slate-500">NIS {{ attendance.student.nis }}</p>
                                <p v-if="!selectedClass" class="mt-1 text-xs text-slate-500">{{ attendance.student.school_class?.display_name ?? attendance.student.schoolClass?.display_name }}</p>
                                <p class="mt-1 text-xs text-emerald-700">Datang {{ time(attendance.arrival_time) }}</p>
                                <p class="mt-1 text-xs text-sky-700">Pulang {{ time(attendance.departure_time) }}</p>
                            </div>
                            <span class="app-badge" :class="statusClass(attendance)">{{ statusLabel(attendance) }}</span>
                        </div>
                        <div class="mt-4 space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <template v-if="attendance.scanned">
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Status<select v-model="correctionForms.rows[index].attendance_status_id" class="app-input mt-2 w-full" :disabled="!submitted"><option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.code }} - {{ s.name }}</option></select></label>
                                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Catatan<input v-model="correctionForms.rows[index].note" class="app-input mt-2 w-full" placeholder="Catatan opsional" :disabled="!submitted" /></label>
                                <button v-if="submitted" type="button" @click="correct(attendance, index)" class="app-button-secondary w-full">Koreksi</button>
                            </template>
                            <p v-else class="rounded-2xl p-3 text-sm font-semibold" :class="statusClass(attendance)">{{ attendance.scan_state_label === 'Alpha' ? 'Belum scan pada tanggal ini. Ditampilkan sebagai Alpha.' : attendance.scan_state_label }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="attendances.length" class="hidden overflow-x-auto md:block">
                    <table class="app-table">
                        <thead><tr><th>Siswa</th><th>NIS</th><th>Datang</th><th>Pulang</th><th>Status</th><th>Catatan</th><th></th></tr></thead>
                        <tbody>
                            <tr v-for="(attendance, index) in attendances" :key="attendance.id">
                                <td class="font-semibold text-slate-900 dark:text-white"><div>{{ attendance.student.name }}</div><div v-if="!selectedClass" class="text-xs font-medium text-slate-500">{{ attendance.student.school_class?.display_name ?? attendance.student.schoolClass?.display_name }}</div></td>
                                <td>{{ attendance.student.nis }}</td>
                                <td>{{ time(attendance.arrival_time) }}</td>
                                <td>{{ time(attendance.departure_time) }}</td>
                                <td><select v-if="attendance.scanned" v-model="correctionForms.rows[index].attendance_status_id" class="app-input min-w-36" :disabled="!submitted"><option v-for="s in statuses" :key="s.id" :value="s.id">{{ s.code }} - {{ s.name }}</option></select><span v-else class="app-badge" :class="statusClass(attendance)">{{ attendance.status ? `${attendance.status.code} - ${attendance.status.name}` : 'A - Alpha' }}</span></td>
                                <td><input v-if="attendance.scanned" v-model="correctionForms.rows[index].note" class="app-input w-full min-w-48" placeholder="Catatan opsional" :disabled="!submitted" /><span v-else class="text-xs font-semibold text-slate-400">Belum ada catatan</span></td>
                                <td class="text-right">
                                    <button v-if="attendance.scanned && submitted" type="button" @click="correct(attendance, index)" class="font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-300 dark:hover:text-indigo-200">Koreksi</button>
                                    <span v-else class="text-xs font-semibold text-slate-400">Belum scan</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
