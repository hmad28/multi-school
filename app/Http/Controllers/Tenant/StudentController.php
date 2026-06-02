<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreStudentRequest;
use App\Http\Requests\MasterData\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Student::class);

        return Inertia::render('Students/Index', [
            'students' => Student::query()
                ->with('schoolClass:id,academic_level_id,name')
                ->latest()
                ->get(['id', 'name', 'nis', 'nisn', 'class_id', 'gender', 'guardian_name', 'guardian_phone', 'status']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Student::class);

        return Inertia::render('Students/Create', [
            'classes' => $this->classOptions(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        Student::query()->create($request->validated());

        return redirect()
            ->route('tenant.students.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(string $tenant, Student $student): Response
    {
        $this->abortIfWrongTenant($student);
        $this->authorize('view', $student);

        return Inertia::render('Students/Show', [
            'student' => $student->load('schoolClass.academicLevel'),
        ]);
    }

    public function edit(string $tenant, Student $student): Response
    {
        $this->abortIfWrongTenant($student);
        $this->authorize('update', $student);

        return Inertia::render('Students/Edit', [
            'student' => $student->only(['id', 'name', 'nis', 'nisn', 'class_id', 'gender', 'guardian_name', 'guardian_phone', 'address', 'status']),
            'classes' => $this->classOptions(),
        ]);
    }

    public function update(UpdateStudentRequest $request, string $tenant, Student $student): RedirectResponse
    {
        $this->abortIfWrongTenant($student);
        $student->update($request->validated());

        return redirect()
            ->route('tenant.students.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(string $tenant, Student $student): RedirectResponse
    {
        $this->abortIfWrongTenant($student);
        $this->authorize('delete', $student);

        $student->delete();

        return redirect()
            ->route('tenant.students.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data siswa berhasil dihapus.');
    }

    private function abortIfWrongTenant(Model $model): void
    {
        abort_if($model->school_id !== TenantContext::id(), 404);
    }

    private function classOptions()
    {
        return SchoolClass::query()
            ->with('academicLevel:id,numeric_value,name')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (SchoolClass $class): array => [
                'id' => $class->id,
                'name' => $class->display_name,
            ]);
    }
}
