# Backend Plan — Platform Sekolah

> **Goal:** Implementasi backend Laravel multi-tenant aman, port pola dari pilot, lapisan platform terpisah.

**Stack:** Laravel 13, PHP 8.4, MySQL 8, Spatie Permission (teams), Horizon (prod).

---

## 1. Aturan backend (semua fase)

1. Write route → Form Request + authorize.
2. Domain write → activity log (`school_id` + user).
3. Multi-row → `DB::transaction()`.
4. Laporan → eager load, no N+1.
5. Tidak ada raw SQL/shell dari input user.
6. Upload → MIME sniff, bukan ekstensi saja.
7. Cross-tenant access → **403** + test.

---

## 2. Packages

```bash
composer require spatie/laravel-permission maatwebsite/excel barryvdh/laravel-dompdf
# prod: laravel/horizon (queue)
```

Publish Spatie dengan **teams**:

```php
// config/permission.php
'teams' => true,
'team_foreign_key' => 'school_id',
```

---

## 3. Tenant resolution

### 3.1 `TenantResolver`

```php
// app/Services/TenantResolver.php
public function resolve(Request $request): ?School
{
    $host = $request->getHost();
    $base = config('platform.tenant_domain'); // platformsekolah.id

    if (! str_ends_with($host, $base)) {
        return null; // platform root / admin host
    }

    $slug = str_replace('.'.$base, '', $host);
    if ($slug === '' || $slug === 'www' || $slug === 'admin') {
        return null;
    }

    return School::where('slug', $slug)->whereIn('status', ['trial', 'active'])->first();
}
```

### 3.2 `TenantContext`

```php
// app/Support/TenantContext.php — bind di container per request
public static function set(School $school): void
public static function id(): ?string
public static function school(): ?School
public static function clear(): void
```

### 3.3 Middleware `SetCurrentSchool`

- Jalankan setelah `auth` pada route tenant.
- Jika school tidak ditemukan → 404 branded.
- Jika `suspended` → 403 halaman khusus.
- Jika trial expired (`trial_ends_at` lewat) → 403 dengan pesan "Masa trial telah berakhir."
- Set `TenantContext::set($school)`.
- Set Spatie team id: `setPermissionsTeamId($school->id)`.

### 3.4 Host admin platform

- `admin.platformsekolah.id` → route group platform, **tanpa** `SetCurrentSchool`.
- User harus role `super-admin`.

---

## 4. Model `BelongsToSchool`

```php
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if ($id = TenantContext::id()) {
                $builder->where($builder->getModel()->getTable().'.school_id', $id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->school_id) && TenantContext::id()) {
                $model->school_id = TenantContext::id();
            }
        });
    }
}
```

**Super-admin** mengakses data tenant:

```php
TenantContext::set($school);
// ... query with scope OR withoutGlobalScope + explicit where school_id
ActivityLog::logSuperAdminAction(...);

## 4.1 Activity Log

Activity logging for platform actions (status changes, password resets):

```php
// app/Models/ActivityLog.php — UUID model
// app/Support/ActivityLogger.php — static helper

ActivityLogger::logStatusChange($school, $from, $to, $performedBy);
ActivityLogger::logPasswordReset($admin, $performedBy);
ActivityLogger::log('tenant.suspended', '...', $user, $school, ['from' => 'trial', 'to' => 'suspended']);
```

- Logs stored in `activity_logs` table with `user_id`, `school_id`, `action`, `description`, `metadata` (JSON).
- Displayed on tenant detail page (last 20 entries).
- No event/listener overhead for v1 — direct static calls in controller.

---

## 5. Auth & users

### 5.1 Registrasi sekolah & onboarding (GT1 — ✅ implemented)

`App\Actions\Registration\RegisterSchoolAction`:

1. Validasi email belum ada (`users.email` unique) via `RegisterSchoolRequest`.
2. Transaction: create `schools` + slug unique + trial `subscriptions` + `users` admin + assign `admin-sekolah` with team perms + `SeedDefaultCatalogAction` (katalog default per-tenant).
3. `SchoolRegistrationController@store` fires `Registered` → kirim email verifikasi, login, redirect ke `verification.notice`.
4. Status school = `trial`, `trial_ends_at` = now + `config('platform.trial_days')` (default 14 hari), `onboarding_step` = 0.

Routes di domain central (`routes/web.php`): `GET/POST /daftar` (guest). CTA marketing → `site.appUrl + /daftar`.

**Verifikasi email (enforced):** `User implements MustVerifyEmail`. `App\Support\PostAuthRedirect::for()` mengarahkan: super-admin → platform, admin sekolah onboarding belum selesai → `tenant.onboarding.show`, selain itu → `tenant.dashboard`. Dipakai `VerifyEmailController` + `EmailVerificationPromptController`.

**Onboarding wizard (soft-gate):** `Tenant\OnboardingController` — checklist hub deteksi progres dari data nyata (profil, tahun ajaran aktif, kelas, siswa, undang user). Aksi inline `updateProfile` + `invite` (mengisi P2 follow-up school profile & user management). `finish` stamp `onboarding_completed_at`. Routes `tenant.onboarding.*` di grup `auth,verified`.

**Trial reminders:** command `platform:trial-reminders {--days=3}` (`App\Console\Commands\SendTrialReminders`) kirim `TrialEndingReminder` ke admin + expire trial lewat tanggal → status `expired`. Dijadwalkan harian 07:00 di `routes/console.php`. `SchoolStatus::Expired` di-handle `TenantResolver` + `SetCurrentSchool` (403 pesan trial berakhir).

### 5.2 Login

- Login form di subdomain tenant → user harus `users.school_id` = tenant yang sama.
- Login di admin host → hanya `super-admin`.

### 5.3 Email

- **Unique global** pada `users.email`.
- Pesan error: *"Email sudah terdaftar."*

---

## 6. Platform controllers (v1)

| Controller | Method | Aksi |
|------------|--------|------|
| `Platform\TenantController` | index | List schools + filter |
| | show | Detail tenant: profil, subscription, users, activity log |
| | updateStatus | suspend/activate + audit log via ActivityLogger |
| | resetPassword | Reset admin sekolah + audit log via ActivityLogger |
| `Platform\BillingController` | index | List subscriptions + filter by status/plan |
| | show | Detail subscription |
| | updateStatus | Mark subscription active/past_due/canceled |

Policies: hanya `super-admin`.

---

## 7. Port modul operasional

Urutan port (lihat [04-development-plan.md](./04-development-plan.md)):

| Modul | Pilot path | Perubahan SaaS |
|-------|------------|----------------|
| Students | `Admin\StudentController` | + BelongsToSchool, compound unique |
| Attendance | `Attendance\*` | + school_id on submissions |
| QR | `QrAttendanceService` | scope token by school |
| Violations | `Violations\*` | idem |
| Reports | `Reports\*` | kop from `schools` |
| Guardian | `Guardian\*` | scope pivot |

Copy file → sesuaikan namespace → tambah trait → fix tests.

---

## 8. Actions & Services (pilot)

Port daftar dari pilot:

```text
app/Actions/Attendance/*
app/Actions/Violations/*
app/Services/QrAttendanceService.php
app/Services/ViolationSummaryService.php
app/Services/ActivityLogService.php
...
```

Tambah (✅ implemented kecuali ditandai):

```text
app/Actions/Registration/RegisterSchoolAction.php   ← GT1 (bukan Actions/Platform)
app/Actions/Catalog/SeedDefaultCatalogAction.php    ← katalog default per-tenant
app/Console/Commands/SendTrialReminders.php         ← GT1 trial reminder + expiry
app/Notifications/TrialEndingReminder.php           ← GT1
app/Support/PostAuthRedirect.php                    ← GT1 routing pasca-verify
app/Services/TenantResolver.php
app/Actions/Platform/SuspendSchoolAction.php        ← belum (suspend via TenantController inline)
```

---

## 9. Policies

- Semua policy tenant: cek permission **dan** `model->school_id === TenantContext::id()`.
- `StudentPolicy::view` — guru kelas: kelas sendiri; wali murid: guardian pivot.

---

## 10. Queue & jobs (P6+)

```text
SendTrialExpiryReminderJob
ProcessSubscriptionPaymentWebhookJob
TenantDatabaseBackupJob
```

Queue connection: `redis` + Horizon di production.

---

## 11. Storage

```php
// disk s3
$path = "schools/{$schoolId}/students/photos/{$filename}";
```

Local dev: `storage/app/public` dengan prefix yang sama.

---

## 12. Testing checklist

| Test | Assert |
|------|--------|
| `TenantIsolationTest` | User tenant A → GET student B = 403/404 |
| `SchoolRegistrationTest` ✅ | Register → school+admin+role+catalog+subscription; slug collision → `-2`; duplicate email ditolak; unverified admin → redirect verify; verified+onboarding → onboarding; finish stamp; profil/invite; trial reminder notif + expiry (10 test) |
| `SuspendedSchoolTest` | Login blocked |
| Port tests | Run pilot feature tests adapted |

> **Catatan implementasi GT1:** `User` `#[Fillable]` semula tak menyertakan `email_verified_at`, sehingga seeder/test yang set field itu diam-diam di-drop. Tak berdampak sampai `MustVerifyEmail` di-enforce (GT1) → semua user seed jadi unverified. Diperbaiki: `email_verified_at` masuk fillable (registrasi tak pernah ambil dari input user, jadi aman).

---

## 13. Enums (port)

```text
app/Enums/Gender.php
app/Enums/AttendanceStatusSlug.php
app/Enums/ViolationStatus.php
app/Enums/SchoolStatus.php          ← BARU: trial, active, suspended
```

---

## 14. Referensi

- [02-erd-database.md](./02-erd-database.md)
- Pilot: `project-cahaya-sunnah/docs/plans/05-backend-plan.md`
