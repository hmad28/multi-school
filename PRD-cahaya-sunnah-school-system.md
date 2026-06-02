# Product Requirements Document
# Platform Sekolah

**Versi:** 2.2  
**Tanggal:** 30 Mei 2026  
**Status:** Draft — Internal  
**Penulis:** Founder  

**Produk SaaS:** Platform Sekolah — domain rencana `platformsekolah.id` (belum dibeli).  
**Proyek klien (Fase 1):** dashboard untuk sekolah **Cahaya Sunnah** — repo `project-cahaya-sunnah` (bukan nama brand SaaS).

**Sumber:** PRD v2.0 terpadu, implementasi pilot, diskusi arsitektur multi-tenant & konfirmasi founder (Mei 2026).

**Dokumentasi teknis detail:** [docs/README.md](./docs/README.md) (scope, ERD, struktur, development/backend/frontend/deployment plans).

---

## Daftar Isi

1. [Ringkasan Eksekutif](#1-ringkasan-eksekutif)
2. [Visi, Nama, dan Posisi Produk](#2-visi-nama-dan-posisi-produk)
3. [Tujuan per Fase](#3-tujuan-per-fase)
4. [Target Pengguna](#4-target-pengguna)
5. [Kompetitor dan Diferensiasi](#5-kompetitor-dan-diferensiasi)
6. [Roadmap Produk](#6-roadmap-produk)
7. [Fase 1 — Pilot Single-Tenant](#7-fase-1--pilot-single-tenant)
8. [Fase 2 — SaaS Multi-Tenant](#8-fase-2--saas-multi-tenant)
9. [Fase 3 — Otomasi dan AI](#9-fase-3--otomasi-dan-ai)
10. [Arsitektur Teknis](#10-arsitektur-teknis)
11. [Arsitektur Data Multi-Tenant](#11-arsitektur-data-multi-tenant)
12. [Struktur Repositori](#12-struktur-repositori)
13. [Model Bisnis dan Pricing](#13-model-bisnis-dan-pricing)
14. [Permission Matrix](#14-permission-matrix)
15. [Non-Functional Requirements](#15-non-functional-requirements)
16. [Out of Scope](#16-out-of-scope)
17. [Risiko dan Mitigasi](#17-risiko-dan-mitigasi)
18. [Metrik Keberhasilan](#18-metrik-keberhasilan)
19. [Keputusan Produk](#19-keputusan-produk)
20. [Glosarium](#20-glosarium)

---

## 1. Ringkasan Eksekutif

**Platform Sekolah** adalah produk SaaS manajemen operasional sekolah (umum — swasta Islam, umum, madrasah): absensi manual & QR, pelanggaran + poin, poin karakter, laporan murid, notifikasi, WhatsApp (handoff/gateway), dan admin yang bisa dipakai guru non-teknis.

**Pemisahan penting:**

| | Platform Sekolah (produk kamu) | Proyek Cahaya Sunnah (klien) |
|---|------------------------------|------------------------------|
| Apa | SaaS multi-sekolah | Dashboard untuk **satu sekolah** (nama sekolah klien) |
| Repo | `multi-school` | `project-cahaya-sunnah` |
| Brand di UI publik | Platform Sekolah | Nama sekolah klien di kop surat & settings |

Produk berkembang dalam **tiga fase**:

| Fase | Mode | Status |
|------|------|--------|
| **1 — Pilot klien** | Satu sekolah (Cahaya Sunnah), on-premise Docker + LAN | **Selesai di repo**; deploy PC sekolah menunggu |
| **2 — SaaS** | Banyak sekolah di cloud, subdomain per tenant | **Rencana** — codebase `multi-school` |
| **3 — AI** | Otomasi & insight | Roadmap tahun 2+ |

**Prinsip arsitektur SaaS:** Shared DB + `school_id`, Global Scope + Spatie **teams**, plus **dashboard platform** (super-admin) untuk kelola sekolah yang mendaftar.

**Prinsip porting:** Modul operasional dari pilot **di-port** ke SaaS; yang baru = tenant, registrasi, billing, super-admin, backup cloud.

---

## 2. Visi, Nama, dan Posisi Produk

### 2.1 Masalah

Sekolah masih mencatat absensi dan pelanggaran manual (buku/Excel/WhatsApp satu per satu). Akibatnya: rekapitulasi error, BK tidak melihat akumulasi poin real-time, orang tua terlambat tahu, admin menghabiskan waktu laporan, tidak ada audit trail.

### 2.2 Visi

> **Platform Sekolah** — sistem kelola absensi, kedisiplinan, dan karakter murid untuk sekolah di Indonesia: sederhana, cepat, tanpa pelatihan teknis panjang, agar guru fokus mendidik.

### 2.3 Penamaan dan brand

| Konteks | Nama |
|---------|------|
| **Brand produk SaaS** | **Platform Sekolah** |
| **Domain (rencana)** | `platformsekolah.id` — **belum dibeli**; dev boleh pakai `.test` / tunnel dulu |
| **Landing & marketing** | Platform Sekolah |
| **Footer app (opsional)** | “Didukang oleh Platform Sekolah” |
| **Nama di kop surat PDF** | **Nama masing-masing sekolah tenant** (bukan Platform Sekolah) |
| **Proyek klien / pilot** | Sekolah **Cahaya Sunnah** — repo `project-cahaya-sunnah` |
| Legacy internal pilot | SiswaPintar (tidak dipakai di UI) |

### 2.4 Domain dan subdomain (terkonfirmasi)

```
platformsekolah.id                 → landing, daftar sekolah baru
admin.platformsekolah.id           → dashboard super-admin (founder/tim internal)
{slug}.platformsekolah.id          → aplikasi per sekolah (tenant)
```

- **Resolusi tenant:** subdomain `{slug}` → middleware baca `Host` → `school_id`.
- **DNS & SSL:** Cloudflare (wildcard `*.platformsekolah.id` + tunnel ke origin/VPS).
- **Contoh tenant klien:** `cahaya-sunnah.platformsekolah.id` (slug final saat onboarding).

Cadangan sebelum domain dibeli: `platformsekolah.com` redirect, atau tunnel + hostname sementara.

### 2.5 Posisi di pasar

Tidak bersaing head-to-head dengan SkoolaCloud (enterprise, per-siswa). Platform Sekolah: **kedisiplinan + karakter + absensi**, onboarding mandiri, **harga flat per sekolah**, browser saja (tanpa RFID wajib).

---

## 3. Tujuan per Fase

### 3.1 Fase 1 — Pilot (proyek klien Cahaya Sunnah)

- Menggantikan administrasi manual di **sekolah klien Cahaya Sunnah** (satu instansi).
- Membuktikan adoption guru/admin tanpa developer di lapangan.
- Menjadi **referensi fitur & UX** untuk port ke Platform Sekolah (SaaS).

### 3.2 Fase 2 — SaaS

- Banyak sekolah di satu deploy cloud dengan **isolasi data ketat**.
- Onboarding mandiri &lt; 1 hari kerja.
- MRR menutup biaya operasional server.

### 3.3 Fase 3 — AI

- Otomasi draft dokumen, deteksi pola, assistant BK.
- Diferensiasi premium di atas data historis multi-sekolah.

---

## 4. Target Pengguna

### 4.1 Segmen B2B

**Utama:** Sekolah Islam swasta (SD/SMP/SMA/pesantren modern), 100–1.500 siswa, Indonesia, sudah punya PC/LAN.

**Sekunder:** Madrasah swasta, sekolah umum yang butuh modul kedisiplinan.

**Tidak disasar sekarang:** Sekolah negeri (Dapodik), yayasan enterprise multi-sekolah skala besar (tier terpisah nanti).

### 4.2 Persona dan kondisi berhasil

| Persona | Tanggung jawab utama | Kondisi berhasil |
|---------|----------------------|------------------|
| **Admin Sekolah / TU** | Master data, import Excel, user, settings, backup | Siapkan data awal tanpa developer |
| **Guru Kelas** | Absensi harian, QR scan, pelanggaran/karakter kelas | Absensi 30 siswa &lt; 5 menit di 1366×768 |
| **Guru BK / Walas** | Validasi pelanggaran, koreksi absensi, laporan | Identifikasi siswa mendekati threshold tanpa spreadsheet |
| **Kepala Sekolah** | Dashboard & laporan (read-heavy) | Ringkasan harian tanpa edit operasional |
| **Wali Murid** | Lihat data anak terhubung saja | Pantau anak dari HP; **sudah ada di pilot** |
| **Super Admin** *(Fase 2, internal)* | Tenant, support, suspend | Tidak ada kebocoran data antar sekolah |

---

## 5. Kompetitor dan Diferensiasi

### 5.1 SkoolaCloud

Lengkap (PPDB, keuangan, RFID, LMS), ~Rp 15.000/siswa/bulan, onboarding berat. **Kelemahan untuk segmen kita:** kompleks, mahal untuk sekolah kecil, bukan fokus Islam/karakter.

### 5.2 Rapor Digital Kemendikbud

Gratis, fokus akademik/rapor resmi, bukan kedisiplinan harian.

### 5.3 Spreadsheet manual

Familiar, tanpa audit trail, tidak kolaboratif.

### 5.4 Diferensiasi CSSS

1. Vertikal sekolah Islam (BK, pelanggaran, karakter, pembinaan).  
2. Onboarding mandiri 1 hari (Fase 2).  
3. Otomasi administrasi (threshold, notifikasi; AI di Fase 3).  
4. **Harga flat per sekolah**, bukan per siswa.  
5. Ringan: browser + QR opsional, tanpa hardware khusus.

---

## 6. Roadmap Produk

```
┌─────────────────────────────────────────────────────────────────┐
│ FASE 1 — PILOT KLIEN (sekarang)                                 │
│ Repo: project-cahaya-sunnah • Sekolah Cahaya Sunnah • Docker LAN│
│ MVP M0–M5 + ekstensi M6+: QR, karakter, wali murid, notifikasi, │
│   WA handoff, kalender libur, export Excel                      │
│ Tests: 107 passed, 634 assertions (baseline 30 Mei 2026)      │
│ Sisa: deploy PC sekolah + training                              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ FASE 2 — Platform Sekolah SaaS (bulan 4–12 setelah pilot stabil)│
│ Brand: Platform Sekolah • platformsekolah.id • repo multi-school│
│ BARU: schools, subdomain, super-admin dashboard, trial, billing │
│ PORT: modul operasional dari pilot klien + school_id + teams    │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│ FASE 3 — AI (tahun 2+, revenue-positive)                        │
│ Deteksi pola, draft surat, assistant BK, prediksi risiko        │
│ Peningkatan modul karakter (bukan CRUD dasar — sudah di Fase 1) │
└─────────────────────────────────────────────────────────────────┘
```

---

## 7. Fase 1 — Pilot Single-Tenant (Klien Cahaya Sunnah)

> Proyek ini **bukan** brand Platform Sekolah — ini implementasi khusus untuk **sekolah klien** yang meminta sistem absensi, pelanggaran, dll. Saat SaaS hidup, sekolah ini bisa menjadi **tenant pertama** dengan slug mis. `cahaya-sunnah`.

### 7.1 Status implementasi

| Item | Nilai |
|------|--------|
| Repositori | `C:\Projects\project-cahaya-sunnah` |
| Klien / instansi | Sekolah **Cahaya Sunnah** (nama di `school_settings`, kop surat) |
| Tenancy | Tidak ada `school_id` — satu DB = satu sekolah |
| Identitas sekolah | `school_settings` (satu baris aktif) |
| Automated tests | 107 tests, 634 assertions |
| Deploy ke PC sekolah | Menunggu |

### 7.2 Arsitektur deploy pilot

```
PC Sekolah (Windows 10/11)
└── Docker Desktop + WSL2
    ├── app       → PHP 8.4-FPM + Laravel 13
    ├── nginx     → port 8000 (LAN)
    ├── mysql     → MySQL 8.4
    └── redis     → standby
```

- **Akses staf:** `http://<IP-PC>:8000` (Chrome/Edge).  
- **Internet:** tidak wajib untuk operasional harian.  
- **Opsional:** Cloudflare Tunnel setelah pilot LAN stabil.

**Env produksi kunci:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://<PC-IP>:8000
APP_TIMEZONE=Asia/Jakarta
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

### 7.3 Tech stack pilot

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13, PHP 8.4-FPM |
| Frontend | Inertia.js 3, Vue 3, TypeScript, Tailwind CSS |
| Database | MySQL 8.4 |
| AuthZ | Laravel Breeze + Spatie Permission |
| Import/Export | maatwebsite/excel, DomPDF |
| QR | qrcode (generate), jsQR (scanner) |
| Container | Docker Compose, Nginx |

### 7.4 Role dan autentikasi

**5 role** (Spatie):

| Slug | Deskripsi |
|------|-----------|
| `admin-sekolah` | Full administrasi sekolah |
| `guru-kelas` | Absensi, QR, pelanggaran/karakter kelas |
| `bk-walas` | Validasi pelanggaran, koreksi absensi, laporan luas |
| `kepala-sekolah` | Monitoring & laporan |
| `wali-murid` | Portal read-only anak terhubung |

**Fitur auth:** login email/password, logout, admin kelola user & reset password, activity log (login, CRUD, import, export, backup).

### 7.5 Modul operasional (semua di pilot)

#### 7.5.1 Data master

- **Siswa:** NIS (unik), NISN opsional, nama, kelas, JK, wali, HP wali, alamat, foto, status aktif/nonaktif. CRUD, filter, import Excel (validasi per baris, all-or-nothing), template download.
- **Guru:** nama, jabatan, NIP opsional, HP, `can_input_teacher_attendance`, status.
- **Kelas:** tingkat (`academic_levels`) + rombel, wali kelas, sort order.
- **Jenis pelanggaran:** kategori ringan/sedang/berat, poin default.
- **Pengaturan sekolah:** nama, NPSN, alamat, logo, kop surat, kepala sekolah, semester aktif, jam absensi/terlambat.

#### 7.5.2 Absensi siswa

- Input per kelas per tanggal; status H/T/I/S/A (Alpha).
- Default Hadir; submit lock per kelas/tanggal; koreksi admin/BK.
- Rekap bulanan; filter sudah scan / belum scan / semua.
- **QR:** token terenkripsi per siswa, scanner guru, duplikat scan → update waktu; regenerasi QR manual; terlambat otomatis (jam dari settings) + pelanggaran otomatis jika dikonfigurasi.
- **Kalender libur:** hari libur aktif → tidak synthesize Alpha; scan QR ditolak.

#### 7.5.3 Absensi guru

- Input harian semua guru aktif; H/I/S/A; submit lock; koreksi **hanya admin**.

#### 7.5.4 Pelanggaran

- Input: siswa, jenis, tanggal, catatan, bukti foto (opsional).
- Status pending → validasi/tolak (alasan wajib jika tolak).
- **Points snapshot** saat create; akumulasi per semester (hanya validated).
- Threshold: 25 / 50 / 75 / 100+.

#### 7.5.5 Poin karakter

- Tipe poin positif (CRUD admin); input per siswa; tampil di laporan murid.

#### 7.5.6 Laporan murid

- **Admin:** semua siswa. **Guru kelas:** kelas sendiri. **BK:** sesuai policy. **Wali murid:** hanya `guardian_student` pivot.
- Detail: absensi, pelanggaran, karakter, ringkasan poin.

#### 7.5.7 Laporan cetak / export

| Laporan | Output |
|---------|--------|
| Rekap absensi siswa bulanan | PDF / Excel |
| Rekap absensi guru | PDF / Excel |
| Rekap pelanggaran | PDF / Excel |
| Rekap poin karakter | PDF / Excel |
| Surat panggilan orang tua | PDF |

Kop surat dari `school_settings`; target generate &lt; 10 detik (~40 siswa).

#### 7.5.8 Dashboard

- Kehadiran siswa/guru hari ini, pelanggaran per kategori, top 5 poin pelanggaran semester.
- Chart ringan (`DashboardChartService`) — opsional, bukan fokus UX.

#### 7.5.9 Notifikasi & WhatsApp

- **In-app:** `school_notifications` ke role/user terkait.
- **WhatsApp:** `whatsapp_messages` — pesan disiapkan + handoff URL (wa.me); riwayat tersimpan; **bukan** WABA fully automated di pilot.

#### 7.5.10 Backup

- Admin trigger → file `.sql` streaming download → hapus dari server.
- Permission `backup.download`; tercatat activity log.

### 7.6 Milestone development

| Milestone | Isi | Status |
|-----------|-----|--------|
| M0 | Bootstrap, Docker, auth | ✅ |
| M1 | Master data, roles, settings | ✅ |
| M2 | Absensi siswa & guru | ✅ |
| M3 | Pelanggaran & poin | ✅ |
| M4 | PDF, dashboard, backup | ✅ |
| M5 | Hardening, docs handover | ✅ |
| M6+ | QR, guardian, karakter, notifikasi, WA, kalender, Excel | ✅ |
| Deploy | PC sekolah + training | ⏳ |

### 7.7 Success criteria pilot

Semua kriteria di bawah **verified di development**; validasi ulang di LAN sekolah saat deploy:

- Admin: user/role, import 500+ siswa dengan error per baris.
- Guru: absensi 30 siswa &lt; 5 menit.
- Duplikat submit absensi kelas/tanggal diblokir.
- BK validasi → poin semester update.
- PDF &lt; 10 detik; dashboard &lt; 2 detik (target LAN).
- Backup download & restore diuji developer.
- Chrome/Edge 1366×768.

---

## 8. Fase 2 — Platform Sekolah (SaaS Multi-Tenant)

> **Produk:** Platform Sekolah @ `platformsekolah.id`  
> **Target mulai:** 4–6 bulan setelah pilot klien stabil + ada sekolah kedua yang berminat.  
> **Repositori:** `C:\Projects\multi-school` — **greenfield** (pilot tetap jalan terpisah di PC klien).

### 8.1 Perubahan arsitektur

| Aspek | Fase 1 (pilot) | Fase 2 (SaaS) |
|-------|----------------|---------------|
| Infrastruktur | PC sekolah | VPS/cloud |
| Sekolah | 1 | Banyak tenant |
| Database | Flat, tanpa `school_id` | Shared DB + `school_id` |
| Akses | LAN / tunnel | HTTPS publik |
| Deploy | Manual per PC | Registrasi + provisioning |
| Backup | Manual admin | Terjadwal + manual |
| Storage | `storage/app` lokal | S3/R2 per tenant |
| Queue | sync | Horizon + Redis |
| Role scope | Global Spatie | **Spatie teams** (`school_id`) |

### 8.2 Scope Fase 2 — dibagi dua

#### A. Platform (fitur baru)

1. **Registrasi & onboarding**
   - Form: nama sekolah, NPSN, email PIC admin, password.
   - **Verifikasi email wajib** per akun.
   - Auto-provision subdomain: `{slug}.platformsekolah.id` (slug unik dari nama sekolah).
   - Wizard: profil → tahun ajaran → kelas → import siswa → undang guru.
2. **Tenant resolution (terkonfirmasi):** subdomain wildcard + Cloudflare (DNS/tunnel); lihat [§2.4](#24-domain-dan-subdomain-terkonfirmasi).
3. **Trial 30 hari** — akses setara Professional; reminder H-7/H-3/H-1; pasca trial → read-only lalu suspend.
4. **Billing** — Midtrans/Xendit; bulanan/tahunan (-20%); invoice email atas nama **Platform Sekolah**; grace 7 hari. Pembayar: **sekolah atau yayasan** (bukan reseller — out of scope).
5. **Dashboard super-admin (v1)** — lihat [§8.5](#85-dashboard-platform-super-admin-v1).
6. **Backup cloud** — harian, retensi 30 hari, notifikasi gagal.
7. **Founding school program** — 5–10 sekolah pertama, harga terkunci (Cahaya Sunnah bisa masuk program ini).

**Out of scope Fase 2 awal:** reseller channel, impersonate ke tenant (nanti + audit), MRR dashboard, agregat jumlah siswa lintas tenant.

#### B. Port dari pilot (tambah `school_id` + policy + tests isolasi)

Semua modul [§7.5](#75-modul-operasional-semua-di-pilot).

**Peningkatan di Fase 2 (bukan modul baru):**

| Area | Pilot | SaaS |
|------|-------|------|
| Portal wali murid | Ada, dasar | Mobile-first, push/email |
| WhatsApp | Handoff wa.me | Gateway Fonnte/WABA opsional per paket |
| Karakter | CRUD poin | Tetap; tier tidak “menambah modul” |

### 8.3 Infrastruktur hosting

**Rencana awal (founder):** mini PC sebagai server + **Cloudflare Tunnel** (tanpa VPS). VPS cloud opsional saat scale.

```
Mini PC (Docker) — atau VPS nanti
├── Laravel + PHP 8.4-FPM
├── Nginx + SSL (Cloudflare / Let's Encrypt)
├── MySQL managed
├── Redis (session, cache, queue)
├── Laravel Horizon
├── S3/R2 (schools/{school_id}/...)
├── Cloudflare CDN/WAF
└── Sentry + Uptime Robot
```

**CI/CD:** GitHub Actions — test (wajib include tenant isolation tests) → deploy staging/production.

### 8.4 Tech stack tambahan

| Komponen | Teknologi |
|----------|-----------|
| Queue | Horizon + Redis |
| Storage | R2 / S3 |
| Email | Resend / Mailgun |
| Payment | Midtrans / Xendit |
| WhatsApp | Fonnte / WABA |
| DNS / tunnel | Cloudflare (wildcard + Tunnel) |

### 8.5 Dashboard platform (super-admin v1)

**URL:** `admin.platformsekolah.id` (atau route `/admin` di domain utama, diproteksi role `super-admin`).

**Pengguna:** founder / tim internal Platform Sekolah — **bukan** admin sekolah tenant.

**Fitur v1 (terkonfirmasi cukup):**

| Fitur | Deskripsi |
|-------|-----------|
| Daftar sekolah | Nama, slug/subdomain, NPSN (jika ada), status, tanggal daftar, `trial_ends_at` |
| Filter / search | Cari nama sekolah atau slug |
| Suspend | Tenant tidak bisa login/input; data tetap ada |
| Reaktivasi | Kembalikan status active/trial |
| Reset password admin sekolah | Reset akun admin utama tenant (dengan konfirmasi) |

**Tidak di v1:** grafik MRR, total siswa agregat, impersonate session, edit data murid lintas tenant.

**Keamanan:** role terpisah dari `admin-sekolah`; akses lintas tenant hanya di halaman platform; nanti impersonate wajib audit log.

### 8.6 Deploy on-site (opsional, terkonfirmasi)

Satu codebase; mode deploy:

| Mode | Keterangan |
|------|------------|
| **Cloud SaaS** | Default — subdomain Platform Sekolah |
| **On-site / LAN** | Docker di lokasi sekolah (seperti pilot klien); single-tenant tanpa `school_id`; penawaran terpisah (setup + maintenance) |

---

## 9. Fase 3 — Otomasi dan AI

> **Prasyarat:** Fase 2 revenue-positive; data historis dari 20+ sekolah (min. ~6 bulan).

### 9.1 Otomasi

- Deteksi pola (alpha hari tertentu, lonjakan poin mingguan).
- Draft surat panggilan otomatis saat threshold (BK review & cetak).
- Notifikasi prediktif & digest mingguan ke BK/kepsek.

### 9.2 Peningkatan karakter

Modul karakter **sudah ada di Fase 1**. Fase 3 menambah:

- Rapor saldo bersih (kebaikan − pelanggaran).
- Kategori islami lanjutan (ibadah, hafalan, prestasi).
- Template rapor karakter otomatis.

### 9.3 AI assistant BK

- Chat bahasa Indonesia atas data sekolah (Claude API).
- Rangkum pelanggaran formal untuk surat.
- Rekomendasi tindakan berbasis history.

### 9.4 Prediksi risiko

- Rule-based awal → ML opsional.
- Label: Perlu perhatian / Normal / Prestasi.

### 9.5 Pricing

Masuk paket **Premium** ([§13](#13-model-bisnis-dan-pricing)).

---

## 10. Arsitektur Teknis

### 10.1 Pola backend (Fase 1–3)

```
HTTP Request
    ↓
Middleware (auth, permission, SetCurrentSchool [Fase 2])
    ↓
Controller (thin)
    ↓
Form Request (validation + authorization)
    ↓
Action (satu workflow, method handle())
    ↓
Service (logic reusable)
    ↓
Eloquent Model + BelongsToSchool scope [Fase 2]
    ↓
MySQL
```

**Aturan wajib:**

- Setiap write route → Form Request.
- Setiap aksi penting → activity log.
- Multi-row write → DB transaction.
- Laporan → hindari N+1; range query + aggregate.
- Tidak ada interpolasi input ke raw SQL/shell.
- Upload → validasi MIME sebenarnya.

### 10.2 Actions (contoh di pilot)

```
app/Actions/
├── Attendance/     Submit*, Correct*
├── Violations/     Create, Validate, Reject
├── CharacterPoints/ CreateStudentCharacterPoint
├── Backup/         CreateDatabaseBackup
└── ...
```

### 10.3 Services (contoh di pilot)

```
app/Services/
├── ActivityLogService.php
├── AttendanceCalendarService.php
├── QrAttendanceService.php
├── ViolationSummaryService.php
├── CharacterPointService.php
├── SchoolNotificationService.php
├── WhatsAppMessageService.php
├── PdfReportService.php
├── DashboardChartService.php
└── StudentReportData.php
```

### 10.4 Frontend

```
resources/js/
├── Pages/        → 1:1 dengan route Laravel
├── Components/
├── Layouts/
├── lib/          → format, permissions, routes
└── types/
```

**UX:** staff-first, bahasa Indonesia, target 1366×768, konfirmasi aksi berbahaya, backend selalu enforce permission meski UI menyembunyikan tombol.

### 10.5 Konfigurasi app

`config/cahaya.php` — limit upload, disk backup, timezone default.

---

## 11. Arsitektur Data Multi-Tenant

### 11.1 Strategi

**Single database + `school_id`** untuk &lt; ~500 sekolah.

Scale-out nanti: DB-per-tenant atau dedicated instance — dokumentasi saja, bukan Fase 2.

### 11.2 Tabel platform baru

#### `schools`

| Kolom | Keterangan |
|-------|------------|
| id | uuid PK |
| name | Nama sekolah |
| slug | unique — subdomain/path |
| npsn | nullable |
| email, phone, address | Kontak |
| logo_path | S3 path |
| timezone | default Asia/Jakarta |
| status | trial / active / suspended / expired |
| trial_ends_at | nullable |
| student_attendance_late_after | time — dari pilot settings |
| … | Field operasional lain dari `school_settings` atau FK 1:1 |

#### `subscriptions`

| Kolom | Keterangan |
|-------|------------|
| school_id | FK |
| plan | starter / professional / premium |
| period | monthly / yearly |
| starts_at, ends_at | |
| status | active / expired / cancelled |
| amount | decimal |
| payment_reference | nullable |

#### `school_user` *(deferred — tidak dipakai v1)*

Tidak diimplementasi di v1: **satu email = satu akun = satu sekolah** (lihat [§19](#19-keputusan-produk)).

### 11.3 Tabel domain — wajib `school_id`

- `users` — setiap user terikat satu `school_id` (kecuali `super-admin` platform)
- `teachers`, `students`, `academic_levels`, `classes`
- `academic_years`, `semesters`
- `school_settings` *(jika tidak digabung ke `schools`)*
- `academic_calendar_holidays`
- `attendance_class_submissions`, `student_attendances`, `qr_attendance_sessions`
- `teacher_attendance_submissions`, `teacher_attendances`
- `violation_types`, `violation_thresholds`, `student_violations`
- `character_point_types`, `student_character_points`
- `school_notifications`, `whatsapp_messages`
- `guardian_student`
- `backup_logs`, `activity_logs`

### 11.4 Tabel global (tanpa `school_id`)

- `attendance_statuses`
- `permissions` (definisi)
- `schools`, `subscriptions`

### 11.5 Spatie Permission — teams wajib

```php
// config/permission.php
'teams' => true,
'team_foreign_key' => 'school_id',
```

- Assign role **selalu** dengan konteks `school_id`.
- `super-admin` platform: guard/role terpisah, **bukan** `admin-sekolah` global.

### 11.6 Unique constraints (terkonfirmasi)

**Email akun — unik global (seluruh platform):**

```sql
UNIQUE users_email_unique (email)   -- satu email hanya satu akun; tidak bisa dipakai sekolah lain
```

Registrasi atau undang user dengan email yang sudah ada → pesan: *"Email sudah terdaftar."*  
Verifikasi email wajib saat aktivasi akun.

**Identitas bisnis per tenant:**

```sql
UNIQUE (school_id, nis)              ON students
UNIQUE (school_id, date)             ON academic_calendar_holidays
UNIQUE (school_id, student_id, date) ON student_attendances
UNIQUE schools_slug_unique (slug)    ON schools
```

Hapus unique global pilot (`nis`, `email` tanpa `school_id`) saat migrasi ke SaaS.

### 11.7 Trait `BelongsToSchool`

```php
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $query) {
            $schoolId = TenantContext::id(); // session/middleware, bukan hanya auth()->user()
            if ($schoolId) {
                $query->where($query->getModel()->getTable().'.school_id', $schoolId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->school_id)) {
                $model->school_id = TenantContext::id();
            }
        });
    }
}
```

### 11.8 Super-admin & support

- Wajib `TenantContext::set($schoolId)` eksplisit saat akses data tenant.
- Setiap aksi support → `activity_logs` dengan `is_super_admin_action = true`.
- **Jangan** hanya mengandalkan user tanpa `school_id` tanpa konteks eksplisit.

### 11.9 Storage

```
schools/{school_id}/logo/
schools/{school_id}/students/photos/...
schools/{school_id}/violations/evidence/...
```

### 11.10 Migrasi data pilot klien → SaaS

1. Insert `schools` untuk tenant **Cahaya Sunnah** (slug contoh: `cahaya-sunnah`, subdomain `cahaya-sunnah.platformsekolah.id`).  
2. Backfill `school_id` semua baris dari DB pilot.  
3. Ubah indexes → compound unique.  
4. Enable teams; re-assign roles per school.  
5. Upload file ke S3; update path di DB.  
6. Test suite isolasi tenant (zero leakage).  
7. Smoke test checklist §7.7 di cloud.

### 11.11 WhatsApp schema

Perluas tabel pilot **`whatsapp_messages`** (+ `school_id`, status gateway) — **jangan** duplikasi dengan `whatsapp_message_logs` kecuali rename terkontrol.

---

## 12. Struktur Repositori

### 12.1 Dua repo, satu produk

| Path | Peran | Fase |
|------|-------|------|
| `C:\Projects\project-cahaya-sunnah` | Pilot single-tenant, referensi fitur & UX | 1 (maintain sampai migrasi) |
| `C:\Projects\multi-school` | **Platform Sekolah** — SaaS multi-tenant, PRD | 2+ |

**Strategi implementasi (terkonfirmasi):** **Greenfield** di `multi-school` + port modul bertahap dari `project-cahaya-sunnah`. Repo pilot **tidak diubah** menjadi multi-tenant sampai migrasi disengaja; deploy LAN klien tetap aman.

### 12.2 Struktur folder pilot (referensi)

```text
project-cahaya-sunnah/
├── app/Http/Controllers
├── app/Models
├── app/Actions
├── app/Services
├── app/Policies
├── config/cahaya.php
├── database/migrations
├── resources/js/Pages
├── routes/web.php
├── tests/Feature
└── docker-compose.yml
```

### 12.3 Struktur tambahan SaaS (rencana)

```text
multi-school/
├── app/Http/Middleware/SetCurrentSchool.php
├── app/Models/School.php
├── app/Models/Concerns/BelongsToSchool.php
├── app/Support/TenantContext.php
├── app/Http/Controllers/Platform/   # super-admin, billing
└── tests/Feature/TenantIsolation/
```

---

## 13. Model Bisnis dan Pricing

### 13.1 Pendapatan

- **Utama:** langganan flat per sekolah (bulanan/tahunan).  
- **Bukan:** per siswa (SkoolaCloud model).  
- **Fase 3:** add-on / tier Premium (AI).  
- **Opsional:** lisensi on-prem sekali bayar (evaluasi pasar).

### 13.2 Paket (draft — validasi pasar)

| | Starter | Professional | Premium |
|---|:---:|:---:|:---:|
| **/bulan** | Rp 299.000 | Rp 599.000 | Rp 999.000 |
| **/tahun** | Rp 2.990.000 | Rp 5.990.000 | Rp 9.990.000 |
| Max siswa | 300 | 1.000 | ∞ |
| Max user | 10 | 30 | ∞ |
| Absensi & pelanggaran | ✓ | ✓ | ✓ |
| PDF & Excel | ✓ | ✓ | ✓ |
| Backup manual | ✓ | ✓ | ✓ |
| Portal wali murid | — * | ✓ | ✓ |
| WA gateway otomatis | — | ✓ | ✓ |
| Backup cloud harian | — | ✓ | ✓ |
| AI & otomasi lanjut | — | — | ✓ |

\* Portal dasar sudah di pilot; tier Starter = tanpa fitur cloud/WA premium atau limit kuota.

### 13.3 Trial

- 30 hari, setara Professional, tanpa kartu kredit.  
- 1 trial per NPSN atau domain email.  
- Habis trial: read-only 14 hari → suspend.

### 13.4 Founding school

5–10 sekolah pertama: harga terkunci seumur hidup, akses Professional, partner feedback.

### 13.5 Biaya operasional (20–50 sekolah)

Estimasi **Rp 800.000–1.650.000/bulan** (VPS, DB, storage, CDN, email, monitoring).  
Break-even: ~3–5 Starter atau 2–3 Professional.

---

## 14. Permission Matrix

### 14.1 Operasional per sekolah

| Permission | Admin | Guru | BK | Kepsek | Wali Murid |
|---|:---:|:---:|:---:|:---:|:---:|
| Kelola siswa/guru/kelas/user | ✓ | — | — | — | — |
| Settings sekolah | ✓ | — | — | — | — |
| Jenis pelanggaran | ✓ | — | ✓ | — | — |
| Input absensi siswa + QR | ✓ | ✓ | ✓ | — | — |
| Koreksi absensi siswa | ✓ | — | ✓ | — | — |
| Input absensi guru | ✓ | * | — | — | — |
| Koreksi absensi guru | ✓ | — | — | — | — |
| Input pelanggaran/karakter | ✓ | ✓ | ✓ | — | — |
| Validasi pelanggaran | ✓ | — | ✓ | — | — |
| Laporan semua murid | ✓ | — | ✓ | ✓ | — |
| Laporan anak sendiri | — | — | — | — | ✓ |
| Cetak/export laporan | ✓ | Terbatas | ✓ | ✓ | — |
| Backup | ✓ | — | — | — | — |
| Activity log | ✓ | — | — | — | — |

\* Guru jika `can_input_teacher_attendance`.

### 14.2 Platform (Fase 2 — Platform Sekolah)

| Role | Scope |
|------|-------|
| `super-admin` | Dashboard platform: daftar tenant, suspend/reaktivasi, reset admin sekolah (v1). Bukan `admin-sekolah` global. |
| Permission baru | `platform.tenants.manage`, `platform.tenants.reset-admin` (nama final mengikuti seeder); tenant: `portal.guardian.view`, `notifications.whatsapp.send`, `school.subscription.manage` |

---

## 15. Non-Functional Requirements

### 15.1 Performa

| Metrik | LAN (Fase 1) | Cloud (Fase 2) |
|--------|--------------|----------------|
| Dashboard | &lt; 2 dtk | &lt; 2 dtk |
| List page | &lt; 1 dtk | &lt; 1,5 dtk |
| Submit absensi | &lt; 3 dtk | &lt; 3 dtk |
| PDF 40 siswa | &lt; 10 dtk | &lt; 10 dtk |
| Import 500 siswa | &lt; 30 dtk | &lt; 30 dtk |

### 15.2 Keamanan

- CSRF, bcrypt, Form Request, MIME validation upload.  
- Activity log + IP/user agent.  
- Fase 2: HTTPS wajib, rate limit login, **automated tenant isolation tests**, audit super-admin.  
- QR: token terenkripsi + hash untuk validasi scan.

### 15.3 Browser & layar

- Chrome/Edge penuh; Firefox best-effort.  
- Utama: 1366×768 (staf); mobile utama untuk portal wali murid (Fase 2 polish).

### 15.4 SLA SaaS

- Uptime 99,5%.  
- Maintenance: Sabtu/Minggu dini hari, H-24 notice.  
- RTO &lt; 4 jam; RPO &lt; 24 jam.

### 15.5 Skalabilitas

| Fase | Target |
|------|--------|
| 1 | 1 sekolah, ≤ 2.000 siswa |
| 2 awal | 50 sekolah × ~500 siswa |
| 2 target | 500 sekolah, ~250.000 siswa |
| 3 | 1.000+ sekolah — evaluasi sharding |

Index wajib: `school_id`, `date`, `semester_id`, `class_id`, `status`.

---

## 16. Out of Scope

### 16.1 Bukan scope produk (semua fase)

- Dapodik sync, rapor Kemendikbud resmi.  
- Modul keuangan/SPP/akuntansi lengkap.  
- Kantin cashless / RFID wajib.  
- Native iOS/Android (PWA cukup).  
- Multi-country / multi-bahasa.

### 16.2 Belum di pilot — masuk Fase 2 platform

- Multi-sekolah / `school_id` / tenant URL.  
- Registrasi mandiri & billing otomatis.  
- Backup terjadwal multi-tenant di cloud.  
- WhatsApp **fully automated** via WABA (pilot: handoff OK).

### 16.3 Fase 3 (bukan Fase 2)

- AI assistant BK, ML pola, prediksi risiko.  
- PPDB, LMS.  
- **Bukan** “membangun modul poin karakter dari nol” — sudah Fase 1.

### 16.4 Opsional produk terpisah

- **On-site / LAN** — deploy di lokasi sekolah (model pilot klien); penawaran terpisah dari cloud SaaS ([§8.6](#86-deploy-on-site-opsional-terkonfirmasi)).

### 16.5 Out of scope komersial

- **Reseller / distributor** — tidak direncanakan v1.

---

## 17. Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| Guru tidak adopt | Tinggi | UX sederhana, training pilot 1 bulan |
| Data leakage tenant | Fatal | Teams + scope + test isolasi + audit impersonate |
| PRD vs kode tidak sinkron | Sedang | PRD v2.1 ini; update saat release |
| WA API policy | Sedang | Provider resmi; fitur opsional per paket |
| Server down jam sekolah | Sedang | Monitoring, SLA, prosedur manual sementara |
| PC rusak (Fase 1) | Sedang | Backup rutin ke drive eksternal |
| Cashflow negatif | Sedang | VPS kecil, break-even 2–3 sekolah Pro |
| Trial tidak konversi | Sedang | Follow-up, perbaiki onboarding dari drop-off |

---

## 18. Metrik Keberhasilan

### 18.1 Fase 1

| Metrik | Target |
|--------|--------|
| Sekolah pilot aktif | 1 |
| Absensi harian guru | &gt; 80% hari efektif |
| Bug blocker 2 minggu pasca go-live | 0 |
| Feedback konkret | ≥ 3 |
| Tests otomatis | ≥ 107 passed (baseline) |

### 18.2 Fase 2 (6 bulan pasca launch SaaS)

| Metrik | Target |
|--------|--------|
| Trial aktif | 20+ |
| Konversi trial → bayar | &gt; 30% |
| Sekolah berbayar | 10+ |
| MRR | Rp 3.000.000+ |
| Churn | &lt; 5%/bulan |
| Onboarding | &lt; 1 hari kerja |
| Insiden leakage | **0** |

### 18.3 Fase 3

| Metrik | Target |
|--------|--------|
| Upgrade Premium | &gt; 30% sekolah berbayar |
| Kepuasan BK (AI) | &gt; 4,0/5 |

---

## 19. Keputusan Produk

### 19.1 Terkonfirmasi (Mei 2026)

| # | Keputusan |
|---|-----------|
| **Brand SaaS** | **Platform Sekolah** — bukan nama sekolah klien Cahaya Sunnah |
| **Domain** | Rencana `platformsekolah.id` (belum dibeli); subdomain per tenant |
| **Tenant URL** | `{slug}.platformsekolah.id` + Cloudflare DNS/tunnel |
| **Super-admin v1** | Daftar sekolah, status, suspend/reaktivasi, reset password admin sekolah |
| **Email akun** | **Unik global**; satu email = satu akun; **verifikasi email wajib** |
| **Multi-sekolah per akun** | **Tidak** di v1 (tanpa `school_user`) |
| **Pembayar** | Sekolah atau yayasan; **bukan reseller** |
| **Repo SaaS** | Greenfield `multi-school`; pilot klien tetap di `project-cahaya-sunnah` |
| **On-prem** | Tetap ditawarkan untuk lokasi tertentu (deploy terpisah) |
| **Pilot klien** | Cahaya Sunnah = proyek sekolah; bisa jadi tenant pertama SaaS |

### 19.2 Masih terbuka

| # | Pertanyaan | Catatan |
|---|------------|---------|
| 1 | Tanggal beli domain `platformsekolah.id` | Boleh dev dengan `.test` / tunnel dulu |
| 2 | Slug resmi tenant Cahaya Sunnah | Contoh: `cahaya-sunnah` |
| 3 | Impersonate support | Setelah v1, dengan audit |
| 4 | Harga final paket | Draft di §13 — validasi pasar |

---

## 20. Glosarium

| Istilah | Definisi |
|---------|----------|
| **Platform Sekolah** | Brand produk SaaS multi-sekolah (`platformsekolah.id`) |
| **Proyek / pilot klien** | Implementasi untuk sekolah Cahaya Sunnah (`project-cahaya-sunnah`) |
| **Tenant** | Satu sekolah terdaftar di Platform Sekolah (`schools.id`) |
| **Super-admin** | Akun internal founder/tim; kelola tenant di dashboard platform |
| **Pilot** | Fase 1 single-tenant (biasanya on-prem klien) |
| **Port** | Membawa fitur pilot ke SaaS dengan tambahan `school_id` |
| **Platform layer** | Registrasi, billing, super-admin — bukan modul absensi |
| **BelongsToSchool** | Trait global scope isolasi tenant |
| **Team (Spatie)** | `school_id` sebagai scope role |
| **Points snapshot** | Poin tersimpan saat pelanggaran dibuat |
| **Submit lock** | Absensi kelas/tanggal terkunci setelah submit |
| **Handoff WA** | Pesan disiapkan + buka wa.me (pilot) |
| **Founding school** | Sekolah awal dengan harga terkunci |
| **MRR** | Monthly Recurring Revenue |

---

## Lampiran A — Peta modul pilot → SaaS

| Modul pilot | File/konsep kunci | Aksi Fase 2 |
|-------------|-------------------|-------------|
| Master data | `Student`, `Teacher`, `SchoolClass` | + `school_id`, compound unique |
| Absensi | `SubmitStudentAttendanceAction`, … | + scope tenant |
| QR | `QrAttendanceService` | + `school_id` pada session/token |
| Pelanggaran | `ViolationPointService` | + scope tenant |
| Karakter | `CharacterPointType`, … | + scope tenant |
| Guardian | `guardian_student`, `wali-murid` | + scope tenant |
| Notifikasi | `SchoolNotification` | + `school_id` |
| WA | `whatsapp_messages` | + `school_id`, gateway fields |
| Libur | `AcademicCalendarHoliday` | unique `(school_id, date)` |
| Settings | `SchoolSetting` | merge ke `schools` atau 1:1 FK |
| Backup | `CreateDatabaseBackupAction` | per-tenant dump + cloud schedule |

---

## Lampiran B — Urutan implementasi Fase 2 (disarankan)

1. Beli domain `platformsekolah.id` (atau lanjut dev dengan tunnel).  
2. `schools` + `TenantContext` + middleware `SetCurrentSchool` + wildcard DNS.  
3. Role `super-admin` + dashboard platform v1 ([§8.5](#85-dashboard-platform-super-admin-v1)).  
4. Spatie teams + `users.email` UNIQUE global.  
5. Backfill tenant Cahaya Sunnah (opsional).  
6. Port modul: master data → absensi → pelanggaran → laporan (dengan tests isolasi).  
7. Guardian + notifikasi + WA + karakter + kalender.  
8. Registrasi, wizard, trial, billing.  
9. Backup cloud, CI/CD, go-live sekolah ke-2.

---

*Dokumen ini adalah **single source of truth** untuk **Platform Sekolah** (SaaS) dan proyek pilot klien **Cahaya Sunnah** (`project-cahaya-sunnah`). Versi 2.2 — Mei 2026.*
