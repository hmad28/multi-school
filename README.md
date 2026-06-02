# Platform Sekolah

**Platform Sekolah** adalah aplikasi manajemen sekolah berbasis SaaS multi-tenant. Melayani operasional harian sekolah — absensi, pelanggaran, poin karakter, kalender akademik, dan laporan — dalam satu platform cloud.

Dibangun dengan Laravel 13 + Inertia 3 + Vue 3 + TypeScript. Modul operasional sekolah di-port dari sistem pilot klien dan disesuaikan untuk isolasi multi-tenant.

---

## Fitur

| Modul | Status |
|-------|--------|
| Manajemen Tenant — dashboard super-admin, daftar sekolah, suspend/aktifkan, reset admin | ✅ |
| Master Data — siswa, guru, kelas, tahun ajaran, semester | ✅ |
| Absensi Siswa — input harian per kelas, koreksi, rekap, finalize | ✅ |
| Absensi QR — scan QR via kamera, session 10 menit, status terlambat otomatis | ✅ |
| Absensi Guru — input harian, koreksi, rekap | ✅ |
| Kalender Akademik — hari libur, periode akademik | ✅ |
| Pelanggaran Siswa — tipe pelanggaran, input, validasi/tolak, ambang peringatan | ✅ |
| Poin Karakter — tipe poin, input, total per semester | ✅ |
| Laporan PDF/Excel | ⏳ |
| Portal Wali Murid | ⏳ |
| Notifikasi & WhatsApp | ⏳ |

---

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4, MySQL 8, Redis
- **Frontend:** Inertia 3, Vue 3, TypeScript, Tailwind CSS, Vite
- **Auth:** Laravel Breeze (sanctum), Spatie Permission (teams)
- **Infra:** Docker Compose, Cloudflare Tunnel, GitHub Actions
- **Marketing:** Astro (`marketing/`)

## Arsitektur

Multi-tenant **shared database** dengan `school_id`. Setiap request tenant melewati middleware `SetCurrentSchool` yang meresolve slug dan menyetel `TenantContext`. Model tenant memakai trait `BelongsToSchool` (global scope).

Role & permission menggunakan Spatie teams dengan `school_id` sebagai team key.

```
URL dev:
  /t/{slug}/dashboard   → tenant app
  /platform/dashboard   → platform admin
  /                     → landing

URL production (rencana):
  {slug}.platformsekolah.id → tenant app
```

---

## Quick Start

```bash
composer install
npm install
cp .env.example .env   # isi DB credentials
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8888
npm run dev
```

### Akun Dev

| Role | Email | Password | URL Login |
|------|-------|----------|-----------|
| Super-admin | `super@platformsekolah.test` | `password` | `/platform/login` |
| Admin demo | `admin@demo.test` | `password` | `/t/demo/login` |
| Admin alfa | `admin@alfa.test` | `password` | `/t/alfa/login` |

Hosts file:
```text
127.0.0.1 platformsekolah.test admin.platformsekolah.test demo.platformsekolah.test
```

### Test

```bash
php artisan test
# 70+ tests — tenant isolation, CRUD, cross-tenant 403
```

---

## Struktur

```
app/Actions/           → action classes (single responsibility)
app/Http/Controllers/
  Tenant/              → controllers tenant app
  Platform/            → controllers platform admin
app/Models/Concerns/
  BelongsToSchool.php  → trait isolasi tenant
resources/js/Pages/    → Inertia pages per modul
routes/
  tenant.php           → route tenant app
  platform.php         → route platform admin
  web.php              → route publik/landing
docs/                  → dokumentasi teknis
```

Dokumentasi lengkap: [`docs/README.md`](docs/README.md)
