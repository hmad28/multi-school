# Development Plan — Platform Sekolah

> **Goal:** Milestone implementasi SaaS dari greenfield `multi-school` dengan port bertahap dari pilot.

**Prinsip:** Jangan rewrite app sekolah dari nol — bagian per sekolah/customer di-port dari `C:\Projects\project-cahaya-sunnah` lalu disesuaikan `school_id`, permission team, dan brand Platform Sekolah. Yang benar-benar baru adalah **platform admin, marketing, onboarding, billing/trial, dan koneksi funnel end-to-end**.

---

## 0. Status implementasi (terakhir diperbarui: 2026-06-03 — GT1 core)

| Phase | Status | Catatan |
|-------|--------|---------|
| **P0** Bootstrap | ✅ Selesai | Laravel 13, Breeze Inertia Vue TS, Docker Compose, domain routing, CI |
| **P1** Tenancy | ✅ Selesai | schools, resolver, middleware, tenant-aware auth/routes, dashboard brand polish, super-admin tenants UI, seed demo+alfa |
| **P2** Master data | 🟡 Core ✅, 3 deferred | Schema, model, permission, CRUD siswa/guru/kelas/akademik, seed demo, tests/build hijau; NIS duplicate test ✅. Import/settings/user tenant deferred (P2 follow-up). |
| **P2.5** Tenant dashboard alignment | ✅ Selesai baseline | Dashboard sekolah mengikuti struktur root pilot dan memakai ringkasan data P2; slot P3–P5 disiapkan |
| **T1** Tenant app port | 🟡 Slice 1-8 selesai ✅ | Slice 1-8 semua ✅; Slice 9 berikutnya |
| **PL1** Platform admin | ✅ Selesai | Dashboard founder, tenant list/detail, status, reset password, usage summary, trial ending soon, audit log (ActivityLogger), trial expiry middleware + countdown UI, billing ops manual (subscription list + status toggle). PL1 acceptance #1 (founder lihat aktivitas tenant) dan #2 (founder tidak bisa browse data siswa/guru) ✅ diverifikasi code. |
| **GT1** Marketing + onboarding funnel | 🟡 Core selesai ✅ | Registrasi sekolah mandiri (form Laravel + RegisterSchoolAction + verifikasi email enforced), onboarding wizard soft-gate 5 langkah, trial reminders command + expiry, visibility onboarding/trial di platform admin, CTA marketing → app. Email reminders butuh mail server di PRD. |
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

## 0.1 Temuan audit (review 2026-06-03)

Hasil review menyeluruh codebase. C1, C2, dan katalog per-tenant **sudah diperbaiki** (2026-06-03). Sisanya dicatat agar tidak hilang.

### 🔴 CRITICAL

**C1 — Query kolom `full_name` pada tabel `students` akan error di produksi (MySQL).** ✅ **DIPERBAIKI 2026-06-03**
Kolom siswa sebenarnya `name` (migrasi `2026_05_31_130003_create_students_table.php:14`), bukan `full_name`. Tabel yang punya `full_name` hanya `teachers`. `full_name` dipakai untuk *student* di 9 file PHP + 6 file Vue — semua sudah diganti ke `name`:
- `StudentViolationController`, `StudentCharacterPointController`, `ReportController`, `GuardianDashboardController`, `StudentReportData`, `StudentAttendanceExport`, `ViolationExport`, `CharacterPointExport` (key prop Inertia `full_name` di `GuardianDashboardController`/`StudentReportData` tetap, hanya sumber `$student->name`).
- Vue: `Violations/Students/{Index,Pending,Create}`, `CharacterPoints/Students/{Index,Create}`, `Reports/Index`.

**Susulan C1 (Opus 4 re-audit 2026-06-03):** 3 sisa `$student?->full_name` accessor di `GuardianDashboardController` (latestAttendances/latestCharacterPoints/latestViolations, baris 68/81/93) terlewat dari fix pertama. Beda dari kasus MySQL — ini akses accessor pada model tanpa atribut `full_name` → diam-diam `null` di **semua** engine (SQLite & MySQL), jadi nama siswa kosong di 3 tabel "terbaru" dashboard wali. Test lama hijau karena cuma assert prop *ada*, tak pernah cek isi. Diperbaiki ke `->name`; `GuardianTest` diperkuat assert `children.0.full_name` & `latestAttendances.0.student_name` = nama siswa nyata (regression guard).

Akar masalah: **test pakai SQLite, produksi MySQL 8**. SQLite memperlakukan identifier `"full_name"` yang tak dikenal sebagai *string literal* (`SELECT "full_name"` mengembalikan teks `'full_name'`, bukan error), sehingga test hijau **tidak menangkap** bug ini. Di MySQL → `Unknown column 'full_name'` → halaman 500. **Belum ditangani:** job CI yang jalan di MySQL (lihat MEDIUM).

**C2 — Otorisasi portal wali murid pakai pencocokan nama fuzzy.** ✅ **DIPERBAIKI 2026-06-03**
Sebelumnya `GuardianStudentReportController` memberi akses bila `str_contains($student->guardian_name, $user->name)`, dan `GuardianDashboardController` fallback `where guardian_name like %name%` lalu auto-link via `syncWithoutDetaching` → "Ana" cocok "Anastasia"/"Diana" → kebocoran lintas-keluarga. **Perbaikan:** fuzzy fallback + auto-link dihapus total. Akses kini **hanya** lewat pivot `guardian_student` eksplisit; tanpa link → dashboard kosong / 403. Pivot di-seed saat registrasi/onboarding (atau manual oleh admin).

### 🟡 MEDIUM

- **Tabel katalog global bisa di-CRUD tenant**: ✅ **DIPERBAIKI 2026-06-03** — `violation_types`, `character_point_types`, `violation_thresholds` kini **per-tenant** (`school_id` + trait `BelongsToSchool`). Unique `violation_thresholds.points` → compound `(school_id, points)`. Default katalog di-seed per sekolah via `App\Actions\Catalog\SeedDefaultCatalogAction` (dipakai `PlatformSeeder`, seam untuk registrasi GT1). `ViolationSeeder` lama dihapus. Test isolasi katalog ditambah di `ViolationTest`/`CharacterPointTest`.
- **Test engine ≠ produksi**: ✅ **DIPERBAIKI 2026-06-03** — ditambah job CI `php-tests-mysql` di `.github/workflows/tests.yml` (service MySQL 8.0, env DB level-job override `phpunit.xml` yang `force="false"`). Job SQLite existing tetap jalan paralel. Catatan: belum diverifikasi run penuh dari lokal (Docker daemon mati), akan jalan saat push. Total test: 126 passing, 2 skipped (domain routing), 549 assertions (termasuk GT1 SchoolRegistrationTest 10 test).
- **Master data tanpa test**: ✅ **DIPERBAIKI 2026-06-03** — `tests/Feature/Tenant/MasterDataTest.php` (17 test): CRUD + validasi + isolasi lintas-tenant untuk Students, Teachers, Classes, Academic setup.
- **Otorisasi tidak konsisten**: ✅ **DITINJAU 2026-06-03 — bukan celah, dijadikan konvensi.** Isolasi tenant dijamin 4 lapis: (1) middleware `SetCurrentSchool` set `TenantContext` + Spatie team, (2) global scope `BelongsToSchool` di semua model domain (route-binding/`findOrFail` lintas-tenant → 404), (3) Policy cek `school_id === TenantContext::id()`, (4) FormRequest scope `unique`/`exists` ke `TenantContext::id()`. Tiga gaya (Policy, `abort_unless(...can())` inline, FormRequest `authorize()`) adalah call-site berbeda dari cek izin yang sama, bukan gap. Konvensi: **master data & domain berbasis model → Policy; aksi katalog/operasional sederhana → inline `abort_unless`.** Dibuktikan oleh `MasterDataTest`, `ViolationTest`, `CharacterPointTest` (akses lintas-tenant ditolak 403/404). Refactor menyamakan gaya berisiko membuka celah tanpa manfaat keamanan → tidak dilakukan.

### 🟢 LOW

- ✅ **DIPERBAIKI 2026-06-03** — 5 nav placeholder `href: '#'` (WhatsApp, Notifikasi, Backup, User, Pengaturan) di `AuthenticatedLayout.vue` di-set `show: false` (disembunyikan sampai fiturnya dibangun), bukan dibiarkan clickable-mati.
- ✅ **DIPERBAIKI 2026-06-03** — Prop TypeScript `any` (~42x). Dibuat `resources/js/types/domain.ts` (Student, Teacher, SchoolClass, AcademicLevel/Year, Semester, CatalogType, AttendanceStatus, Paginated<T>, dll). 23 prop entitas di-type ulang. Sisa 6 `any` sengaja dipertahankan untuk row view-model denormalisasi (hasil join/agregat: `rows`, `attendances`, `Paginated<any>`) — typing presisi rapuh tanpa manfaat. **Bonus temuan kritis:** `vue-tsc` exit 2 (build `vue-tsc && vite build` rusak) karena 5 halaman impor `@/Components/App/Pagination.vue` yang **tidak ada filenya**. Komponen dibuat; `vue-tsc` kini exit 0, `npm run build` hijau.
- ✅ **DIPERBAIKI 2026-06-03** — `tests/Unit/ExampleTest.php` & `tests/Feature/ExampleTest.php` scaffolding dihapus.

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
- [x] `php artisan test` green (smoke) — 28 tests (116 passing di fase PL1)

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
- [x] Audit log reset password (selesai di PL1 — `ActivityLogger`)
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
- [ ] Import Excel siswa + template — **deferred: P2 follow-up**
- [ ] School profile edit (= fields di `schools` atau settings 1:1) — **deferred: P2 follow-up**
- [ ] User management per tenant (assign role with team_id) — **deferred: P2 follow-up**

### Acceptance

- [x] Admin tenant CRUD siswa/guru/kelas/akademik dasar
- [x] Dashboard tenant membaca jumlah siswa/guru/kelas dan tahun ajaran/semester aktif dari data tenant
- [x] NIS duplikat **dalam sekolah sama** ditolak (StoreStudentRequest unique + school_id scope, test MasterDataTest.php:85)
- [ ] Import 100 baris dengan validasi per baris — **deferred: P2 follow-up**

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
- [ ] Pisahkan tampilan central/platform jika nanti dashboard pusat butuh halaman sendiri — **deferred: not needed yet, platform dashboard is standalone at /platform/dashboard**

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

#### Slice 7 — Reports (PDF/Excel) ✅

- [x] Blade PDF templates: layout (letterhead from `TenantContext::school()`), student-attendance, teacher-attendance, violations, parent-call-letter
- [x] Excel export classes: `StudentAttendanceExport`, `TeacherAttendanceExport`, `ViolationExport`, `CharacterPointExport` (FromCollection, ShouldAutoSize, WithHeadings, WithMapping)
- [x] `ReportController` with 9 public methods: index, 3 PDF (student-attendance, teacher-attendance, violations), 3 Excel (student-attendance, teacher-attendance, violations) + characterPointsExcel + parentCallLetter, plus private `download()` helper
- [x] Inertia page `Reports/Index.vue`: filter bar (date range, class, status), 4 report cards (PDF/Excel buttons), parent call letter section
- [x] Routes: 9 routes under `auth,verified` group (`reports.*`)
- [x] Permission `reports.print` in `admin-sekolah`
- [x] Sidebar nav item Laporan → route live (`tenant.reports.*`)
- [x] Tests: 11 tests (page loads, 7 PDF/Excel downloads, unauth block, auth redirect)

#### Slice 8 — Guardian / Wali Murid Portal ✅

- [x] Migration: `guardian_student` pivot table (user_id, student_id, relationship)
- [x] Model relationships: `User.guardianStudents()`, `Student.guardianUsers()` via BelongsToMany
- [x] Services: `StudentReportData`, `ViolationPointService`, `CharacterPointService` (ported from pilot)
- [x] `GuardianDashboardController` — children via guardianStudents(), attendance summary month-to-date, latest records, fallback to guardian_name match
- [x] `GuardianStudentReportController` — validate linked child OR fallback, build report via StudentReportData
- [x] Vue pages: `Guardian/Dashboard.vue` (hero banner, KPI cards, attendance summary, tables), `Guardian/StudentShow.vue` (student profile, point cards, filterable reports)
- [x] Routes: 2 guardian routes in `auth` group (`tenant.guardian.*`)
- [x] Permissions: `guardians.view-dashboard`, `guardians.view-child-reports`
- [x] Role `wali-murid` created per school with guardian permissions
- [x] Seeds: wali@demo.test + wali@alfa.test users with linked students
- [x] Tests: 5 tests (dashboard loads, child report viewable, unlinked child 404, unauth forbidden, admin forbidden)

## 7. PL1 — Platform admin layer

### Goal

Bangun dashboard admin/founder untuk mengelola lifecycle pelanggan tanpa mengelola data operasional sekolah.

### Tasks

- [x] Tenant list/status/suspend/activate/reset admin password baseline
- [x] Tenant detail page: profil sekolah, subscription/trial, user tenant, usage summary non-sensitif
- [x] Platform dashboard: jumlah tenant trial/active/suspended, trial ending soon, recent signups, estimasi MRR seed/manual
- [x] Audit log untuk reset password, suspend/activate, billing status changes — ActivityLogger (+ BillingShow.vue audit gap fixed 2026-06-03)
- [-] Trial/read-only controls — **trial expiry middleware selesai** (expired → 403). Read-only mode (view tapi nggak bisa edit) sengaja didefer ke GT1/PRD karena perlu middleware di semua write route dan belum ada grace period post-trial. Lihat ADR PL1.
- [x] Billing ops manual — BillingController, rute, Index.vue, Show.vue (dibuat 2026-06-03)

### Acceptance

- Founder bisa tahu tenant mana yang aktif, trial hampir habis, suspended, atau belum selesai onboarding.
- Founder tidak bisa browse/edit data siswa/guru tenant di v1.

---

## 8. GT1 — Marketing + onboarding funnel

### Goal

Nyambungin marketing page, registrasi sekolah, onboarding, dan dashboard tenant supaya customer baru bisa go-live tanpa developer.

### Tasks

- [x] Marketing Astro baseline: home, harga, daftar CTA
- [x] CTA `/daftar` → backend register school (marketing `site.appUrl` → Laravel `/daftar` Inertia form)
- [x] Register school: PIC user, school name, slug/subdomain, email verify (RegisterSchoolAction + MustVerifyEmail enforced)
- [x] Onboarding wizard 5 langkah: profil sekolah → tahun ajaran/semester → kelas → import siswa → undang user (soft-gate checklist hub, deteksi progress dari data nyata)
- [x] Seed/default permission tenant baru setelah onboarding (RegisterSchoolAction sync admin-sekolah perms + SeedDefaultCatalogAction)
- [x] Redirect selesai onboarding ke `tenant.dashboard` (PostAuthRedirect helper)
- [x] Trial 14 hari + reminders (command `platform:trial-reminders` + TrialEndingReminder notif + expiry) + platform admin visibility (kolom onboarding/trial)
- [x] Pricing/trial copy di marketing sama dengan behavior backend (trial 14 hari, plan map standar)

### Acceptance

- [x] Customer dari landing bisa daftar, verifikasi, onboarding, lalu masuk dashboard sekolah dengan data awal (SchoolRegistrationTest 10 test)
- [x] Platform admin bisa lihat status onboarding/trial customer tersebut (Tenants Index kolom Onboarding, Show stat card)

---

## 9. PRD — Production platform

### Tasks

- [x] Desain landing → implementasi **Astro** (`marketing/`) — home, harga, daftar
- [ ] Deploy Astro (Nginx mini PC / Cloudflare — lihat ADR 0005)
- [x] CTA / form daftar → Laravel (GT1: CTA marketing → `/daftar` Inertia, Opsi B)
- [x] Register school → email verify → create school + slug (GT1; subdomain aktif saat PRD routing)
- [x] Onboarding wizard sebagai jembatan marketing → dashboard tenant (GT1: checklist hub soft-gate)
- [x] Trial **14 hari** + reminders command (GT1; email butuh mail server real di PRD)
- [ ] Billing integration draft (Midtrans/Xendit) — bisa flag manual dulu
- [ ] S3 storage prod; backup scheduled command

### Acceptance

- Sekolah baru dari nol bisa go-live tanpa developer — ✅ flow lengkap di kode (GT1); verifikasi end-to-end di staging
- Trial habis → read-only — ⚠️ saat ini trial habis → **403 (expired)**, bukan read-only; read-only mode didefer (lihat PL1 ADR)

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
