<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const school = page.props.school as { slug: string };
const form = useForm({ nip: '', full_name: '', position: 'Guru', phone: '', status: 'active', can_input_teacher_attendance: false });

function submit(): void {
    form.post(route('tenant.teachers.store', { tenant: school.slug }));
}
</script>

<template>
    <Head title="Tambah Guru" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="page-kicker">Master Data</p>
                <h1 class="text-2xl font-bold text-ink dark:text-white">Tambah Guru</h1>
                <p class="text-sm text-slate-500">Lengkapi data guru.</p>
            </div>
        </template>

        <form class="app-card max-w-3xl space-y-5 p-6" @submit.prevent="submit">
            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2"><span class="text-sm font-semibold">Nama lengkap</span><input v-model="form.full_name" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.full_name }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">NIP</span><input v-model="form.nip" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.nip }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Jabatan</span><input v-model="form.position" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.position }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Telepon</span><input v-model="form.phone" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.phone }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Status</span><select v-model="form.status" class="app-input w-full"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><span class="text-sm text-rose-600">{{ form.errors.status }}</span></label>
                <label class="flex items-center gap-3 pt-8"><input v-model="form.can_input_teacher_attendance" type="checkbox" class="rounded border-slate-300 text-brand-700" /><span class="text-sm font-semibold">Bisa input absensi guru</span></label>
            </div>
            <div class="flex gap-3"><button class="app-button-primary" type="submit" :disabled="form.processing">Simpan</button><Link :href="route('tenant.teachers.index', { tenant: school.slug })" class="app-button-secondary">Batal</Link></div>
        </form>
    </AuthenticatedLayout>
</template>
