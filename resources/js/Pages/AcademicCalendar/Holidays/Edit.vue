<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Holiday = {
    id: string;
    date: string;
    name: string;
    description: string | null;
    status: string;
};

const props = defineProps<{ holiday: Holiday }>();
const page = usePage();
const school = computed(() => page.props.school as { slug: string });
const form = useForm({
    date: props.holiday.date,
    name: props.holiday.name,
    description: props.holiday.description ?? '',
    status: props.holiday.status,
});

function submit(): void {
    form.put(route('tenant.academic-calendar.holidays.update', { tenant: school.value.slug, holiday: props.holiday.id }));
}
</script>

<template>
    <Head title="Edit Hari Libur" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="page-kicker">Kalender Akademik</p>
                <h1 class="text-2xl font-bold text-ink dark:text-white">Edit hari libur</h1>
                <p class="text-sm text-slate-500">Perubahan hanya berlaku untuk tenant aktif.</p>
            </div>
        </template>

        <form class="app-card max-w-2xl space-y-4 p-5" @submit.prevent="submit">
            <div>
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tanggal</label>
                <input v-model="form.date" type="date" class="app-input mt-1 w-full" />
                <p v-if="form.errors.date" class="mt-1 text-sm text-rose-600">{{ form.errors.date }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nama</label>
                <input v-model="form.name" class="app-input mt-1 w-full" placeholder="Libur nasional" />
                <p v-if="form.errors.name" class="mt-1 text-sm text-rose-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Deskripsi</label>
                <textarea v-model="form.description" class="app-input mt-1 min-h-28 w-full" placeholder="Catatan opsional"></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</p>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Status</label>
                <select v-model="form.status" class="app-input mt-1 w-full">
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-sm text-rose-600">{{ form.errors.status }}</p>
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="app-button-primary" :disabled="form.processing">Simpan perubahan</button>
                <Link :href="route('tenant.academic-calendar.holidays.index', { tenant: school.slug })" class="app-button-secondary">Batal</Link>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
