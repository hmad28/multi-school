# Project Scope — Platform Sekolah (SaaS)

> **Goal:** Mendefinisikan scope produk SaaS multi-sekolah, relasi ke pilot klien, dan modul yang di-port vs dibangun baru.

**Produk:** Platform Sekolah  
**Domain (rencana):** `platformsekolah.id`  
**Codebase:** `C:\Projects\multi-school`  
**Pilot referensi:** `C:\Projects\project-cahaya-sunnah` (sekolah klien **Cahaya Sunnah**)

---

## 1. Pemisahan produk vs proyek klien

| | Platform Sekolah | Proyek Cahaya Sunnah |
|---|------------------|----------------------|
| Brand | Platform Sekolah | Nama sekolah klien di kop surat |
| Deploy default | Cloud, multi-tenant | On-prem LAN (Docker di PC sekolah) |
| Repo | `multi-school` | `project-cahaya-sunnah` |
| User platform | `super-admin` (founder) | Tidak ada |

Pilot **bukan** nama SaaS; pilot adalah sumber utama app per sekolah/customer. Modul sekolah di `multi-school` harus copy/port dari root `C:\Projects\project-cahaya-sunnah`, lalu disesuaikan untuk multi-tenant (`school_id`, team permission, route tenant, brand Platform Sekolah). Fokus build baru ada di platform admin, marketing, onboarding, trial/billing, dan integrasi funnel.

---

## 2. Tujuan Fase 2 (SaaS)

1. Satu aplikasi cloud melayani **banyak sekolah** dengan isolasi data ketat.
2. Sekolah baru bisa **daftar mandiri** → subdomain `{slug}.platformsekolah.id`.
3. Founder punya **dashboard platform** untuk kelola tenant (daftar, suspend, reset admin).
4. Modul operasional dari pilot **di-port** (dashboard sekolah, absensi, QR, pelanggaran, karakter, laporan, wali murid, notifikasi, WA handoff, kalender libur).
5. Alur produk end-to-end harus nyambung: marketing page menjelaskan value → onboarding membuat tenant siap pakai → dashboard sekolah memakai pola operasional pilot → platform admin memantau lifecycle tenant tanpa masuk data sekolah.
6. Monetisasi: trial, langganan flat per sekolah (bukan reseller).

---

## 3. In scope — Platform layer (baru)

Ini misi utama `multi-school`, karena app operasional sekolah sudah punya pilot.

- [ ] Tabel `schools`, `subscriptions` (billing draft)
- [ ] Marketing CTA → registrasi sekolah + verifikasi email PIC
- [ ] Onboarding wizard (profil → TA → kelas → import → undang user)
- [ ] Resolusi tenant via **subdomain** + middleware `SetCurrentSchool`
- [ ] Spatie Permission **teams** (`school_id`)
- [ ] `users.email` **UNIQUE global**; satu email = satu akun = satu sekolah
- [ ] Dashboard **super-admin**: lifecycle tenant, trial, onboarding, billing ops, suspend/read-only, reset admin
- [ ] Audit log untuk aksi platform penting
- [ ] Backup cloud terjadwal (per tenant / full DB sesuai keputusan ops)
- [ ] Storage S3/R2: `schools/{school_id}/...`
- [ ] CI/CD, HTTPS, Cloudflare DNS/tunnel
- [ ] Automated tests **tenant isolation**

---

## 4. In scope — Tenant app port dari pilot (tambah `school_id`)

Semua modul per sekolah/customer yang sudah jalan di root `project-cahaya-sunnah` (baseline 107 tests) harus di-copy/port, bukan dirancang ulang. Penyesuaian wajib: `school_id`, `BelongsToSchool`, route `tenant.*`, Spatie teams, policy isolation, dan brand Platform Sekolah.

| Modul | Ringkas |
|-------|---------|
| Auth & roles | 5 role tenant; permissions per team |
| Master data | Siswa, guru, kelas, import Excel |
| Absensi siswa & guru | Submit lock, koreksi, rekap — ✅ Absensi Siswa (T1 Slice 2) ✅ Absensi Guru (T1 Slice 4) |
| QR absensi | Token, scanner, libur menolak scan — ✅ (T1 Slice 3) |
| Pelanggaran | Pending/validasi, threshold, snapshot poin |
| Poin karakter | Tipe + transaksi |
| Laporan murid | Scope role + wali murid |
| PDF / Excel | Rekap, surat panggilan |
| Notifikasi in-app | Per tenant |
| WhatsApp | Handoff; evolusi gateway di tier Pro |
| Kalender libur | Per tenant — ✅ (T1 Slice 1) |
| Backup manual | Per tenant |
| Dashboard operasional | Beranda per sekolah mengikuti sistem root pilot `project-cahaya-sunnah`: kartu ringkasan harian, shortcut modul, status absensi/kelas, dan konteks tenant aktif |

Detail acceptance criteria pilot: lihat `project-cahaya-sunnah/docs/plans/01-project-scope.md` §9.

---

## 5. Out of scope (Fase 2 awal)

- Reseller / channel partner
- Impersonate tenant tanpa audit (defer)
- MRR dashboard, agregat siswa lintas tenant di super-admin
- Dapodik, modul keuangan/SPP, RFID/kantin
- Native mobile app (PWA cukup)
- Multi-country / multi-bahasa
- AI layer (Fase 3)
- `school_user` / satu email banyak sekolah (v1)

---

## 6. Dua mode deploy produk

| Mode | Kapan | Tenancy |
|------|-------|---------|
| **Cloud SaaS** | Default penjualan | `school_id` + subdomain |
| **On-site** | Sekolah minta server di lokasi | Single-tenant (seperti pilot); penawaran terpisah |

Satu codebase; feature flag atau build tanpa platform routes untuk on-site opsional.

---

## 7. Persona

### 7.1 Tenant (per sekolah)

Sama pilot: Admin TU, Guru Kelas, BK/Walas, Kepala Sekolah, Wali Murid.

### 7.2 Platform (internal)

**Super-admin:** founder/tim — hanya di `admin.platformsekolah.id` (atau route `/admin`).

---

## 8. Success metrics (Fase 2)

| Metrik | Target |
|--------|--------|
| Insiden data leakage antar tenant | **0** |
| Onboarding sekolah baru | &lt; 1 hari kerja |
| Test suite | Semua hijau + suite isolasi tenant |
| Trial → paid (6 bln pasca launch) | &gt; 30% |
| Sekolah berbayar | 10+ |

---

## 9. Permission matrix (ringkas)

Lihat PRD §14. Tenant permissions = sama pilot + scope team. Platform:

| Aksi | Super-admin |
|------|:-----------:|
| Lihat daftar sekolah | ✓ |
| Suspend / reaktivasi | ✓ |
| Reset admin sekolah | ✓ |
| Lihat/edit data murid tenant | ✗ (v1) |
| Impersonate | ✗ (v1) |

---

## 10. Referensi silang

- [02-erd-database.md](./02-erd-database.md)
- [04-development-plan.md](./04-development-plan.md)
- [../PRD-cahaya-sunnah-school-system.md](../PRD-cahaya-sunnah-school-system.md)
