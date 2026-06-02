# T1 Tenant App Port Slice 1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Start T1 by porting the first safe tenant app module from `C:\Projects\project-cahaya-sunnah` into `C:\Projects\multi-school`: academic calendar holidays, adapted to SaaS tenant routes and `school_id` isolation.

**Architecture:** Port one pilot module end-to-end before bigger attendance/QR modules. Add a tenant-scoped `AcademicCalendarHoliday` model using `BelongsToSchool`, routes under `tenant.academic-calendar.holidays.*`, permission `academic-calendar.manage`, Inertia pages under `resources/js/Pages/AcademicCalendar/Holidays`, and docs that make T1 the active next milestone. Reuse current tenant infrastructure: `TenantContext`, `BelongsToSchool`, `AuthenticatedLayout`, `app-card`, `app-table`, and route pattern with leading `{tenant}` argument in controller resource methods.

**Tech Stack:** Laravel 13, Inertia 3, Vue 3, TypeScript, Tailwind CSS, Spatie Permission teams, PHPUnit.

---

## File Structure

**Create:**
- `database/migrations/2026_06_02_100000_create_academic_calendar_holidays_table.php` — tenant-scoped holiday table.
- `app/Models/AcademicCalendarHoliday.php` — tenant-scoped holiday model.
- `app/Http/Requests/AcademicCalendar/StoreAcademicCalendarHolidayRequest.php` — tenant-scoped validation for create.
- `app/Http/Requests/AcademicCalendar/UpdateAcademicCalendarHolidayRequest.php` — tenant-scoped validation for update.
- `app/Http/Controllers/Tenant/AcademicCalendarHolidayController.php` — tenant route CRUD controller.
- `resources/js/Pages/AcademicCalendar/Holidays/Index.vue` — month calendar/list page.
- `resources/js/Pages/AcademicCalendar/Holidays/Create.vue` — create page.
- `resources/js/Pages/AcademicCalendar/Holidays/Edit.vue` — edit page.
- `tests/Feature/Tenant/AcademicCalendarHolidayTest.php` — tenant permission and isolation tests.

**Modify:**
- `routes/tenant.php` — add tenant routes for academic calendar holidays.
- `database/seeders/RoleSeeder.php` — add `academic-calendar.manage` to tenant admin role.
- `database/seeders/PlatformSeeder.php` — seed sample holidays per demo/alfa school.
- `resources/js/Layouts/AuthenticatedLayout.vue` — turn “Kalender Akademik” nav into real route.
- `docs/plans/04-development-plan.md` — mark T1 as active/in progress and Slice 1 scope.
- `docs/plans/06-frontend-plan.md` — mark academic calendar as first T1 ported module and route.

**Reuse from current app:**
- `App\Support\TenantContext` for tenant id.
- `App\Models\Concerns\BelongsToSchool` for global tenant scope and auto `school_id` on create.
- Existing P2 controller signatures in `Tenant\StudentController`, `Tenant\TeacherController`, `Tenant\SchoolClassController`: resource methods must accept `string $tenant` before route model objects.
- Existing UI classes in `resources/css/app.css`.

**Reuse from pilot:**
- `C:\Projects\project-cahaya-sunnah\app\Http\Controllers\Admin\AcademicCalendarHolidayController.php`
- `C:\Projects\project-cahaya-sunnah\app\Models\AcademicCalendarHoliday.php`
- `C:\Projects\project-cahaya-sunnah\app\Http\Requests\AcademicCalendar\StoreAcademicCalendarHolidayRequest.php`
- `C:\Projects\project-cahaya-sunnah\app\Http\Requests\AcademicCalendar\UpdateAcademicCalendarHolidayRequest.php`
- `C:\Projects\project-cahaya-sunnah\tests\Feature\AcademicCalendar\AcademicCalendarHolidayTest.php`
- `C:\Projects\project-cahaya-sunnah\resources\js\Pages\AcademicCalendar\Holidays\*.vue`

---

### Task 1: Add tenant-scoped academic calendar schema and model

**Files:**
- Create: `database/migrations/2026_06_02_100000_create_academic_calendar_holidays_table.php`
- Create: `app/Models/AcademicCalendarHoliday.php`
- Test: `tests/Feature/Tenant/AcademicCalendarHolidayTest.php`

- [ ] **Step 1: Write tenant isolation model test**

Create `tests/Feature/Tenant/AcademicCalendarHolidayTest.php` with this initial test:

```php
<?php

namespace Tests\Feature\Tenant;

use App\Models\AcademicCalendarHoliday;
use App\Models\School;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicCalendarHolidayTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_calendar_holidays_are_scoped_to_current_tenant(): void
    {
        $demo = School::factory()->create(['slug' => 'demo']);
        $alfa = School::factory()->create(['slug' => 'alfa']);

        TenantContext::set($demo);
        AcademicCalendarHoliday::query()->create([
            'date' => '2026-06-01',
            'name' => 'Libur Demo',
            'description' => 'Rapat guru demo',
            'status' => 'active',
        ]);

        TenantContext::set($alfa);
        AcademicCalendarHoliday::query()->create([
            'date' => '2026-06-01',
            'name' => 'Libur Alfa',
            'description' => 'Rapat guru alfa',
            'status' => 'active',
        ]);

        $this->assertSame(['Libur Alfa'], AcademicCalendarHoliday::query()->pluck('name')->all());

        TenantContext::set($demo);
        $this->assertSame(['Libur Demo'], AcademicCalendarHoliday::query()->pluck('name')->all());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run:

```powershell
php artisan test --filter=AcademicCalendarHolidayTest
```

Expected: fail because `AcademicCalendarHoliday` model/table do not exist.

- [ ] **Step 3: Create migration**

Create `database/migrations/2026_06_02_100000_create_academic_calendar_holidays_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_calendar_holidays', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'date']);
            $table->index(['school_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_calendar_holidays');
    }
};
```

- [ ] **Step 4: Create model**

Create `app/Models/AcademicCalendarHoliday.php`:

```php
<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicCalendarHoliday extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'date',
        'name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run:

```powershell
php artisan test --filter=AcademicCalendarHolidayTest
```

Expected: pass.

---

### Task 2: Add tenant routes, validation, controller, and permissions

**Files:**
- Create: `app/Http/Requests/AcademicCalendar/StoreAcademicCalendarHolidayRequest.php`
- Create: `app/Http/Requests/AcademicCalendar/UpdateAcademicCalendarHolidayRequest.php`
- Create: `app/Http/Controllers/Tenant/AcademicCalendarHolidayController.php`
- Modify: `routes/tenant.php`
- Modify: `database/seeders/RoleSeeder.php`
- Test: `tests/Feature/Tenant/AcademicCalendarHolidayTest.php`

- [ ] **Step 1: Extend feature test for CRUD and permissions**

Append these tests inside `AcademicCalendarHolidayTest`:

```php
public function test_tenant_admin_can_manage_academic_calendar_holidays(): void
{
    $this->seed(\Database\Seeders\PlatformSeeder::class);

    $school = School::query()->where('slug', 'demo')->firstOrFail();
    $admin = \App\Models\User::query()->where('email', 'admin@demo.test')->firstOrFail();

    $this->actingAs($admin)->get(route('tenant.academic-calendar.holidays.index', ['tenant' => $school->slug]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('AcademicCalendar/Holidays/Index'));

    $this->actingAs($admin)->post(route('tenant.academic-calendar.holidays.store', ['tenant' => $school->slug]), [
        'date' => '2026-06-01',
        'name' => 'Libur sekolah',
        'description' => 'Rapat guru',
        'status' => 'active',
    ])->assertRedirect(route('tenant.academic-calendar.holidays.index', ['tenant' => $school->slug]));

    $holiday = AcademicCalendarHoliday::query()->where('name', 'Libur sekolah')->firstOrFail();

    $this->actingAs($admin)->put(route('tenant.academic-calendar.holidays.update', ['tenant' => $school->slug, 'holiday' => $holiday->id]), [
        'date' => '2026-06-02',
        'name' => 'Libur nasional',
        'description' => null,
        'status' => 'inactive',
    ])->assertRedirect(route('tenant.academic-calendar.holidays.index', ['tenant' => $school->slug]));

    $this->assertDatabaseHas('academic_calendar_holidays', [
        'id' => $holiday->id,
        'school_id' => $school->id,
        'name' => 'Libur nasional',
        'status' => 'inactive',
    ]);
}

public function test_same_holiday_date_is_allowed_across_different_tenants(): void
{
    $this->seed(\Database\Seeders\PlatformSeeder::class);

    $demo = School::query()->where('slug', 'demo')->firstOrFail();
    $alfa = School::query()->where('slug', 'alfa')->firstOrFail();
    $demoAdmin = \App\Models\User::query()->where('email', 'admin@demo.test')->firstOrFail();
    $alfaAdmin = \App\Models\User::query()->where('email', 'admin@alfa.test')->firstOrFail();

    $this->actingAs($demoAdmin)->post(route('tenant.academic-calendar.holidays.store', ['tenant' => $demo->slug]), [
        'date' => '2026-06-01',
        'name' => 'Libur demo',
        'description' => null,
        'status' => 'active',
    ])->assertRedirect();

    $this->actingAs($alfaAdmin)->post(route('tenant.academic-calendar.holidays.store', ['tenant' => $alfa->slug]), [
        'date' => '2026-06-01',
        'name' => 'Libur alfa',
        'description' => null,
        'status' => 'active',
    ])->assertRedirect();

    $this->assertDatabaseHas('academic_calendar_holidays', [
        'school_id' => $demo->id,
        'date' => '2026-06-01 00:00:00',
        'name' => 'Libur demo',
    ]);
    $this->assertDatabaseHas('academic_calendar_holidays', [
        'school_id' => $alfa->id,
        'date' => '2026-06-01 00:00:00',
        'name' => 'Libur alfa',
    ]);
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run:

```powershell
php artisan test --filter=AcademicCalendarHolidayTest
```

Expected: fail because routes/controller/requests/permission do not exist.

- [ ] **Step 3: Create store request**

Create `app/Http/Requests/AcademicCalendar/StoreAcademicCalendarHolidayRequest.php`:

```php
<?php

namespace App\Http\Requests\AcademicCalendar;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicCalendarHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic-calendar.manage');
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                Rule::unique('academic_calendar_holidays', 'date')
                    ->where('school_id', TenantContext::id())
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
```

- [ ] **Step 4: Create update request**

Create `app/Http/Requests/AcademicCalendar/UpdateAcademicCalendarHolidayRequest.php`:

```php
<?php

namespace App\Http\Requests\AcademicCalendar;

use App\Support\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicCalendarHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('academic-calendar.manage');
    }

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                Rule::unique('academic_calendar_holidays', 'date')
                    ->ignore($this->route('holiday'))
                    ->where('school_id', TenantContext::id())
                    ->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
```

- [ ] **Step 5: Create controller**

Create `app/Http/Controllers/Tenant/AcademicCalendarHolidayController.php`:

```php
<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicCalendar\StoreAcademicCalendarHolidayRequest;
use App\Http\Requests\AcademicCalendar\UpdateAcademicCalendarHolidayRequest;
use App\Models\AcademicCalendarHoliday;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicCalendarHolidayController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('academic-calendar.manage'), 403);

        $month = $request->string('month')->toString();
        $startsAt = filled($month)
            ? CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->toImmutable()->startOfMonth();
        $calendarStart = $startsAt->startOfWeek();
        $calendarEnd = $startsAt->endOfMonth()->endOfWeek();

        return Inertia::render('AcademicCalendar/Holidays/Index', [
            'holidays' => AcademicCalendarHoliday::query()
                ->whereBetween('date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
                ->orderBy('date')
                ->get()
                ->map(fn (AcademicCalendarHoliday $holiday): array => [
                    'id' => $holiday->id,
                    'date' => $holiday->date?->toDateString(),
                    'name' => $holiday->name,
                    'description' => $holiday->description,
                    'status' => $holiday->status,
                ]),
            'month' => $startsAt->format('Y-m'),
            'monthLabel' => $startsAt->translatedFormat('F Y'),
            'previousMonth' => $startsAt->subMonth()->format('Y-m'),
            'nextMonth' => $startsAt->addMonth()->format('Y-m'),
            'calendarStart' => $calendarStart->toDateString(),
            'calendarEnd' => $calendarEnd->toDateString(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AcademicCalendarHoliday::class);

        return Inertia::render('AcademicCalendar/Holidays/Create');
    }

    public function store(StoreAcademicCalendarHolidayRequest $request): RedirectResponse
    {
        AcademicCalendarHoliday::query()->create($request->validated());

        return redirect()->route('tenant.academic-calendar.holidays.index', [
            'tenant' => $request->route('tenant'),
        ])->with('success', 'Hari libur berhasil dibuat.');
    }

    public function edit(string $tenant, AcademicCalendarHoliday $holiday): Response
    {
        $this->authorize('update', $holiday);

        return Inertia::render('AcademicCalendar/Holidays/Edit', [
            'holiday' => [
                'id' => $holiday->id,
                'date' => $holiday->date?->toDateString(),
                'name' => $holiday->name,
                'description' => $holiday->description,
                'status' => $holiday->status,
            ],
        ]);
    }

    public function update(UpdateAcademicCalendarHolidayRequest $request, string $tenant, AcademicCalendarHoliday $holiday): RedirectResponse
    {
        $holiday->update($request->validated());

        return redirect()->route('tenant.academic-calendar.holidays.index', [
            'tenant' => $tenant,
        ])->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Request $request, string $tenant, AcademicCalendarHoliday $holiday): RedirectResponse
    {
        $this->authorize('delete', $holiday);
        $holiday->delete();

        return redirect()->route('tenant.academic-calendar.holidays.index', [
            'tenant' => $tenant,
        ])->with('success', 'Hari libur berhasil dihapus.');
    }
}
```

- [ ] **Step 6: Register simple authorization policy behavior**

If no dedicated policy exists, add `AcademicCalendarHoliday` checks to `app/Providers/AuthServiceProvider.php` using a policy class or add a new `app/Policies/AcademicCalendarHolidayPolicy.php` with this content:

```php
<?php

namespace App\Policies;

use App\Models\AcademicCalendarHoliday;
use App\Models\User;

class AcademicCalendarHolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('academic-calendar.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('academic-calendar.manage');
    }

    public function update(User $user, AcademicCalendarHoliday $holiday): bool
    {
        return $user->can('academic-calendar.manage') && $user->school_id === $holiday->school_id;
    }

    public function delete(User $user, AcademicCalendarHoliday $holiday): bool
    {
        return $user->can('academic-calendar.manage') && $user->school_id === $holiday->school_id;
    }
}
```

Then register it in `AuthServiceProvider`:

```php
AcademicCalendarHoliday::class => AcademicCalendarHolidayPolicy::class,
```

- [ ] **Step 7: Add route**

Modify `routes/tenant.php` imports:

```php
use App\Http\Controllers\Tenant\AcademicCalendarHolidayController;
```

Inside the existing `auth, verified` group, after `academic` setup routes, add:

```php
Route::resource('academic-calendar/holidays', AcademicCalendarHolidayController::class)
    ->parameters(['holidays' => 'holiday'])
    ->names('academic-calendar.holidays')
    ->except(['show']);
```

- [ ] **Step 8: Add permission**

Modify `database/seeders/RoleSeeder.php` permissions array to include:

```php
'academic-calendar.manage',
```

- [ ] **Step 9: Run tests**

Run:

```powershell
php artisan test --filter=AcademicCalendarHolidayTest
```

Expected: pass.

---

### Task 3: Port Inertia pages and sidebar navigation

**Files:**
- Create: `resources/js/Pages/AcademicCalendar/Holidays/Index.vue`
- Create: `resources/js/Pages/AcademicCalendar/Holidays/Create.vue`
- Create: `resources/js/Pages/AcademicCalendar/Holidays/Edit.vue`
- Modify: `resources/js/Layouts/AuthenticatedLayout.vue`

- [ ] **Step 1: Create Index page**

Create `resources/js/Pages/AcademicCalendar/Holidays/Index.vue` adapted from pilot, using current UI classes and tenant routes. It must accept props:

```ts
type Holiday = {
    id: string;
    date: string;
    name: string;
    description: string | null;
    status: string;
};

defineProps<{
    holidays: Holiday[];
    month: string;
    monthLabel: string;
    previousMonth: string;
    nextMonth: string;
    calendarStart: string;
    calendarEnd: string;
}>();
```

Use `const school = computed(() => usePage().props.school as { slug: string });` and route helpers:

```ts
const tenantParams = (params: Record<string, string> = {}) => ({ tenant: school.value.slug, ...params });
```

Links must use:
- `route('tenant.academic-calendar.holidays.create', tenantParams())`
- `route('tenant.academic-calendar.holidays.index', tenantParams({ month: previousMonth }))`
- `route('tenant.academic-calendar.holidays.edit', tenantParams({ holiday: holiday.id }))`

Delete must use `router.delete(route('tenant.academic-calendar.holidays.destroy', tenantParams({ holiday: holiday.id })), { preserveScroll: true })` after confirm.

- [ ] **Step 2: Create Create page**

Create `resources/js/Pages/AcademicCalendar/Holidays/Create.vue` with `useForm({ date: '', name: '', description: '', status: 'active' })`. Submit to:

```ts
form.post(route('tenant.academic-calendar.holidays.store', { tenant: school.value.slug }));
```

- [ ] **Step 3: Create Edit page**

Create `resources/js/Pages/AcademicCalendar/Holidays/Edit.vue` with prop `holiday`, `useForm({ date: holiday.date, name: holiday.name, description: holiday.description ?? '', status: holiday.status })`. Submit to:

```ts
form.put(route('tenant.academic-calendar.holidays.update', { tenant: school.value.slug, holiday: holiday.id }));
```

- [ ] **Step 4: Wire sidebar**

Modify `resources/js/Layouts/AuthenticatedLayout.vue` Operasional nav item:

```ts
{ label: 'Kalender Akademik', shortLabel: 'Kalender', href: tenantRoute('tenant.academic-calendar.holidays.index'), active: route().current('tenant.academic-calendar.*'), show: isTenantRoute.value, icon: 'calendar-check' },
```

If current nav does not include `Kalender Akademik`, replace the existing placeholder item or insert it near `Absensi Guru` before `Pelanggaran`.

- [ ] **Step 5: Run frontend build**

Run:

```powershell
npm run build
```

Expected: build passes.

---

### Task 4: Seed data and docs ordering update

**Files:**
- Modify: `database/seeders/PlatformSeeder.php`
- Modify: `docs/plans/04-development-plan.md`
- Modify: `docs/plans/06-frontend-plan.md`

- [ ] **Step 1: Seed sample holidays**

In `PlatformSeeder::seedSchoolMasterData(School $school, string $prefix)`, after academic year/semester seed, create two records:

```php
\App\Models\AcademicCalendarHoliday::query()->firstOrCreate([
    'school_id' => $school->id,
    'date' => now()->startOfMonth()->addDays(5)->toDateString(),
], [
    'name' => 'Libur sekolah',
    'description' => 'Contoh kalender akademik tenant.',
    'status' => 'active',
]);

\App\Models\AcademicCalendarHoliday::query()->firstOrCreate([
    'school_id' => $school->id,
    'date' => now()->startOfMonth()->addDays(12)->toDateString(),
], [
    'name' => 'Kegiatan guru',
    'description' => 'Contoh agenda non-aktif untuk demo.',
    'status' => 'inactive',
]);
```

- [ ] **Step 2: Update development plan**

Modify `docs/plans/04-development-plan.md` so status table says:

```md
| **T1** Tenant app port | 🟡 Slice 1 berjalan | Port pilot dimulai dari Kalender Akademik tenant; berikutnya attendance/QR, pelanggaran, karakter, laporan, wali murid, backup |
| **PL1** Platform admin | 🟡 Baseline selesai | Dashboard founder, tenant list/detail, status, reset password, usage summary, trial ending soon sudah ada; audit/billing ops menyusul |
```

Add T1 Slice 1 checklist under T1 section:

```md
#### Slice 1 — Kalender Akademik tenant

- [x] Rute tenant `tenant.academic-calendar.holidays.*`
- [x] Model/table `academic_calendar_holidays` scoped by `school_id`
- [x] CRUD Inertia untuk hari libur kalender akademik
- [x] Permission `academic-calendar.manage`
- [x] Seed demo/alfa calendar data
```

- [ ] **Step 3: Update frontend plan**

Modify `docs/plans/06-frontend-plan.md` sidebar menu so `Kalender Akademik` is no longer only future port. Add note:

```md
T1 Slice 1 mem-port `AcademicCalendar/Holidays/*` dari pilot ke tenant route `tenant.academic-calendar.holidays.*` dengan brand Platform Sekolah dan `school_id` isolation.
```

- [ ] **Step 4: Run full verification**

Run:

```powershell
php artisan migrate:fresh --seed
php artisan test
npm run build
```

Expected:
- migrations + seed pass
- all PHPUnit tests pass except existing skipped tests
- Vite build passes

---

## Manual Demo

1. Login tenant demo: `http://127.0.0.1:8888/t/demo/login` with `admin@demo.test` / `password`.
2. Open sidebar item `Kalender Akademik`.
3. Confirm URL is `/t/demo/academic-calendar/holidays`.
4. Create a holiday on any date.
5. Edit the holiday to inactive.
6. Login tenant alfa: `http://127.0.0.1:8888/t/alfa/login` with `admin@alfa.test` / `password`.
7. Open `Kalender Akademik` and confirm demo holiday is not visible.

## Follow-up T1 Slices

After Slice 1 is verified:

1. Slice 2: Student attendance manual grid + attendance recap.
2. Slice 3: QR attendance session/scanner/student token.
3. Slice 4: Teacher attendance.
4. Slice 5: Violation types + student violations.
5. Slice 6: Character point types + student character points.
6. Slice 7: Reports and exports.
7. Slice 8: Guardian dashboard/student reports.
8. Slice 9: Notifications, WhatsApp preparation, backup.
