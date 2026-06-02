# Referensi — Repo pilot klien

**Path:** `C:\Projects\project-cahaya-sunnah`  
**Instansi:** Sekolah klien **Cahaya Sunnah** (bukan brand SaaS)  
**Status:** Single-tenant, fitur operasional lengkap, 107 tests (baseline 30 Mei 2026)

---

## Dokumentasi pilot

| File | Isi |
|------|-----|
| `docs/plans/01-project-scope.md` | Scope & acceptance criteria detail |
| `docs/plans/02-erd-database.md` | ERD tanpa `school_id` |
| `docs/plans/03-project-structure.md` | Struktur folder aktual |
| `docs/plans/04-development-plan.md` | Milestone M0–M10 |
| `docs/plans/05-backend-plan.md` | Backend detail |
| `docs/plans/06-frontend-plan.md` | Frontend detail |
| `docs/plans/07-deployment-plan.md` | Docker LAN PC sekolah |
| `docs/handover/admin-guide.md` | Panduan admin sekolah |
| `docs/handover/backup-restore-sop.md` | SOP backup |
| `README.md` | Fitur & alur QR |

---

## Peta port ke Platform Sekolah

| Area pilot | Lokasi utama | Aksi di `multi-school` |
|------------|--------------|------------------------|
| Models | `app/Models/` | + `BelongsToSchool`, `school_id` |
| Actions | `app/Actions/` | Copy + namespace |
| Services | `app/Services/` | Copy + tenant scope |
| Controllers | `app/Http/Controllers/` | Copy + middleware |
| Policies | `app/Policies/` | + school_id check |
| Migrations | `database/migrations/` | New migrations with `school_id` |
| Vue Pages | `resources/js/Pages/` | Copy + shared props `school` |
| Tests | `tests/Feature/` | Adapt + `TenantIsolation` |

---

## Perbedaan wajib SaaS vs pilot

| Aspek | Pilot | Platform Sekolah |
|-------|-------|------------------|
| Tenancy | Tidak ada | `school_id` everywhere |
| `users.email` | Unique | Unique **global** |
| Roles | Global Spatie | **Teams** per school |
| Settings | `school_settings` 1 row | Fields di `schools` |
| URL | `http://IP:8000` | `{slug}.platformsekolah.id` |
| Super-admin | Tidak ada | Platform dashboard |

---

## Tenant pertama (opsional)

Saat migrasi client:

- `schools.name` = nama resmi sekolah client  
- `schools.slug` = `cahaya-sunnah` (contoh)  
- Import dump pilot → backfill `school_id`

PC LAN client boleh **tetap** jalan single-tenant sampai cutover disepakati.

---

## Perintah verifikasi pilot

```powershell
cd C:\Projects\project-cahaya-sunnah
php artisan test
npm run build
```
