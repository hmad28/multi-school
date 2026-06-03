/** Laravel app base URL. Override via PUBLIC_APP_URL at build time. */
const appUrl = import.meta.env.PUBLIC_APP_URL ?? 'http://127.0.0.1:8888';

export const site = {
	name: 'Platform Sekolah',
	tagline: 'Absensi, kedisiplinan, dan karakter murid — dalam satu platform.',
	description:
		'Platform Sekolah: absensi QR, pelanggaran & poin BK, poin karakter, portal wali murid, laporan Excel, kalender libur, dan notifikasi WhatsApp. Untuk sekolah swasta Indonesia.',
	url: 'https://platformsekolah.id',
	locale: 'id_ID',
	contactEmail: 'halo@platformsekolah.id',
	appUrl,
	/** School self-registration lives in the Laravel app (Inertia form + email verify). */
	registerUrl: `${appUrl}/daftar`,
	/** Trial marketing — batas hari diset di produk/backend nanti */
	trialDays: 14,
	trialLabel: 'Trial 14 hari — gratis, fitur Standar + preview AI',
	existingClientHint:
		'Sudah menjadi pelanggan? Gunakan link akses yang dikirim ke email admin sekolah Anda.',
} as const;
