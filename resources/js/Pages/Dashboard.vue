<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type School = {
    id: string;
    name: string;
    slug: string;
    status: string;
};

type Metrics = {
    students: number;
    teachers: number;
    classes: number;
    academicYear: string | null;
    semester: string | null;
};

const props = defineProps<{
    school?: School;
    tenantMode?: boolean;
    metrics?: Metrics;
}>();

const isTenantDashboard = computed(() => Boolean(props.tenantMode && props.school));
const tenantBase = computed(() => (props.school ? `/t/${props.school.slug}` : '/dashboard'));
const todayLabel = computed(() => new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).format(new Date()));
const metrics = computed<Metrics>(() => props.metrics ?? { students: 0, teachers: 0, classes: 0, academicYear: null, semester: null });

const kpiCards = computed(() => [
    { label: 'Siswa aktif', value: metrics.value.students, icon: 'users', caption: 'Dari master data P2' },
    { label: 'Guru aktif', value: metrics.value.teachers, icon: 'user-square', caption: 'Siap untuk wali kelas dan absensi guru' },
    { label: 'Kelas aktif', value: metrics.value.classes, icon: 'graduation-cap', caption: 'Rombel aktif semester ini' },
    { label: 'Absensi hari ini', value: 'P3', icon: 'calendar-check', caption: 'Slot siap diisi modul absensi' },
]);

const shortcuts = computed(() => [
    { label: 'Data Siswa', href: `${tenantBase.value}/students`, icon: 'users', ready: true },
    { label: 'Data Guru', href: `${tenantBase.value}/teachers`, icon: 'user-square', ready: true },
    { label: 'Kelas', href: `${tenantBase.value}/classes`, icon: 'graduation-cap', ready: true },
    { label: 'Akademik', href: `${tenantBase.value}/academic`, icon: 'calendar-check', ready: true },
    { label: 'Absensi Siswa', href: '#', icon: 'calendar-check', ready: false },
    { label: 'Pelanggaran', href: '#', icon: 'shield-alert', ready: false },
]);

const statusCards = computed(() => [
    { title: 'Tahun ajaran', value: metrics.value.academicYear ?? 'Belum diatur', tone: 'bg-brand-100 text-brand-700' },
    { title: 'Semester aktif', value: metrics.value.semester ?? 'Belum diatur', tone: 'bg-sky-100 text-sky-700' },
    { title: 'Status tenant', value: props.school?.status ?? 'central', tone: 'bg-slate-100 text-slate-700' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Beranda sekolah</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Dashboard</h1>
        </template>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#2563EB] via-[#3B82F6] to-[#1A1D20] p-6 text-white shadow-xl shadow-blue-200 dark:shadow-slate-950/50">
                <div class="flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-blue-100">
                            <template v-if="isTenantDashboard && school">{{ school.name }}</template>
                            <template v-else>Platform Sekolah</template>
                        </p>
                        <h2 class="mt-3 max-w-3xl text-3xl font-bold tracking-tight">
                            <template v-if="isTenantDashboard && school">Beranda operasional {{ school.name }}</template>
                            <template v-else>Pilih konteks platform atau sekolah untuk mulai bekerja.</template>
                        </h2>
                        <p class="mt-2 max-w-2xl text-sm text-blue-50">
                            Dashboard ini mengikuti ritme sistem pilot: ringkasan harian, shortcut modul, dan slot operasional yang siap diisi absensi, pelanggaran, laporan, dan notifikasi.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-medium text-white ring-1 ring-white/20">
                        {{ todayLabel }}
                    </div>
                </div>
            </section>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="card in kpiCards" :key="card.label" class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">{{ card.label }}</div>
                            <div class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ card.value }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-100 text-brand-700 dark:bg-brand-700/20 dark:text-brand-100">
                            <Icon :name="card.icon" class="h-6 w-6" />
                        </div>
                    </div>
                    <p class="mt-4 text-sm font-medium text-slate-500">{{ card.caption }}</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
                <section class="app-card p-5">
                    <div class="mb-5">
                        <h2 class="font-bold text-slate-900 dark:text-slate-100">Akses cepat</h2>
                        <p class="text-sm text-slate-500">Modul P2 sudah aktif; modul P3–P5 tetap disiapkan sebagai slot operasional.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <component
                            :is="shortcut.ready ? Link : 'div'"
                            v-for="shortcut in shortcuts"
                            :key="shortcut.label"
                            :href="shortcut.ready ? shortcut.href : undefined"
                            class="flex items-center justify-between rounded-2xl border border-line bg-white px-4 py-4 text-sm font-semibold text-ink shadow-sm transition dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            :class="shortcut.ready ? 'hover:border-brand-600/50 hover:bg-brand-100/50' : 'opacity-60'"
                        >
                            <span class="flex items-center gap-3"><Icon :name="shortcut.icon" class="h-5 w-5 text-brand-700" />{{ shortcut.label }}</span>
                            <span class="text-xs text-slate-400">{{ shortcut.ready ? 'Buka' : 'Soon' }}</span>
                        </component>
                    </div>
                </section>

                <section class="app-card p-5">
                    <div class="mb-5">
                        <h2 class="font-bold text-slate-900 dark:text-slate-100">Status akademik</h2>
                        <p class="text-sm text-slate-500">Dipakai onboarding dan modul absensi berikutnya.</p>
                    </div>
                    <div class="space-y-3">
                        <div v-for="item in statusCards" :key="item.title" class="app-card-muted flex items-center justify-between px-4 py-3">
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ item.title }}</span>
                            <span class="app-badge" :class="item.tone">{{ item.value }}</span>
                        </div>
                    </div>
                </section>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <article class="app-card p-5">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100">Absensi terbaru</h3>
                    <p class="mt-2 text-sm text-slate-500">Slot P3. Setelah absensi di-port, panel ini menampilkan rekap hadir/izin/sakit/alpa hari ini.</p>
                </article>
                <article class="app-card p-5">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100">Perlu perhatian</h3>
                    <p class="mt-2 text-sm text-slate-500">Slot P4. Pelanggaran pending, threshold karakter, dan notifikasi penting tampil di sini.</p>
                </article>
                <article class="app-card p-5">
                    <h3 class="font-bold text-slate-900 dark:text-slate-100">Laporan cepat</h3>
                    <p class="mt-2 text-sm text-slate-500">Slot P5. Tombol PDF, Excel, backup, dan laporan wali murid akan masuk panel ini.</p>
                </article>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
