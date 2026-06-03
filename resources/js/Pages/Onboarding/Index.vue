<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Step = { key: string; title: string; description: string; done: boolean; route: string };

const props = defineProps<{
    school: { id: string; name: string; slug: string; email: string; phone: string | null; address: string | null; principal_name: string | null };
    steps: Step[];
    completed: boolean;
    trialEndsAt: string | null;
}>();

const page = usePage();
const tenant = computed(() => page.props.school as { slug: string });
const tRoute = (name: string, params: Record<string, string> = {}) => route(name, { tenant: tenant.value.slug, ...params });

const doneCount = computed(() => props.steps.filter((s) => s.done).length);
const progress = computed(() => Math.round((doneCount.value / props.steps.length) * 100));

const profileForm = useForm({
    phone: props.school.phone ?? '',
    address: props.school.address ?? '',
    principal_name: props.school.principal_name ?? '',
});
const inviteForm = useForm({ name: '', email: '', password: '', password_confirmation: '' });

const saveProfile = () => profileForm.patch(tRoute('tenant.onboarding.profile'), { preserveScroll: true });
const sendInvite = () => inviteForm.post(tRoute('tenant.onboarding.invite'), { preserveScroll: true, onSuccess: () => inviteForm.reset() });
const finish = () => router.post(tRoute('tenant.onboarding.finish'));
const skip = () => router.visit(tRoute('tenant.dashboard'));

const linkFor = (step: Step) => (step.route === 'tenant.onboarding.show' ? null : tRoute(step.route));
</script>

<template>
    <Head title="Onboarding Sekolah" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Langkah awal</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Onboarding {{ school.name }}</h1>
        </template>


        <div class="max-w-4xl space-y-6">
            <section class="app-card p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ doneCount }} dari {{ steps.length }} langkah selesai</h2>
                        <p class="mt-1 text-sm text-slate-500">Lengkapi data awal sekolah. Kamu bisa lewati dan kembali kapan saja.</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" class="app-button-secondary" @click="skip">Lewati dulu</button>
                        <button type="button" class="app-button-primary" @click="finish">{{ completed ? 'Onboarding selesai' : 'Tandai selesai' }}</button>
                    </div>
                </div>
                <div class="mt-5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                    <div class="h-full rounded-full bg-[#FF5A1E] transition-all" :style="{ width: progress + '%' }"></div>
                </div>
            </section>

            <section class="app-card overflow-hidden">
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    <div v-for="step in steps" :key="step.key" class="flex items-start gap-4 p-5">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold" :class="step.done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">{{ step.done ? '✓' : '•' }}</span>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="font-semibold text-slate-900 dark:text-white">{{ step.title }}</p>
                                <Link v-if="linkFor(step)" :href="linkFor(step)!" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Buka →</Link>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ step.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="app-card p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Profil sekolah</h2>
                <p class="mt-1 text-sm text-slate-500">Dipakai untuk kop laporan PDF.</p>
                <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="saveProfile">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Telepon
                        <input v-model="profileForm.phone" class="app-input mt-2 w-full" placeholder="0812..." />
                        <span class="text-sm text-rose-600">{{ profileForm.errors.phone }}</span>
                    </label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nama kepala sekolah
                        <input v-model="profileForm.principal_name" class="app-input mt-2 w-full" placeholder="Nama lengkap" />
                        <span class="text-sm text-rose-600">{{ profileForm.errors.principal_name }}</span>
                    </label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200 sm:col-span-2">Alamat
                        <textarea v-model="profileForm.address" class="app-input mt-2 w-full" rows="2" placeholder="Alamat sekolah"></textarea>
                        <span class="text-sm text-rose-600">{{ profileForm.errors.address }}</span>
                    </label>
                    <div class="sm:col-span-2"><button type="submit" class="app-button-primary" :disabled="profileForm.processing">Simpan profil</button></div>
                </form>
            </section>

            <section class="app-card p-6">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Undang pengguna</h2>
                <p class="mt-1 text-sm text-slate-500">Tambahkan operator/guru lain sebagai admin sekolah. Opsional.</p>
                <form class="mt-4 grid gap-4 sm:grid-cols-2" @submit.prevent="sendInvite">
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Nama
                        <input v-model="inviteForm.name" class="app-input mt-2 w-full" />
                        <span class="text-sm text-rose-600">{{ inviteForm.errors.name }}</span>
                    </label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Email
                        <input v-model="inviteForm.email" type="email" class="app-input mt-2 w-full" />
                        <span class="text-sm text-rose-600">{{ inviteForm.errors.email }}</span>
                    </label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Password
                        <input v-model="inviteForm.password" type="password" class="app-input mt-2 w-full" autocomplete="new-password" />
                        <span class="text-sm text-rose-600">{{ inviteForm.errors.password }}</span>
                    </label>
                    <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Konfirmasi password
                        <input v-model="inviteForm.password_confirmation" type="password" class="app-input mt-2 w-full" autocomplete="new-password" />
                    </label>
                    <div class="sm:col-span-2"><button type="submit" class="app-button-secondary" :disabled="inviteForm.processing">Undang pengguna</button></div>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
