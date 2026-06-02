<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{ classes: { id: string; name: string }[] }>();
const page = usePage();
const school = page.props.school as { slug: string };
const form = useForm({ name: '', nis: '', nisn: '', class_id: '', gender: '', guardian_name: '', guardian_phone: '', address: '', status: 'active' });

function submit(): void {
    form.post(route('tenant.students.store', { tenant: school.slug }));
}
</script>

<template>
    <Head title="Tambah Siswa" />
    <AuthenticatedLayout>
        <template #header><div><p class="page-kicker">Master Data</p><h1 class="text-2xl font-bold text-ink dark:text-white">Tambah Siswa</h1><p class="text-sm text-slate-500">Lengkapi biodata siswa.</p></div></template>
        <form class="app-card max-w-4xl space-y-5 p-6" @submit.prevent="submit">
            <div class="grid gap-5 md:grid-cols-2">
                <label class="space-y-2"><span class="text-sm font-semibold">Nama</span><input v-model="form.name" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.name }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">NIS</span><input v-model="form.nis" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.nis }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">NISN</span><input v-model="form.nisn" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.nisn }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Kelas</span><select v-model="form.class_id" class="app-input w-full"><option value="">Tanpa kelas</option><option v-for="item in classes" :key="item.id" :value="item.id">{{ item.name }}</option></select><span class="text-sm text-rose-600">{{ form.errors.class_id }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Gender</span><select v-model="form.gender" class="app-input w-full"><option value="">-</option><option value="male">Laki-laki</option><option value="female">Perempuan</option></select><span class="text-sm text-rose-600">{{ form.errors.gender }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Status</span><select v-model="form.status" class="app-input w-full"><option value="active">Aktif</option><option value="inactive">Nonaktif</option></select><span class="text-sm text-rose-600">{{ form.errors.status }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Nama wali</span><input v-model="form.guardian_name" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.guardian_name }}</span></label>
                <label class="space-y-2"><span class="text-sm font-semibold">Telepon wali</span><input v-model="form.guardian_phone" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.guardian_phone }}</span></label>
            </div>
            <label class="space-y-2 block"><span class="text-sm font-semibold">Alamat</span><textarea v-model="form.address" rows="4" class="app-input w-full" /><span class="text-sm text-rose-600">{{ form.errors.address }}</span></label>
            <div class="flex gap-3"><button class="app-button-primary" type="submit" :disabled="form.processing">Simpan</button><Link :href="route('tenant.students.index', { tenant: school.slug })" class="app-button-secondary">Batal</Link></div>
        </form>
    </AuthenticatedLayout>
</template>
