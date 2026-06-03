<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import ThresholdBadge from '@/Components/App/ThresholdBadge.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatDate } from '@/lib/datetime';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });

const tenantRoute = (name: string, params: Record<string, string> = {}): string => {
    return route(name, { tenant: school.value?.slug, ...params });
};

const props = defineProps<{
    student: {
        id: string;
        nis: string;
        nisn: string | null;
        full_name: string;
        gender: string;
        class_name: string | null;
        guardian_name: string | null;
        guardian_phone: string | null;
        address: string | null;
    };
    filters: { from: string; to: string };
    summary: { attendance_total: number; present_count: number; late_count: number; absent_count: number; violation_count: number; character_count: number };
    attendanceSummary: Array<{ code: string; name: string; total: number }>;
    attendances: Array<{ id: string; date: string | null; code: string | null; status: string | null; note: string | null }>;
    violations: Array<{ id: string; date: string | null; violation_type: string | null; category: string; points: number; note: string | null }>;
    characterPoints: Array<{ id: string; date: string | null; type: string | null; category: string; points: number; note: string | null }>;
    pointSummary: { total: number; character_total: number; threshold: { points: number; label: string } | null };
}>();

const applyFilter = (event: Event) => {
    const form = event.target as HTMLFormElement;
    const data = new FormData(form);

    router.get(
        tenantRoute('tenant.guardian.students.show', { student: props.student.id }),
        {
            from: data.get('from'),
            to: data.get('to'),
        },
        { preserveState: true, preserveScroll: true },
    );
};
</script>

<template>
    <Head :title="student.full_name" />

    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Laporan Anak</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">{{ student.full_name }}</h1>
        </template>

        <div class="space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <Link :href="tenantRoute('tenant.guardian.dashboard')" class="app-button-secondary gap-2"><Icon name="arrow-left" class="h-4 w-4" />Kembali</Link>
            </div>

            <section class="grid gap-4 lg:grid-cols-3">
                <div class="app-card p-5 lg:col-span-2">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="page-kicker">Profil Siswa</p>
                            <h2 class="mt-1 text-2xl font-bold text-slate-900">{{ student.full_name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ student.nis }} · {{ student.class_name ?? '-' }}</p>
                        </div>
                        <span class="app-badge bg-[#99CC33]/15 text-[#5f8f12]">{{ student.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </div>
                    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">NISN</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ student.nisn ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Wali Murid</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ student.guardian_name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">No HP Wali</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ student.guardian_phone ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-400">Alamat</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ student.address ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="grid gap-4">
                    <div class="app-card p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-medium text-slate-500">Poin Pelanggaran</div>
                                <div class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ pointSummary.total }}</div>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600"><Icon name="shield-alert" class="h-6 w-6" /></div>
                        </div>
                        <div class="mt-4">
                            <ThresholdBadge :points="pointSummary.total" :thresholds="[]" />
                        </div>
                        <p v-if="pointSummary.threshold" class="mt-3 text-sm text-slate-500">Batas aktif: {{ pointSummary.threshold.label }}</p>
                    </div>
                    <div class="app-card p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-medium text-slate-500">Poin Karakter</div>
                                <div class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ pointSummary.character_total }}</div>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><Icon name="heart-pulse" class="h-6 w-6" /></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid gap-3 md:grid-cols-3 lg:grid-cols-6">
                <div class="app-card p-4"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Total Absensi</div><div class="mt-2 text-3xl font-black text-slate-900">{{ summary.attendance_total }}</div></div>
                <div class="app-card p-4"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Hadir</div><div class="mt-2 text-3xl font-black text-emerald-600">{{ summary.present_count }}</div></div>
                <div class="app-card p-4"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Terlambat</div><div class="mt-2 text-3xl font-black text-amber-600">{{ summary.late_count }}</div></div>
                <div class="app-card p-4"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Tidak Masuk</div><div class="mt-2 text-3xl font-black text-rose-600">{{ summary.absent_count }}</div></div>
                <div class="app-card p-4"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Pelanggaran</div><div class="mt-2 text-3xl font-black text-red-600">{{ summary.violation_count }}</div></div>
                <div class="app-card p-4"><div class="text-xs font-bold uppercase tracking-wide text-slate-400">Poin Karakter</div><div class="mt-2 text-3xl font-black text-lime-600">{{ summary.character_count }}</div></div>
            </section>

            <section class="app-card p-5">
                <form class="flex flex-col gap-3 sm:flex-row sm:items-end" @submit.prevent="applyFilter">
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="from">Dari tanggal</label>
                        <input id="from" name="from" type="date" class="app-input mt-1" :default-value="filters.from" />
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="to">Sampai tanggal</label>
                        <input id="to" name="to" type="date" class="app-input mt-1" :default-value="filters.to" />
                    </div>
                    <button type="submit" class="app-button-primary gap-2"><Icon name="search" class="h-4 w-4" />Terapkan</button>
                </form>
            </section>

            <section class="app-card p-5">
                <div class="mb-5">
                    <h2 class="font-bold text-slate-900">Ringkasan Absensi</h2>
                    <p class="text-sm text-slate-500">Periode {{ formatDate(filters.from) }} - {{ formatDate(filters.to) }}</p>
                </div>
                <div v-if="attendanceSummary.length" class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div v-for="status in attendanceSummary" :key="status.code" class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm">
                        <div class="text-xs font-bold uppercase text-[#FF5A1E]">{{ status.code }}</div>
                        <div class="mt-1 text-2xl font-bold text-[#1A1D20]">{{ status.total }}</div>
                        <div class="text-xs font-medium text-slate-500">{{ status.name }}</div>
                    </div>
                </div>
                <p v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada absensi pada periode ini.</p>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="app-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-bold text-slate-900">Riwayat Absensi</h2>
                    </div>
                    <div v-if="attendances.length" class="overflow-x-auto">
                        <table class="app-table">
                            <thead><tr><th>Tanggal</th><th>Status</th><th>Catatan</th></tr></thead>
                            <tbody>
                                <tr v-for="attendance in attendances" :key="attendance.id">
                                    <td>{{ attendance.date ? formatDate(attendance.date) : '-' }}</td>
                                    <td><span class="app-badge bg-[#99CC33]/15 text-[#5f8f12]">{{ attendance.code }} · {{ attendance.status }}</span></td>
                                    <td>{{ attendance.note ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="m-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada absensi.</p>
                </div>

                <div class="app-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-bold text-slate-900">Poin Karakter</h2>
                    </div>
                    <div v-if="characterPoints.length" class="overflow-x-auto">
                        <table class="app-table">
                            <thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Poin</th></tr></thead>
                            <tbody>
                                <tr v-for="point in characterPoints" :key="point.id">
                                    <td>{{ point.date ? formatDate(point.date) : '-' }}</td>
                                    <td class="font-semibold text-slate-900">{{ point.type }}</td>
                                    <td>{{ point.category }}</td>
                                    <td>{{ point.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="m-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada poin karakter.</p>
                </div>

                <div class="app-card overflow-hidden lg:col-span-2">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-bold text-slate-900">Pelanggaran Tervalidasi</h2>
                    </div>
                    <div v-if="violations.length" class="overflow-x-auto">
                        <table class="app-table">
                            <thead><tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Poin</th></tr></thead>
                            <tbody>
                                <tr v-for="violation in violations" :key="violation.id">
                                    <td>{{ violation.date ? formatDate(violation.date) : '-' }}</td>
                                    <td class="font-semibold text-slate-900">{{ violation.violation_type }}</td>
                                    <td>{{ violation.category }}</td>
                                    <td>{{ violation.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="m-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada pelanggaran tervalidasi.</p>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
