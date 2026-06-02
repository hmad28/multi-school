# Development Plan — Platform Sekolah

> **Goal:** Milestone implementasi SaaS dari greenfield `multi-school` dengan port bertahap dari pilot.

**Prinsip:** Jangan rewrite app sekolah dari nol — bagian per sekolah/customer di-port dari `C:\Projects\project-cahaya-sunnah` lalu disesuaikan `school_id`, permission team, dan brand Platform Sekolah. Yang benar-benar baru adalah **platform admin, marketing, onboarding, billing/trial, dan koneksi funnel end-to-end**.

---

## 0. Status implementasi (terakhir diperbarui: 2026-06-02)

| Phase | Status | Catatan |
|-------|--------|---------|
| **P0** Bootstrap | ✅ Selesai | Laravel 13, Breeze Inertia Vue TS, Docker Compose, domain routing, CI |
| **P1** Tenancy | ✅ Selesai | schools, resolver, middleware, tenant-aware auth/routes, dashboard brand polish, super-admin tenants UI, seed demo+alfa |
| **P2** Master data | 🟡 Core selesai | Schema, model, permission, CRUD siswa/guru/kelas/akademik, seed demo, tests/build hijau; import/settings/user tenant menyusul |
| **P2.5** Tenant dashboard alignment | ✅ Selesai baseline | Dashboard sekolah mengikuti struktur root pilot dan memakai ringkasan data P2; slot P3–P5 disiapkan |
| **T1** Tenant app port | 🟡 Slice 1-6 selesai ✅ | Slice 1 Kalender Akademik ✅, Slice 2 Absensi Siswa ✅, Slice 3 QR Attendance ✅, Slice 4 Absensi Guru ✅, Slice 5 Pelanggaran ✅, Slice 6 Poin Karakter ✅; Slice 7-9 berikutnya |
| **PL1** Platform admin | 🟡 Baseline selesai | Dashboard founder, tenant list/detail, status, reset password, usage summary, trial ending soon sudah ada; audit/billing ops menyusul |
| **GT1** Marketing + onboarding funnel | 🟡 Parsial | Marketing Astro selesai; backend register/onboarding belum nyambung |
| **PRD** Production | ⬜ Belum | Domain, deploy, monitoring, storage, billing production |

**Cara menjalankan (lokal):**

```bash
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8888   # jika 8000 diblokir Windows

cd marketing && npm run dev
```

**Link dev (path routing default ON — `PLATFORM_PATH_ROUTING=true`):**

| Apa | URL | Login |
|-----|-----|-------|
| Marketing | http://127.0.0.1:4321/ | — |
| Super-admin login | http://127.0.0.1:8888/platform/login | `super@platformsekolah.test` / `password` |
| Super-admin dashboard | http://127.0.0.1:8888/platform/dashboard | `super@platformsekolah.test` / `password` |
| Super-admin tenants | http://127.0.0.1:8888/platform/tenants | (setelah login) |
| Tenant demo login | http://127.0.0.1:8888/t/demo/login | `admin@demo.test` / `password` |
| Tenant demo app | http://127.0.0.1:8888/t/demo/dashboard | (setelah login) |

Setelah ubah routing: `php artisan config:clear` lalu restart `php artisan serve`.

**Hosts dev (Windows: `C:\Windows\System32\drivers\etc\hosts`):**

```text
127.0.0.1 platformsekolah.test
127.0.0.1 admin.platformsekolah.test
127.0.0.1 demo.platformsekolah.test
```

---

## 1. Prinsip delivery

1. Vertical slice per milestone — tiap akhir milestone ada UI + test hijau.
2. **Tenant isolation test** sebelum fitur operasional di-port masal.
3. Pilot repo tetap utuh untuk LAN client sampai migrasi disengaja.
4. Commit per task group; PR kecil reviewable.
5. Staging subdomain nyata sebelum production.

---

## 2. Milestone overview

| Phase | Durasi (est.) | Deliverable |
|-------|---------------|-------------|
| **P0** | 3–5 hari | Bootstrap Laravel, Docker dev, domain routing lokal |
| **P1** | 1–2 minggu | `schools`, tenant middleware, teams, super-admin v1 |
| **P2** | 2–3 minggu | Port master data + settings per tenant |
| **P2.5** | 2–4 hari | Dashboard tenant per sekolah mengikuti root pilot + ringkasan data P2 |
| **T1 Tenant app port** | 4–6 minggu bertahap | Contek/port app sekolah root pilot: absensi, QR, kalender, pelanggaran, karakter, laporan, wali murid, notifikasi, backup |
| **PL1 Platform admin** | 1–2 minggu | Dashboard founder, tenant detail, audit reset password, trial/billing ops, suspend/read-only controls |
| **GT1 Marketing + onboarding funnel** | 2–3 minggu | CTA marketing → register school → verify email → onboarding 5 langkah → tenant dashboard |
| **PRD Production** | 1 minggu | Production deploy, storage, monitoring, migrasi tenant pilot opsional |

---

## 3. P0 — Bootstrap

### Tasks

- [x] `laravel new` / copy skeleton ke `multi-school`
- [x] Breeze Inertia Vue + TypeScript + Tailwind
- [x] Docker Compose: app, nginx, mysql, redis
- [x] Package: spatie/permission (teams), excel, dompdf
- [x] `config/platform.php`, timezone Asia/Jakarta
- [x] Route domain lokal: `*.platformsekolah.test` → `/etc/hosts` atau Valet
- [x] CI: `php artisan test` on push

### Acceptance

- [x] Login page di `http://platformsekolah.test` dan `http://demo.platformsekolah.test` resolve (meski 404 tenant OK)
- [x] `php artisan test` green (smoke) — 28 tests

---

## 4. P1 — Tenancy & platform admin

### Tasks

- [x] Migration `schools`, `subscriptions` (minimal)
- [x] Migration alter: `users.school_id`, email unique global
- [x] Enable Spatie teams (`school_id` UUID); seed roles + `super-admin`
- [x] `TenantResolver`, `TenantContext`, `SetCurrentSchool` middleware
- [x] `BelongsToSchool` trait + model `Student` (contoh isolasi)
- [x] Platform UI: `Pages/Platform/Tenants/Index` — list, suspend, activate
- [x] Reset password admin sekolah (flash password sementara)
- [ ] Audit log reset password (P2+)
- [x] Tests: `TenantIsolationTest`, `SuperAdminCanListTenantsTest`
- [x] Dev routes path: `/t/{slug}/…`, `/platform/…` (tanpa hosts)
- [x] Tenant-aware shared Inertia props (`school`, roles, permissions)
- [x] Tenant/platform-safe dashboard, profile, logout, and auth redirects
- [x] Dashboard/app shell mengikuti ritme root pilot `project-cahaya-sunnah` dengan token brand Platform Sekolah (`brand-*`)

### Acceptance

- [x] Dua sekolah seed → user A tidak lihat siswa sekolah B (404 scoped)
- [x] Super-admin akses `/platform/tenants`; tenant admin ditolak (403)

---

## 5. P2 — Master data port

### Current baseline before P2

- P0/P1 sudah menyediakan tenant resolver, context, auth redirects, shared Inertia props, platform tenant list, dan contoh model `Student` berscope `school_id`.
- Core P2 sudah menyediakan model/controller/page untuk `Teacher`, `AcademicLevel`, `SchoolClass`, `AcademicYear`, `Semester`, dan CRUD siswa berscope tenant.
- P2 follow-up yang masih tersisa: import Excel siswa, school profile/settings, dan user management tenant.
- P2.5 dashboard tenant sudah memakai ringkasan P2 agar P3 absensi bisa masuk tanpa redesign besar.

### Tasks

- [x] Port migrations: teachers, academic_levels, classes, students, years, semesters
- [x] Port controllers, requests, policies (scoped)
- [x] Core Inertia pages: siswa, guru, kelas, akademik
- [x] Seed demo/alfa master data + team-scoped permissions
- [ ] Import Excel siswa + template
- [ ] School profile edit (= fields di `schools` atau settings 1:1)
- [ ] User management per tenant (assign role with team_id)

### Acceptance

- [x] Admin tenant CRUD siswa/guru/kelas/akademik dasar
- [x] Dashboard tenant membaca jumlah siswa/guru/kelas dan tahun ajaran/semester aktif dari data tenant
- [ ] NIS duplikat **dalam sekolah sama** ditolak; NIS sama di sekolah lain **boleh** perlu test eksplisit baru
- [ ] Import 100 baris dengan validasi per baris

---

## 5.5 P2.5 — Tenant dashboard alignment

### Goal

Dashboard per sekolah (`tenant.dashboard`) harus mengikuti sistem root pilot `C:\Projects\project-cahaya-sunnah`, agar modul berikutnya tinggal copas/port fitur dan mengisi slot data yang sama.

### Tasks

- [x] Audit dashboard root pilot dan mapping slot ke multi-tenant app
- [x] Ubah `Tenant\DashboardController` agar mengirim ringkasan P2: jumlah siswa aktif, guru aktif, kelas aktif, tahun ajaran/semester aktif
- [x] Rework `resources/js/Pages/Dashboard.vue` untuk mode tenant: hero sekolah, KPI operasional, shortcut modul, panel aktivitas/empty state
- [x] Pastikan sidebar route master data dan dashboard saling nyambung
- [x] Update test Inertia dashboard untuk shared prop + metrics tenant
- [ ] Pisahkan tampilan central/platform jika nanti dashboard pusat butuh halaman sendiri

### Acceptance

- Admin sekolah login ke `/t/demo/dashboard` melihat dashboard operasional sekolah seperti ritme pilot, bukan halaman placeholder SaaS
- Metrics P2 tampil dari data tenant aktif dan tidak bocor antar sekolah
- Slot absensi/pelanggaran/laporan siap diisi P3–P5 tanpa redesign besar

---

## 6. T1 — Tenant app port dari pilot

### Goal

Bagian per sekolah/customer bukan product discovery baru. App tenant harus contek/port dari root `C:\Projects\project-cahaya-sunnah`, lalu disesuaikan untuk SaaS multi-tenant: `school_id`, route `tenant.*`, permission team, shared props `school`, dan brand Platform Sekolah.

### Modul yang di-port

- Absensi siswa/guru, submit lock, koreksi, rekap ✅ (Slice 2)
- QR attendance: session, token, scanner, libur menolak scan
- Kalender akademik/libur ✅ (Slice 1)
- Pelanggaran + validasi/reject
- Poin karakter + transaksi/snapshot
- Notifikasi in-app per tenant
- Laporan PDF/Excel + kop dari data `schools`
- Guardian/wali murid portal dan relasi anak
- WhatsApp handoff
- Backup manual per tenant
- Dashboard operasional lanjutan/charts dari data modul di atas

### Porting rules

- Copy pola controller/request/model/page/test dari pilot terlebih dulu, jangan desain ulang.
- Tambah `school_id` dan `BelongsToSchool` untuk semua entity tenant.
- Semua query harus tenant-scoped dan punya test forbidden/cross-tenant.
- UI mengikuti root pilot; brand warna memakai token Platform Sekolah.
- Acceptance pilot tetap jadi sumber utama, lalu ditambah acceptance isolation tenant.

### Acceptance

- Fitur tenant setara pilot untuk modul yang sudah di-port.
- Tidak ada data leak antar sekolah.
- Test pilot yang relevan dipindahkan dan hijau di SaaS.

#### Slice 2 — Absensi Siswa ✅

- [x] Migrasi `attendance_statuses` (global reference), `student_attendances`, `attendance_class_submissions` dengan `school_id`
- [x] Model `AttendanceStatus`, `StudentAttendance`, `AttendanceClassSubmission` dengan `BelongsToSchool`
- [x] Permission `attendance.students.input` + `.correct` di `admin-sekolah`
- [x] Rute tenant `tenant.attendance.students.*` (index, store, finalize, correct, recap)
- [x] `SubmitStudentAttendanceAction` + `CorrectStudentAttendanceAction` + `AttendanceCalendarService`
- [x] Inertia pages: Index (filter kelas/tanggal/status/scan state, roster, finalize, koreksi), Recap (rekapitulasi per periode)
- [x] Sidebar nav item Absensi Siswa → route live
- [x] Seed attendance statuses (H/T/I/S/A) + sample attendance per tenant
- [x] Tenant isolation tests: 7 tests (scoped, submit, finalize, duplicate rejection, cross-tenant 403, recap)
- [x] Migrasi tabel `academic_calendar_holidays` dengan `school_id`
- [x] Model `AcademicCalendarHoliday` dengan `BelongsToSchool` + soft deletes
- [x] Permission `academic-calendar.manage` di `admin-sekolah`
- [x] Rute tenant `tenant.academic-calendar.holidays.*`
- [x] CRUD Inertia pages: Index (kalender + list per bulan), Create, Edit
- [x] Sidebar nav item Kalender Akademik
- [x] Seed 2 hari libur per sekolah
- [x] Validation: date unique per school, cross-tenant date allowed
- [x] Policy + tenant isolation tests

#### Slice 3 — QR Attendance ✅

- [x] Migration: add `qr_token`, `qr_token_hash` to students + `qr_attendance_sessions` table
- [x] Model `QrAttendanceSession` dengan `BelongsToSchool`
- [x] Student model: encrypted `qr_token`, hidden `qr_token_hash`, `has_qr_token` accessor
- [x] `QrAttendanceService`: token issuance, session creation (10-min expiry), scan recording (arrival/departure), late status auto-apply, late violation auto-creation, holiday rejection
- [x] `QrStudentAttendanceController`: index (scanner page), session, scan, studentQr, regenerateStudentQr
- [x] Vue pages: QrScanner (camera + BarcodeDetector/jsQR + manual input + feedback modal), QrToken (QR display + print + regenerate)
- [x] npm packages: `jsqr`, `qrcode`
- [x] Routes: qr index, session, scan, token, regenerate
- [x] Sidebar: QR Scanner link in Absensi Siswa header
- [x] QR tests: 5 tests (page loads, token issue, scan creates attendance, invalid token error, regenerate)

#### Slice 4 — Teacher Attendance ✅

- [x] Migrasi `teacher_attendances` + `teacher_attendance_submissions` dengan `school_id`
- [x] Models: `TeacherAttendance`, `TeacherAttendanceSubmission` dengan `BelongsToSchool`
- [x] Actions: `SubmitTeacherAttendanceAction`, `CorrectTeacherAttendanceAction`
- [x] Permission `attendance.teachers.input` + `.correct` di `admin-sekolah`
- [x] Rute tenant `tenant.attendance.teachers.*` (index, store, correct)
- [x] Inertia pages: Index (date picker, teacher roster, status select, submit, correct), Recap
- [x] Sidebar nav item Absensi Guru → route live
- [x] Tests: 4 tests (page loads, submit, duplicate rejection, cross-tenant 403)

---

#### Slice 5 — Violations / Pelanggaran ✅

- [x] Migration: `violation_types` (global), `violation_thresholds` (global), `student_violations` (tenant with `school_id`)
- [x] Models: `ViolationType`, `ViolationThreshold` (global), `StudentViolation` with `BelongsToSchool`
- [x] Actions: `CreateStudentViolationAction`, `ValidateStudentViolationAction`, `RejectStudentViolationAction`
- [x] Permissions: `violations.input`, `violations.validate`, `violations.manage-types` at `admin-sekolah`
- [x] Form requests with tenant-scoped existence validation
- [x] Controllers: `ViolationTypeController` (CRUD), `StudentViolationController` (index, create, store, pending, validate, reject)
- [x] Inertia pages: Violations/Types (Index, Create, Edit), Violations/Students (Index with filters + ThresholdBadge, Create, Pending with validate/reject)
- [x] Sidebar nav item Pelanggaran → route live
- [x] Seed 9 violation types (ringan/sedang/berat), 4 thresholds (25/50/75/100)
- [x] Tests: 8 tests (index, create type, student index, student create, validate, reject, pending page, cross-tenant 403)

#### Slice 6 — Character Points / Poin Karakter ✅

- [x] Migration: `character_point_types` (global), `student_character_points` (tenant with `school_id`)
- [x] Models: `CharacterPointType` (global), `StudentCharacterPoint` with `BelongsToSchool`
- [x] Action: `CreateStudentCharacterPointAction` with semester snapshot
- [x] Permissions: `character-points.view`, `character-points.input`, `character-points.manage-types` at `admin-sekolah`
- [x] Form requests with tenant-scoped existence validation
- [x] Controllers: `CharacterPointTypeController` (CRUD), `StudentCharacterPointController` (index with totals per semester, create, store)
- [x] Inertia pages: CharacterPoints/Types (Index, Create, Edit), CharacterPoints/Students (Index with point totals, Create)
- [x] Sidebar nav item Poin Karakter → route live
- [x] Seed 9 character point types (akhlak/ibadah/sosial/kedisiplinan/akademik)
- [x] Tests: 5 tests (index type, create type, student index, student create, cross-tenant 403)

## 7. PL1 — Platform admin layer

### Goal

Bangun dashboard admin/founder untuk mengelola lifecycle pelanggan tanpa mengelola data operasional sekolah.

### Tasks

- [x] Tenant list/status/suspend/activate/reset admin password baseline
- [x] Tenant detail page: profil sekolah, subscription/trial, user tenant, usage summary non-sensitif
- [x] Platform dashboard: jumlah tenant trial/active/suspended, trial ending soon, recent signups, estimasi MRR seed/manual
- [ ] Audit log untuk reset password, suspend/activate, billing status changes
- [ ] Trial/read-only controls yang nyambung ke tenant middleware/UI
- [ ] Billing ops manual draft sebelum payment gateway

### Acceptance

- Founder bisa tahu tenant mana yang aktif, trial hampir habis, suspended, atau belum selesai onboarding.
- Founder tidak bisa browse/edit data siswa/guru tenant di v1.

---

## 8. GT1 — Marketing + onboarding funnel

### Goal

Nyambungin marketing page, registrasi sekolah, onboarding, dan dashboard tenant supaya customer baru bisa go-live tanpa developer.

### Tasks

- [x] Marketing Astro baseline: home, harga, daftar CTA
- [ ] CTA `/daftar` → backend register school
- [ ] Register school: PIC user, school name, slug/subdomain, email verify
- [ ] Onboarding wizard 5 langkah: profil sekolah → tahun ajaran/semester → kelas → import siswa → undang user
- [ ] Seed/default permission tenant baru setelah onboarding
- [ ] Redirect selesai onboarding ke `tenant.dashboard`
- [ ] Trial 14 hari + email reminders + platform admin visibility
- [ ] Pricing/trial copy di marketing harus sama dengan behavior backend

### Acceptance

- Customer dari landing bisa daftar, verifikasi, onboarding, lalu masuk dashboard sekolah dengan data awal.
- Platform admin bisa lihat status onboarding/trial customer tersebut.

---

## 9. PRD — Production platform

### Tasks

- [x] Desain landing → implementasi **Astro** (`marketing/`) — home, harga, daftar
- [ ] Deploy Astro (Nginx mini PC / Cloudflare — lihat ADR 0005)
- [ ] CTA / form daftar → Laravel API (form `/daftar` masih menunggu backend)
- [ ] Register school → email verify → create school + slug + subdomain
- [ ] Onboarding wizard (5 langkah) sebagai jembatan marketing → dashboard tenant: profil sekolah, tahun ajaran, kelas, import siswa, undang user
- [ ] Trial **14 hari** job + email reminders (`PLATFORM_TRIAL_DAYS`, selaras marketing)
- [ ] Billing integration draft (Midtrans/Xendit) — bisa flag manual dulu
- [ ] S3 storage prod; backup scheduled command

### Acceptance

- Sekolah baru dari nol bisa go-live tanpa developer
- Trial habis → read-only

---

## 10. P7 — Production

### Tasks

- [ ] Beli domain `platformsekolah.id`
- [ ] Cloudflare wildcard + tunnel atau VPS + SSL
- [ ] GitHub Actions deploy staging/prod
- [ ] Sentry, uptime monitor
- [ ] Opsional: import data dari DB pilot client ke tenant `cahaya-sunnah`

### Acceptance

- Smoke checklist PRD §7.7 di production subdomain
- RTO/RPO documented

---

## 11. Definisi selesai (per PR)

- [ ] Feature tests untuk path happy + forbidden cross-tenant
- [ ] Pint / ESLint clean
- [ ] Activity log untuk write penting
- [ ] UI label bahasa Indonesia
- [ ] Dokumen plan di-update jika schema berubah

---

## 12. Risiko timeline

| Risiko | Mitigasi |
|--------|----------|
| Port besar sekaligus | Ikuti P2–P5 order; jangan skip P1 tests |
| Domain belum dibeli | Dev dengan `.test` + tunnel |
| Scope creep billing | P6 billing bisa manual invoice dulu |

---

## 13. Referensi

- [01-project-scope.md](./01-project-scope.md)
- [05-backend-plan.md](./05-backend-plan.md)
- Pilot milestones M0–M6+: `project-cahaya-sunnah/docs/plans/04-development-plan.md`
