<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    plan?: string | null;
    period?: string | null;
    trialDays: number;
}>();

const form = useForm({
    school_name: '',
    admin_name: '',
    admin_email: '',
    password: '',
    password_confirmation: '',
    plan: props.plan ?? 'trial',
    period: props.period ?? 'monthly',
});

const submit = () => {
    form.post(route('school.register.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Sekolah" />

        <div class="mb-6">
            <h1 class="text-lg font-bold text-slate-900">Daftarkan sekolah Anda</h1>
            <p class="mt-1 text-sm text-slate-600">Mulai trial {{ trialDays }} hari gratis. Setelah daftar, verifikasi email lalu lengkapi onboarding.</p>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="school_name" value="Nama sekolah" />
                <TextInput id="school_name" type="text" class="mt-1 block w-full" v-model="form.school_name" required autofocus />
                <InputError class="mt-2" :message="form.errors.school_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="admin_name" value="Nama admin (PIC)" />
                <TextInput id="admin_name" type="text" class="mt-1 block w-full" v-model="form.admin_name" required autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.admin_name" />
            </div>

            <div class="mt-4">
                <InputLabel for="admin_email" value="Email admin" />
                <TextInput id="admin_email" type="email" class="mt-1 block w-full" v-model="form.admin_email" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.admin_email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />
                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Konfirmasi password" />
                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="form.password_confirmation" required autocomplete="new-password" />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link :href="route('login')" class="rounded-md text-sm text-slate-600 underline hover:text-slate-900">Sudah punya akun?</Link>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Daftar sekolah</PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
