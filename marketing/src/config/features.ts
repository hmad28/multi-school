/** Copy fitur untuk marketing — boleh lengkap; hindari detail arsitektur di sini */

export const featureModules = [
	{
		key: 'attendance' as const,
		title: 'Absensi siswa & guru',
		summary: 'Catat kehadiran harian per kelas, rekap otomatis, tanpa kartu RFID.',
		bullets: [
			'Input absensi harian per kelas oleh guru',
			'Absensi via scan QR dari HP guru',
			'Rekap kehadiran bulanan untuk TU',
			'Absensi guru terpisah dari siswa',
		],
	},
	{
		key: 'violation' as const,
		title: 'Pelanggaran & poin',
		summary: 'Kedisiplinan tercatat rapi, bisa divalidasi BK, dengan ambang tindakan.',
		bullets: [
			'Catat pelanggaran per murid',
			'Validasi oleh BK sebelum masuk rekap resmi',
			'Akumulasi poin per semester',
			'Ambang tindakan otomatis (peringatan, panggilan ortu, dll.)',
		],
	},
	{
		key: 'character' as const,
		title: 'Poin karakter',
		summary: 'Perilaku positif ikut terdokumentasi — bukan hanya hukuman.',
		bullets: [
			'Catat prestasi dan perilaku baik murid',
			'Seimbang dengan catatan pelanggaran',
			'Membantu BK melihat profil murid secara utuh',
		],
	},
	{
		key: 'report' as const,
		title: 'Laporan murid',
		summary: 'Ekspor dan ringkasan untuk admin, wali kelas, dan kepala sekolah.',
		bullets: [
			'Laporan PDF per murid atau per kelas',
			'Ekspor Excel untuk arsip TU',
			'Rekap kehadiran dan poin dalam satu tampilan',
		],
	},
	{
		key: 'portal' as const,
		title: 'Portal wali murid',
		summary: 'Orang tua cek data anak sendiri — tanpa menunggu rapat.',
		bullets: [
			'Akun khusus wali murid (hanya data anak sendiri)',
			'Lihat kehadiran dan ringkasan kedisiplinan',
			'Mengurangi pertanyaan berulang ke TU',
		],
	},
	{
		key: 'calendar' as const,
		title: 'Kalender libur & hari efektif',
		summary: 'Libur nasional dan sekolah dihitung benar di rekap absensi.',
		bullets: [
			'Atur hari libur sekolah dan nasional',
			'Hari libur tidak dihitung alpha',
			'Absensi QR ditolak di tanggal non-efektif',
		],
	},
	{
		key: 'notify' as const,
		title: 'Notifikasi & WhatsApp',
		summary: 'Sekolah dan wali murid lebih cepat dapat informasi penting.',
		bullets: [
			'Notifikasi in-app untuk guru dan admin',
			'Siapkan pesan ke wali murid (handoff / gateway di paket Pro)',
			'Contoh: murid alpha, kasus BK menunggu validasi',
		],
	},
	{
		key: 'import' as const,
		title: 'Import data murid',
		summary: 'Onboarding cepat — tidak perlu input satu per satu.',
		bullets: [
			'Template Excel siap pakai',
			'Upload siswa dan kelas dalam satu proses',
			'Validasi data sebelum masuk sistem',
		],
	},
] as const;

export const roles = [
	{
		title: 'Admin & TU',
		desc: 'Kelola data murid, user, kalender libur, dan cetak laporan administrasi.',
	},
	{
		title: 'Guru kelas',
		desc: 'Absensi pagi, input pelanggaran, dan pantau murid di kelasnya.',
	},
	{
		title: 'BK & kepala sekolah',
		desc: 'Validasi kasus, pantau tren kedisiplinan, ambil keputusan berbasis data.',
	},
	{
		title: 'Wali murid',
		desc: 'Portal khusus untuk melihat perkembangan anak — kapan saja.',
	},
] as const;
