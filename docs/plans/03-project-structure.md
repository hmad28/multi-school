# Project Structure — Platform Sekolah

> **Goal:** Struktur repo `multi-school` yang konsisten dengan pilot, plus lapisan platform & tenant.

**Stack:** Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind, MySQL, Docker (dev), S3 (prod).

---

## 1. Repository root (target)

```text
multi-school/
├── marketing/                ← Astro SSG (platformsekolah.id) — lihat 08-marketing
├── app/
├── bootstrap/
├── config/
│   ├── platform.php          ← app-specific (ganti/alias cahaya.php dari pilot)
│   └── permission.php        ← teams enabled
├── database/
│   ├── migrations/
│   └── seeders/
├── docker/
├── docs/                     ← dokumen ini
├── public/
├── resources/js/
├── routes/
│   ├── web.php               ← tenant app
│   ├── platform.php          ← super-admin (opsional split)
│   └── auth.php
├── tests/
│   ├── Feature/
│   │   ├── Tenant/
│   │   └── Platform/
│   └── Unit/
├── .env.example
├── docker-compose.yml
├── PRD-cahaya-sunnah-school-system.md
└── vite.config.ts
```

**Tidak** copy `vendor/` / `node_modules` dari pilot — bootstrap fresh Laravel lalu port file.

---

## 2. Dokumentasi

```text
docs/
├── README.md
├── plans/          01–07
├── decisions/      ADR
└── reference/      pilot-repo.md
```

Handover sekolah (admin guide, backup SOP) tetap di **pilot** sampai SaaS go-live; duplikasi nanti di `docs/handover/`.

---

## 3. Backend `app/`

```text
app/
├── Actions/
│   ├── Attendance/           ← port dari pilot
│   ├── Violations/
│   ├── CharacterPoints/
│   ├── Backup/
│   ├── Imports/
│   ├── Reports/
│   └── Platform/             ← BARU: RegisterSchool, SuspendTenant, ...
├── Enums/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Attendance/
│   │   ├── Platform/         ← SuperAdmin: TenantController, BillingController
│   │   ├── Onboarding/     ← wizard steps
│   │   └── ...
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php
│   │   ├── SetCurrentSchool.php      ← BARU
│   │   └── EnsurePlatformAdmin.php   ← BARU
│   └── Requests/
├── Models/
│   ├── School.php            ← BARU tenant master
│   ├── Concerns/
│   │   └── BelongsToSchool.php
│   └── ...                   ← port + trait
├── Policies/
├── Services/
│   ├── TenantResolver.php    ← BARU: Host → School
│   ├── TenantContext.php     ← BARU: facade/container
│   └── ...                   ← port
└── Support/
    ├── TenantContext.php     ← BARU: facade/container
    └── ActivityLogger.php   ← BARU: static audit logging
```

### 3.1 Aturan layer

| Layer | Tanggung jawab |
|-------|----------------|
| Controller | Authorize, delegate, return Inertia/redirect |
| Form Request | Validasi + authorize |
| Action | Satu workflow bisnis (`handle()`) |
| Service | Logic reusable, query aggregate |
| Model | Persistence + scope |

---

## 4. Frontend `resources/js/`

```text
resources/js/
├── Pages/
│   ├── Platform/             ← Tenants/Index, Show, ...
│   ├── Onboarding/           ← Step1..Step5
│   ├── Dashboard/
│   ├── AcademicCalendar/
│   │   └── Holidays/         ← Index, Create, Edit — ✅ (T1 Slice 1)
│   ├── Students/
│   └── ...                   ← port dari pilot
├── Layouts/
│   ├── AuthenticatedLayout.vue
│   ├── PlatformLayout.vue    ← BARU: sidebar super-admin
│   └── GuestLayout.vue
├── Components/
├── lib/
│   ├── permissions.ts
│   ├── routes.ts
│   └── tenant.ts             ← slug, school name dari shared props
└── types/
    ├── school.ts
    ├── students.ts
    └── platform.ts
```

**Shared Inertia props (tenant):**

```php
'school' => fn () => TenantContext::school()?->only('id', 'name', 'slug', 'logo_url'),
'auth' => ...,
'permissions' => ...,
```

---

## 5. Routes

### 5.1 Tenant (subdomain)

```php
// routes/web.php — middleware: web, auth, SetCurrentSchool
Route::domain('{tenant}.platformsekolah.id')->group(...);
// local dev: APP_TENANT_DOMAIN=platformsekolah.test
```

### 5.2 Platform

```php
// admin.platformsekolah.id atau Route::prefix('admin')->domain(...)
Route::middleware(['auth', 'role:super-admin'])->group(...);
```

### 5.3 Marketing / registrasi

```php
// platformsekolah.id
Route::get('/', Landing);
Route::post('/register', RegisterSchool);
```

---

## 6. Config `config/platform.php`

```php
// Snippet selaras dengan config/platform.php aktual
return [
    'name' => env('PLATFORM_NAME', 'Platform Sekolah'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'trial_days' => (int) env('PLATFORM_TRIAL_DAYS', 14),
    'tenant_base_domain' => env('TENANT_BASE_DOMAIN', 'platformsekolah.test'),
    'admin_domain' => env('ADMIN_DOMAIN', 'admin.platformsekolah.test'),
    'central_domains' => [...],            // env CENTRAL_DOMAINS, comma-separated
    'marketing_url' => env('MARKETING_URL', 'http://127.0.0.1:4321'),
    'use_path_routing' => env('PLATFORM_PATH_ROUTING', true),       // dev default
    'use_subdomain_routing' => env('PLATFORM_SUBDOMAIN_ROUTING', false), // prod
];
```

---

## 7. Testing

```text
tests/Feature/TenantIsolation/
tests/Feature/Platform/SuperAdminTenantTest.php
tests/Feature/Onboarding/RegisterSchoolTest.php
```

Port test pilot bertahap; setiap modul port wajib tambah assert `school_id`.

---

## 8. Naming conventions

| Item | Konvensi |
|------|----------|
| Model kelas | `SchoolClass` (bukan `Class`) |
| Controller platform | `Platform\TenantController` |
| Action | `RegisterSchoolAction`, `SubmitStudentAttendanceAction` |
| Migration | `2026_06_01_000001_create_schools_table` |
| Vue page | `Pages/Students/Index.vue` |

---

## 9. Referensi pilot

Struktur aktual: `project-cahaya-sunnah/docs/plans/03-project-structure.md`  
Peta port: [../reference/pilot-repo.md](../reference/pilot-repo.md)
