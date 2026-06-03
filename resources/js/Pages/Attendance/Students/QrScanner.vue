<script setup lang="ts">
import Icon from '@/Components/App/Icon.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { SchoolClass } from '@/types/domain';
import { formatDate } from '@/lib/datetime';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import jsQR from 'jsqr';
import { onBeforeUnmount, ref } from 'vue';

type FeedbackKind = 'success' | 'error' | 'duplicate';

const props = defineProps<{ classes: SchoolClass[]; filters: { class_id: string; date: string; scan_type: 'arrival' | 'departure' } }>();
const page = usePage();
const filter = useForm({ class_id: props.filters.class_id ?? '', date: props.filters.date, scan_type: props.filters.scan_type ?? 'arrival' });
const scanForm = useForm({ student_token: '', date: filter.date, scan_type: filter.scan_type, force_update: false });
const video = ref<HTMLVideoElement | null>(null);
const scanning = ref(false);
const cameraError = ref('');
const cameraStatus = ref('');
const feedbackOpen = ref(false);
const feedbackKind = ref<FeedbackKind>('success');
const feedbackTitle = ref('');
const feedbackMessage = ref('');
let stream: MediaStream | null = null;
let timer: number | null = null;
let canvas: HTMLCanvasElement | null = null;

const tenant = (page.props.school as any)?.slug ?? '';
const tenantParams = () => ({ tenant });
const tRoute = (name: string, params?: Record<string, any>) => route(name, { ...params, ...tenantParams() });
const attendanceParams = () => ({ class_id: filter.class_id, date: filter.date });

const load = () => router.get(tRoute('tenant.attendance.students.qr.index'), filter.data(), { preserveState: true });
const firstError = (errors: Record<string, string>) => Object.values(errors)[0] ?? 'Scan gagal. QR tidak terdaftar atau data tidak valid.';
const openFeedback = (kind: FeedbackKind, title: string, message: string) => {
    feedbackKind.value = kind;
    feedbackTitle.value = title;
    feedbackMessage.value = message;
    feedbackOpen.value = true;
};
const closeFeedback = () => (feedbackOpen.value = false);
const goToAttendance = () => router.get(tRoute('tenant.attendance.students.index'), attendanceParams());
const submitScan = (forceUpdate = false) => {
    scanForm.date = filter.date;
    scanForm.scan_type = filter.scan_type;
    scanForm.force_update = forceUpdate;
    scanForm.post(tRoute('tenant.attendance.students.qr.scan'), {
        preserveScroll: true,
        onSuccess: () => {
            const flash = (page.props.flash ?? {}) as any;
            scanForm.student_token = '';
            openFeedback('success', 'Scan berhasil', flash.success ?? 'Data absensi sudah masuk.');
        },
        onError: (errors) => {
            const message = firstError(errors);
            if (errors.duplicate_scan) {
                openFeedback('duplicate', 'QR sudah discan', message);
                return;
            }
            openFeedback('error', 'Scan gagal', message);
        },
        onFinish: () => (scanForm.force_update = false),
    });
};
const confirmUpdateScan = () => {
    closeFeedback();
    submitScan(true);
};
const dismissFeedback = () => {
    const kind = feedbackKind.value;
    closeFeedback();
    if (kind === 'success') goToAttendance();
};

const detectQr = async () => {
    if (!video.value || video.value.readyState < 2) return '';

    if ('BarcodeDetector' in window) {
        const detector = new (window as any).BarcodeDetector({ formats: ['qr_code'] });
        const codes = await detector.detect(video.value);
        return codes[0]?.rawValue ?? '';
    }

    canvas ??= document.createElement('canvas');
    canvas.width = video.value.videoWidth;
    canvas.height = video.value.videoHeight;
    const context = canvas.getContext('2d');
    if (!context || !canvas.width || !canvas.height) return '';

    context.drawImage(video.value, 0, 0, canvas.width, canvas.height);
    const image = context.getImageData(0, 0, canvas.width, canvas.height);
    return jsQR(image.data, image.width, image.height)?.data ?? '';
};

const startCamera = async () => {
    cameraError.value = '';
    cameraStatus.value = 'Browser akan meminta izin kamera. Pilih Allow/Izinkan untuk mulai scan QR.';

    if (!navigator.mediaDevices?.getUserMedia) {
        cameraStatus.value = '';
        cameraError.value = 'Browser tidak mendukung akses kamera atau alamat aplikasi belum HTTPS/secure context. Gunakan input manual.';
        return;
    }

    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        if (video.value) video.value.srcObject = stream;
        scanning.value = true;
        cameraStatus.value = 'Kamera aktif. Arahkan kamera ke QR siswa.';

        timer = window.setInterval(async () => {
            const rawValue = await detectQr();
            if (rawValue) {
                scanForm.student_token = rawValue;
                stopCamera();
                submitScan();
            }
        }, 700);
    } catch (error) {
        cameraStatus.value = '';
        cameraError.value = 'Kamera tidak bisa dibuka. Pilih Allow/Izinkan saat browser meminta izin. Jika tidak ada prompt, browser kemungkinan memblokir kamera karena alamat belum HTTPS/secure context.';
    }
};

const stopCamera = () => {
    scanning.value = false;
    cameraStatus.value = '';
    if (timer) window.clearInterval(timer);
    timer = null;
    stream?.getTracks().forEach((track) => track.stop());
    stream = null;
};

onBeforeUnmount(stopCamera);
</script>

<template>
    <Head title="QR Absensi Siswa" />
    <AuthenticatedLayout>
        <template #header>
            <p class="page-kicker">Scan QR</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">QR Absensi Siswa</h1>
        </template>

        <div class="space-y-5">
            <div v-if="feedbackOpen" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/45 p-4 backdrop-blur-sm sm:items-center">
                <div class="w-full max-w-md overflow-hidden rounded-[2rem] bg-white shadow-2xl ring-1 ring-slate-900/10">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl" :class="feedbackKind === 'success' ? 'bg-emerald-100 text-emerald-700' : feedbackKind === 'duplicate' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700'">
                                <Icon :name="feedbackKind === 'success' ? 'check-circle' : feedbackKind === 'duplicate' ? 'clock' : 'alert-circle'" class="h-6 w-6" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold uppercase tracking-[0.2em]" :class="feedbackKind === 'success' ? 'text-emerald-600' : feedbackKind === 'duplicate' ? 'text-amber-600' : 'text-rose-600'">{{ feedbackKind === 'success' ? 'Berhasil' : feedbackKind === 'duplicate' ? 'Duplikat scan' : 'Perlu dicek' }}</p>
                                <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ feedbackTitle }}</h2>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ feedbackMessage }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col-reverse gap-2 border-t border-slate-100 bg-slate-50 p-4 sm:flex-row sm:justify-end">
                        <button v-if="feedbackKind === 'duplicate'" type="button" class="app-button-secondary" @click="dismissFeedback">Batal</button>
                        <button v-if="feedbackKind === 'duplicate'" type="button" class="app-button-primary" @click="confirmUpdateScan">Update waktu terbaru</button>
                        <button v-else type="button" class="app-button-primary" @click="dismissFeedback">{{ feedbackKind === 'success' ? 'Lihat Absensi' : 'Mengerti' }}</button>
                    </div>
                </div>
            </div>

            <section class="app-card p-5">
                <div class="grid gap-3 lg:grid-cols-[1fr_180px_180px_auto_auto] lg:items-end">
                    <label class="text-sm font-semibold text-slate-700">Filter kelas<select v-model="filter.class_id" class="app-input mt-2 w-full"><option value="">Semua kelas</option><option v-for="item in classes" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
                    <label class="text-sm font-semibold text-slate-700">Tanggal<input v-model="filter.date" type="date" class="app-input mt-2 w-full" /></label>
                    <label class="text-sm font-semibold text-slate-700">Tipe scan<select v-model="filter.scan_type" class="app-input mt-2 w-full"><option value="arrival">Datang</option><option value="departure">Pulang</option></select></label>
                    <button type="button" class="app-button-secondary" @click="load">Terapkan</button>
                    <button type="button" class="app-button-primary" @click="router.get(tRoute('tenant.attendance.students.index'), attendanceParams())">Lihat Absensi</button>
                </div>
            </section>

            <section class="grid gap-5 lg:grid-cols-[1fr_420px]">
                <div class="app-card overflow-hidden">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="text-lg font-bold text-slate-900">Kamera scanner</h2>
                    </div>
                    <div class="p-5">
                        <div class="aspect-video overflow-hidden rounded-3xl bg-slate-950">
                            <video ref="video" autoplay playsinline muted class="h-full w-full object-cover"></video>
                        </div>
                        <p v-if="cameraStatus" class="mt-3 rounded-2xl bg-sky-50 p-3 text-sm text-sky-800">{{ cameraStatus }}</p>
                        <p v-if="cameraError" class="mt-3 rounded-2xl bg-amber-50 p-3 text-sm text-amber-800">{{ cameraError }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <button type="button" class="app-button-primary inline-flex items-center gap-2" :disabled="scanning" @click="startCamera"><Icon name="search" class="h-4 w-4" /> Minta Izin & Mulai Kamera</button>
                            <button type="button" class="app-button-secondary" :disabled="!scanning" @click="stopCamera">Stop</button>
                        </div>
                    </div>
                </div>

                <form class="app-card p-5" @submit.prevent="submitScan()">
                    <h2 class="text-lg font-bold text-slate-900">Input manual</h2>
                    <label class="mt-5 block text-sm font-semibold text-slate-700">Token QR siswa<textarea v-model="scanForm.student_token" class="app-input mt-2 min-h-32 w-full" placeholder="Tempel token QR siswa" /></label>
                    <button type="submit" class="app-button-primary mt-5 inline-flex w-full items-center justify-center gap-2" :disabled="scanForm.processing || !scanForm.student_token"><Icon name="check-circle" class="h-4 w-4" /> Catat Scan</button>
                    <p class="mt-4 text-xs leading-5 text-slate-500">Tanggal aktif: {{ formatDate(filter.date) }}. Tipe: {{ filter.scan_type === 'arrival' ? 'Datang' : 'Pulang' }}.</p>
                </form>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
