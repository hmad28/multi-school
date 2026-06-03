<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Student } from '@/types/domain';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { onMounted, ref } from 'vue';

const props = defineProps<{ student: Student; token: string; hadToken: boolean }>();
const page = usePage();
const payload = props.token;
const qrDataUrl = ref('');
const confirmOpen = ref(false);

const tenant = (page.props.school as any)?.slug ?? '';
const tRoute = (name: string, params?: Record<string, any>) => route(name, { ...params, tenant });

const printQr = () => window.print();
const regenerateQr = () => router.post(tRoute('tenant.attendance.students.qr.token.regenerate', { student: props.student.id }));

onMounted(async () => {
    qrDataUrl.value = await QRCode.toDataURL(payload, {
        errorCorrectionLevel: 'M',
        margin: 2,
        width: 320,
    });
});
</script>

<template>
    <Head title="Token QR Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">QR siswa</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Token QR {{ student.name }}</h1>
        </template>

        <div class="app-card max-w-3xl space-y-5 p-6">
            <div>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-black text-slate-900">{{ student.name }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ student.school_class?.name }} · NIS {{ student.nis }}</p>
                    </div>
                    <span class="app-badge" :class="hadToken ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'">{{ hadToken ? 'QR sudah ada' : 'QR baru dibuat' }}</span>
                </div>
            </div>
            <div class="grid gap-5 lg:grid-cols-[360px_1fr]">
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-5 text-center shadow-sm">
                    <p class="text-sm font-semibold text-slate-700">QR siap scan</p>
                    <img v-if="qrDataUrl" :src="qrDataUrl" :alt="`QR ${student.name}`" class="mx-auto mt-4 h-72 w-72 rounded-2xl bg-white p-3" />
                    <p v-else class="mt-4 text-sm text-slate-500">Membuat QR...</p>
                    <button type="button" class="app-button-primary mt-4 w-full" @click="printQr">Cetak QR</button>
                </div>

                <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-700">Token manual siswa</p>
                    <textarea class="app-input mt-3 min-h-32 w-full font-mono text-xs" readonly :value="payload"></textarea>
                    <p class="mt-3 text-xs leading-5 text-slate-500">Pakai QR di kiri untuk scan kamera. Token manual ini cadangan kalau kamera tidak bisa membaca QR.</p>
                </div>
            </div>

            <div class="rounded-2xl bg-emerald-50 p-4 text-sm leading-6 text-emerald-900">
                QR ini stabil. Membuka halaman ini tidak membuat QR baru, jadi kartu siswa yang sudah dicetak tetap berlaku.
            </div>

            <div class="rounded-2xl bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                Buat ulang QR hanya jika kartu hilang atau token bocor. Setelah dibuat ulang, QR lama tidak bisa dipakai scan lagi.
            </div>

            <div class="flex flex-wrap gap-2">
                <Link :href="tRoute('tenant.attendance.students.index')" class="app-button-secondary inline-flex">Kembali ke Absensi Siswa</Link>
                <Link :href="tRoute('tenant.attendance.students.qr.index')" class="app-button-primary inline-flex">Buka QR Scanner</Link>
                <button type="button" class="app-button-danger" @click="confirmOpen = true">Buat ulang QR</button>
            </div>
        </div>

        <div v-if="confirmOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4">
            <div class="w-full max-w-lg rounded-[2rem] bg-white p-6 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600">!</div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">Buat ulang QR?</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">QR yang sudah dicetak di kartu siswa tidak akan berlaku lagi. Lakukan ini hanya jika kartu hilang atau token bocor.</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap justify-end gap-2">
                    <button type="button" class="app-button-secondary" @click="confirmOpen = false">Batal</button>
                    <button type="button" class="app-button-danger" @click="regenerateQr">Ya, buat ulang</button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
