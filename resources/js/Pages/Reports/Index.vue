<script setup lang="ts">
import { formatDateRange } from '@/lib/datetime';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { DateRangeFilters, SchoolClass, Student } from '@/types/domain';
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{ classes: SchoolClass[]; students: Pick<Student, 'id' | 'name' | 'nis'>[]; filters: DateRangeFilters }>();
const from = ref(props.filters.from);
const to = ref(props.filters.to);
const classId = ref('');
const status = ref('');
const studentId = ref('');
const period = computed(() => formatDateRange(from.value, to.value));
const tenantParams = (extra = {}) => ({ ...extra });

const openPdf = (name: string, params: Record<string, string>) => window.open(route(name, params), '_blank');
const openExcel = (name: string, params: Record<string, string>) => window.open(route(name, params), '_blank');
</script>

<template>
    <Head title="Laporan" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Cetak dokumen</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Laporan</h1>
        </template>

        <div class="space-y-5">
            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Filter periode laporan</h2>
                        <p class="mt-1 text-sm text-slate-500">Periode aktif: <span class="font-semibold text-slate-700">{{ period }}</span></p>
                    </div>
                    <span class="app-badge bg-indigo-50 text-indigo-700">PDF siap cetak</span>
                </div>
                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <label class="text-sm font-semibold text-slate-700">Dari<input v-model="from" type="date" class="app-input mt-2 w-full" /></label>
                    <label class="text-sm font-semibold text-slate-700">Sampai<input v-model="to" type="date" class="app-input mt-2 w-full" /></label>
                    <label class="text-sm font-semibold text-slate-700">Kelas<select v-model="classId" class="app-input mt-2 w-full"><option value="">Semua kelas</option><option v-for="item in classes" :key="item.id" :value="item.id">{{ item.display_name }}</option></select></label>
                    <label class="text-sm font-semibold text-slate-700">Status Pelanggaran<select v-model="status" class="app-input mt-2 w-full"><option value="">Semua status</option><option value="pending">Menunggu validasi</option><option value="validated">Tervalidasi</option><option value="rejected">Ditolak</option></select></label>
                </div>
            </section>

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="app-card p-5 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                    <span class="app-badge bg-indigo-50 text-indigo-700">Absensi</span>
                    <div class="mt-4 text-lg font-bold text-slate-900">Absensi Siswa</div>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Rekap absensi siswa berdasarkan periode dan kelas.</p>
                    <div class="mt-4 flex gap-2"><button type="button" class="app-button-primary flex-1" @click="openPdf('tenant.reports.student-attendance', { from, to, class_id: classId })">PDF</button><button type="button" class="app-button-secondary flex-1" @click="openExcel('tenant.reports.student-attendance.excel', { from, to, class_id: classId })">Excel</button></div>
                </div>
                <div class="app-card p-5 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md">
                    <span class="app-badge bg-emerald-50 text-emerald-700">Guru</span>
                    <div class="mt-4 text-lg font-bold text-slate-900">Absensi Guru</div>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Rekap absensi guru berdasarkan periode.</p>
                    <div class="mt-4 flex gap-2"><button type="button" class="app-button-primary flex-1" @click="openPdf('tenant.reports.teacher-attendance', { from, to })">PDF</button><button type="button" class="app-button-secondary flex-1" @click="openExcel('tenant.reports.teacher-attendance.excel', { from, to })">Excel</button></div>
                </div>
                <div class="app-card p-5 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md">
                    <span class="app-badge bg-amber-50 text-amber-700">Pelanggaran</span>
                    <div class="mt-4 text-lg font-bold text-slate-900">Pelanggaran</div>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Rekap pelanggaran siswa berdasarkan periode dan status.</p>
                    <div class="mt-4 flex gap-2"><button type="button" class="app-button-primary flex-1" @click="openPdf('tenant.reports.violations', { from, to, status })">PDF</button><button type="button" class="app-button-secondary flex-1" @click="openExcel('tenant.reports.violations.excel', { from, to, status })">Excel</button></div>
                </div>
                <div class="app-card p-5 transition hover:-translate-y-0.5 hover:border-lime-200 hover:shadow-md">
                    <span class="app-badge bg-lime-50 text-lime-700">Karakter</span>
                    <div class="mt-4 text-lg font-bold text-slate-900">Poin Karakter</div>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Rekap poin kebaikan siswa berdasarkan periode.</p>
                    <div class="mt-4"><button type="button" class="app-button-secondary w-full" @click="openExcel('tenant.reports.character-points.excel', { from, to })">Excel</button></div>
                </div>
            </section>

            <section class="app-card p-5">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Surat Panggilan Orang Tua</h2>
                        <p class="mt-1 text-sm text-slate-500">Pilih siswa untuk membuat surat panggilan siap cetak.</p>
                    </div>
                    <button type="button" class="app-button-primary w-full disabled:opacity-50 sm:w-auto" :disabled="!studentId" @click="openPdf('tenant.reports.parent-call-letter', { student_id: studentId })">Download Surat</button>
                </div>
                <label class="mt-5 block text-sm font-semibold text-slate-700">Siswa<select v-model="studentId" class="app-input mt-2 w-full"><option value="">Pilih siswa</option><option v-for="student in students" :key="student.id" :value="student.id">{{ student.name }} · {{ student.nis }}</option></select></label>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
