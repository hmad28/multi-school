<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{ levels: any[]; years: any[] }>();
const page = usePage();
const school = page.props.school as { slug: string };
const levelForm = useForm({ name: '', numeric_value: 1 });
const yearForm = useForm({ name: '', starts_on: '', ends_on: '', is_active: false });
const semesterForm = useForm({ academic_year_id: '', name: '', starts_on: '', ends_on: '', is_active: false });

function storeLevel(): void { levelForm.post(route('tenant.academic.levels.store', { tenant: school.slug }), { onSuccess: () => levelForm.reset() }); }
function storeYear(): void { yearForm.post(route('tenant.academic.years.store', { tenant: school.slug }), { onSuccess: () => yearForm.reset() }); }
function storeSemester(): void { semesterForm.post(route('tenant.academic.semesters.store', { tenant: school.slug }), { onSuccess: () => semesterForm.reset() }); }
</script>

<template>
    <Head title="Akademik" />
    <AuthenticatedLayout>
        <template #header><div><p class="page-kicker">Master Data</p><h1 class="text-2xl font-bold text-ink dark:text-white">Akademik</h1><p class="text-sm text-slate-500">Kelola tingkat, tahun ajaran, dan semester.</p></div></template>

        <div class="grid gap-6 xl:grid-cols-3">
            <section class="app-card p-5">
                <h2 class="text-lg font-bold">Tingkat</h2>
                <form class="mt-4 space-y-3" @submit.prevent="storeLevel">
                    <input v-model="levelForm.name" class="app-input w-full" placeholder="Kelas 1" />
                    <input v-model="levelForm.numeric_value" type="number" min="1" max="12" class="app-input w-full" />
                    <button class="app-button-primary w-full" type="submit">Tambah Tingkat</button>
                </form>
                <div class="mt-5 space-y-2"><div v-for="level in levels" :key="level.id" class="app-card-muted flex justify-between px-4 py-3 text-sm"><span>{{ level.name }}</span><span>{{ level.numeric_value }}</span></div></div>
            </section>

            <section class="app-card p-5">
                <h2 class="text-lg font-bold">Tahun Ajaran</h2>
                <form class="mt-4 space-y-3" @submit.prevent="storeYear">
                    <input v-model="yearForm.name" class="app-input w-full" placeholder="2026/2027" />
                    <input v-model="yearForm.starts_on" type="date" class="app-input w-full" />
                    <input v-model="yearForm.ends_on" type="date" class="app-input w-full" />
                    <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="yearForm.is_active" type="checkbox" class="rounded border-slate-300 text-brand-700" /> Aktif</label>
                    <button class="app-button-primary w-full" type="submit">Tambah Tahun</button>
                </form>
                <div class="mt-5 space-y-2"><div v-for="year in years" :key="year.id" class="app-card-muted px-4 py-3 text-sm"><div class="flex justify-between font-semibold"><span>{{ year.name }}</span><span>{{ year.is_active ? 'Aktif' : '-' }}</span></div><p class="text-slate-500">{{ year.starts_on ?? '-' }} — {{ year.ends_on ?? '-' }}</p></div></div>
            </section>

            <section class="app-card p-5">
                <h2 class="text-lg font-bold">Semester</h2>
                <form class="mt-4 space-y-3" @submit.prevent="storeSemester">
                    <select v-model="semesterForm.academic_year_id" class="app-input w-full"><option value="">Pilih tahun ajaran</option><option v-for="year in years" :key="year.id" :value="year.id">{{ year.name }}</option></select>
                    <input v-model="semesterForm.name" class="app-input w-full" placeholder="Ganjil" />
                    <input v-model="semesterForm.starts_on" type="date" class="app-input w-full" />
                    <input v-model="semesterForm.ends_on" type="date" class="app-input w-full" />
                    <label class="flex items-center gap-2 text-sm font-semibold"><input v-model="semesterForm.is_active" type="checkbox" class="rounded border-slate-300 text-brand-700" /> Aktif</label>
                    <button class="app-button-primary w-full" type="submit">Tambah Semester</button>
                </form>
                <div class="mt-5 space-y-2"><template v-for="year in years" :key="year.id"><div v-for="semester in year.semesters" :key="semester.id" class="app-card-muted px-4 py-3 text-sm"><div class="flex justify-between font-semibold"><span>{{ semester.name }}</span><span>{{ semester.is_active ? 'Aktif' : '-' }}</span></div><p class="text-slate-500">{{ year.name }}</p></div></template></div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
