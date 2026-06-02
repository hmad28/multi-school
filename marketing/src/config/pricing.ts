/** Model harga: flat per sekolah · bulanan/tahunan · 3 tingkat */

export const yearlyDiscountPercent = 17;

function yearlyTotal(monthlyIdr: number): number {
	return Math.round(monthlyIdr * 12 * (1 - yearlyDiscountPercent / 100));
}

function yearlyPerMonth(monthlyIdr: number): number {
	return Math.round(yearlyTotal(monthlyIdr) / 12);
}

export const pricingTiers = [
	{
		id: 'standar',
		name: 'Standar',
		tagline: 'Satu harga per sekolah — fitur inti',
		desc: 'Untuk operasional harian: absensi, kedisiplinan, laporan. Tanpa modul AI.',
		monthlyIdr: 249_000,
		highlight: false,
		cta: 'Berlangganan',
		features: [
			'Absensi siswa & guru (input & QR)',
			'Pelanggaran, poin, & poin karakter',
			'Kalender libur & hari efektif',
			'Laporan PDF & Excel',
			'Import data murid via Excel',
			'Peran: admin, guru, BK, kepala sekolah',
			'Notifikasi in-app dasar',
		],
		notIncluded: ['Portal wali murid', 'WhatsApp ke wali', 'Modul AI', 'Fitur custom'],
	},
	{
		id: 'plus-ai',
		name: 'Plus AI',
		tagline: 'Standar + kecerdasan buatan',
		desc: 'Semua fitur Standar, plus modul AI untuk membantu admin, BK, dan kepala sekolah.',
		monthlyIdr: 399_000,
		highlight: true,
		cta: 'Berlangganan',
		features: [
			'Semua fitur paket Standar',
			'Portal wali murid',
			'Handoff / notifikasi WhatsApp ke wali',
			'Ringkasan & insight AI (kehadiran, tren pelanggaran)',
			'Saran tindakan BK berbasis pola data',
			'Backup data cloud',
		],
	},
	{
		id: 'plus-ai-custom',
		name: 'Plus AI & Custom',
		tagline: 'Untuk kebutuhan khusus sekolah / yayasan',
		desc: 'Plus AI ditambah pengembangan fitur custom, integrasi, atau alur sesuai permintaan sekolah.',
		monthlyIdr: null as number | null,
		highlight: false,
		cta: 'Hubungi kami',
		contactOnly: true,
		features: [
			'Semua fitur Plus AI',
			'Fitur custom (modul, laporan, integrasi)',
			'Penyesuaian alur kerja sekolah',
			'Onboarding & training dedicated',
			'Prioritas support & SLA',
			'Cocok untuk yayasan multi unit (roadmap)',
		],
	},
] as const;

export type PricingTierId = (typeof pricingTiers)[number]['id'];

export function formatRupiah(amount: number): string {
	return new Intl.NumberFormat('id-ID', {
		style: 'currency',
		currency: 'IDR',
		maximumFractionDigits: 0,
	}).format(amount);
}

export function getYearlyPricing(monthlyIdr: number) {
	return {
		total: yearlyTotal(monthlyIdr),
		perMonth: yearlyPerMonth(monthlyIdr),
	};
}

/** Trial: fitur Standar + preview AI — lihat site.trialDays */
export const trialFeatures = [
	`Akses fitur Standar selama percobaan`,
	'Preview modul AI (terbatas)',
	'Tanpa kartu kredit',
	'Satu trial per sekolah',
	'Upgrade ke Plus AI atau Custom kapan saja',
];
