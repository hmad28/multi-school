<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { formatDate } from '@/lib/datetime';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const school = computed(() => page.props.school as { slug: string });

const tenantRoute = (name: string, params: Record<string, string> = {}): string => {
    return route(name, { tenant: school.value?.slug, ...params });
};

defineProps<{
    children: Array<{ id: string; nis: string; full_name: string; class_name: string | null }>;
    summary: {
        child_count: number;
        attendance_month: Array<{ code: string; name: string; total: number }>;
        total_points: number;
        validated_violation_count: number;
        character_points: number;
    };
    latestAttendances: Array<{ id: string; student_name: string | null; date: string | null; code: string | null; status: string | null; note: string | null }>;
    latestCharacterPoints: Array<{ id: string; student_name: string | null; type: string | null; date: string | null; points: number }>;
    latestViolations: Array<{ id: string; student_name: string | null; violation_type: string | null; date: string | null; points: number }>;
}>();
</script>

<template>
    <Head title="Laporan Anak" />

    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Wali Murid</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Laporan Anak</h1>
        </template>

        <div class="space-y-6">
            <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-[#FF5A1E] via-[#ff7a3d] to-[#1A1D20] p-6 text-white shadow-xl shadow-orange-200">
                <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-orange-100">Portal Wali Murid</p>
                        <h2 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight">Pantau ringkasan kehadiran dan kedisiplinan anak.</h2>
                        <p class="mt-2 text-sm text-orange-50">Data hanya menampilkan siswa yang terhubung dengan akun wali murid ini.</p>
                    </div>
                    <div class="rounded-2xl bg-white/15 px-4 py-3 text-sm font-medium text-white ring-1 ring-white/20">{{ summary.child_count }} anak terhubung</div>
                </div>
            </section>

            <div class="grid gap-4 md:grid-cols-4">
                <div class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Anak Terhubung</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ summary.child_count }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#FF5A1E]/10 text-[#FF5A1E]"><Icon name="users" class="h-6 w-6" /></div>
                    </div>
                </div>
                <div class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Poin Karakter</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ summary.character_points }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><Icon name="heart-pulse" class="h-6 w-6" /></div>
                    </div>
                </div>
                <div class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Poin Pelanggaran</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ summary.total_points }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-600"><Icon name="shield-alert" class="h-6 w-6" /></div>
                    </div>
                </div>
                <div class="app-card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <div class="text-sm font-medium text-slate-500">Pelanggaran</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ summary.validated_violation_count }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#99CC33]/15 text-[#5f8f12]"><Icon name="gavel" class="h-6 w-6" /></div>
                    </div>
                </div>
            </div>

            <section class="app-card p-5">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-bold text-slate-900">Absensi Bulan Ini</h2>
                        <p class="text-sm text-slate-500">Rekap semua anak terhubung.</p>
                    </div>
                </div>
                <div v-if="summary.attendance_month.length" class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                    <div v-for="status in summary.attendance_month" :key="status.code" class="rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm">
                        <div class="text-xs font-bold uppercase text-[#FF5A1E]">{{ status.code }}</div>
                        <div class="mt-1 text-2xl font-bold text-[#1A1D20]">{{ status.total }}</div>
                        <div class="text-xs font-medium text-slate-500">{{ status.name }}</div>
                    </div>
                </div>
                <p v-else class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada data absensi bulan ini.</p>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="app-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-bold text-slate-900">Absensi Terbaru</h2>
                        <p class="text-sm text-slate-500">Data scan dan input absensi terakhir.</p>
                    </div>
                    <div v-if="latestAttendances.length" class="overflow-x-auto">
                        <table class="app-table">
                            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Status</th><th>Catatan</th></tr></thead>
                            <tbody>
                                <tr v-for="attendance in latestAttendances" :key="attendance.id">
                                    <td>{{ attendance.date ? formatDate(attendance.date) : '-' }}</td>
                                    <td class="font-semibold text-slate-900">{{ attendance.student_name }}</td>
                                    <td>{{ attendance.code }} · {{ attendance.status }}</td>
                                    <td>{{ attendance.note ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="m-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada absensi.</p>
                </div>

                <div class="app-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-bold text-slate-900">Poin Karakter Terbaru</h2>
                        <p class="text-sm text-slate-500">Riwayat poin kebaikan anak.</p>
                    </div>
                    <div v-if="latestCharacterPoints.length" class="overflow-x-auto">
                        <table class="app-table">
                            <thead><tr><th>Tanggal</th><th>Siswa</th><th>Jenis</th><th>Poin</th></tr></thead>
                            <tbody>
                                <tr v-for="point in latestCharacterPoints" :key="point.id">
                                    <td>{{ point.date ? formatDate(point.date) : '-' }}</td>
                                    <td class="font-semibold text-slate-900">{{ point.student_name }}</td>
                                    <td>{{ point.type }}</td>
                                    <td>{{ point.points }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="m-5 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada poin karakter.</p>
                </div>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <div class="app-card p-5">
                    <div class="mb-4">
                        <h2 class="font-bold text-slate-900">Daftar Anak</h2>
                        <p class="text-sm text-slate-500">Klik untuk melihat laporan detail.</p>
                    </div>
                    <div class="space-y-3">
                        <Link v-for="child in children" :key="child.id" :href="tenantRoute('tenant.guardian.students.show', { student: child.id })" class="mobile-list-card flex items-center justify-between gap-3">
                            <span>
                                <span class="block font-semibold text-slate-900">{{ child.full_name }}</span>
                                <span class="text-sm text-slate-500">{{ child.nis }} · {{ child.class_name ?? '-' }}</span>
                            </span>
                            <Icon name="file-text" class="h-5 w-5 text-[#FF5A1E]" />
                        </Link>
                        <p v-if="!children.length" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">Belum ada siswa yang terhubung dengan akun ini.</p>
                    </div>
                </div>

                <div class="app-card overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="font-bold text-slate-900">Pelanggaran Terbaru</h2>
                        <p class="text-sm text-slate-500">Hanya pelanggaran tervalidasi.</p>
                    </div>
                    <div v-if="latestViolations.length" class="overflow-x-auto">
                        <table class="app-table">
                            <thead>
                                <tr><th>Tanggal</th><th>Siswa</th><th>Jenis</th><th>Poin</th></tr>
                            </thead>
                            <tbody>
                                <tr v-for="violation in latestViolations" :key="violation.id">
                                    <td>{{ violation.date ? formatDate(violation.date) : '-' }}</td>
                                    <td class="font-semibold text-slate-900">{{ violation.student_name }}</td>
                                    <td>{{ violation.violation_type }}</td>
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
