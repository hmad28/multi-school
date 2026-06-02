<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreAcademicLevelRequest;
use App\Http\Requests\MasterData\StoreAcademicYearRequest;
use App\Http\Requests\MasterData\StoreSemesterRequest;
use App\Http\Requests\MasterData\UpdateAcademicLevelRequest;
use App\Http\Requests\MasterData\UpdateAcademicYearRequest;
use App\Http\Requests\MasterData\UpdateSemesterRequest;
use App\Models\AcademicLevel;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AcademicSetupController extends Controller
{
    public function index(): Response
    {
        $this->authorizeManage();

        return Inertia::render('Academic/Index', [
            'levels' => AcademicLevel::query()->orderBy('numeric_value')->get(['id', 'name', 'numeric_value']),
            'years' => AcademicYear::query()->with('semesters:id,academic_year_id,name,starts_on,ends_on,is_active')->latest()->get(),
        ]);
    }

    public function storeLevel(StoreAcademicLevelRequest $request): RedirectResponse
    {
        AcademicLevel::query()->create($request->validated());

        return $this->back('Tingkat akademik berhasil ditambahkan.');
    }

    public function updateLevel(UpdateAcademicLevelRequest $request, AcademicLevel $level): RedirectResponse
    {
        $this->authorizeManage();

        $level->update($request->validated());

        return $this->back('Tingkat akademik berhasil diperbarui.');
    }

    public function storeYear(StoreAcademicYearRequest $request): RedirectResponse
    {
        AcademicYear::query()->create($request->validated());

        return $this->back('Tahun ajaran berhasil ditambahkan.');
    }

    public function updateYear(UpdateAcademicYearRequest $request, AcademicYear $year): RedirectResponse
    {
        $this->authorizeManage();

        $year->update($request->validated());

        return $this->back('Tahun ajaran berhasil diperbarui.');
    }

    public function storeSemester(StoreSemesterRequest $request): RedirectResponse
    {
        Semester::query()->create($request->validated());

        return $this->back('Semester berhasil ditambahkan.');
    }

    public function updateSemester(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $this->authorizeManage();

        $semester->update($request->validated());

        return $this->back('Semester berhasil diperbarui.');
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()?->school_id === TenantContext::id() && auth()->user()->can('academic.manage'), 403);
    }

    private function back(string $message): RedirectResponse
    {
        return redirect()
            ->route('tenant.academic.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', $message);
    }
}
