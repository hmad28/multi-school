<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreSchoolClassRequest;
use App\Http\Requests\MasterData\UpdateSchoolClassRequest;
use App\Models\AcademicLevel;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SchoolClassController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', SchoolClass::class);

        return Inertia::render('Classes/Index', [
            'classes' => SchoolClass::query()
                ->with(['academicLevel:id,name,numeric_value', 'homeroomTeacher:id,full_name'])
                ->withCount('students')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', SchoolClass::class);

        return Inertia::render('Classes/Create', [
            'levels' => AcademicLevel::query()->orderBy('numeric_value')->get(['id', 'name', 'numeric_value']),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function store(StoreSchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::query()->create($request->validated());

        return redirect()
            ->route('tenant.classes.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function edit(string $tenant, SchoolClass $class): Response
    {
        $this->authorize('update', $class);

        return Inertia::render('Classes/Edit', [
            'classData' => $class->only(['id', 'academic_level_id', 'name', 'homeroom_teacher_id', 'status', 'sort_order']),
            'levels' => AcademicLevel::query()->orderBy('numeric_value')->get(['id', 'name', 'numeric_value']),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name']),
        ]);
    }

    public function update(UpdateSchoolClassRequest $request, string $tenant, SchoolClass $class): RedirectResponse
    {
        $class->update($request->validated());

        return redirect()
            ->route('tenant.classes.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy(string $tenant, SchoolClass $class): RedirectResponse
    {
        $this->authorize('delete', $class);

        $class->delete();

        return redirect()
            ->route('tenant.classes.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data kelas berhasil dihapus.');
    }
}
