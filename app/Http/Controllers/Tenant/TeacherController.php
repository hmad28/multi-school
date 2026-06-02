<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreTeacherRequest;
use App\Http\Requests\MasterData\UpdateTeacherRequest;
use App\Models\Teacher;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Teacher::class);

        return Inertia::render('Teachers/Index', [
            'teachers' => Teacher::query()
                ->latest()
                ->get(['id', 'nip', 'full_name', 'position', 'phone', 'status', 'can_input_teacher_attendance']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Teacher::class);

        return Inertia::render('Teachers/Create');
    }

    public function store(StoreTeacherRequest $request): RedirectResponse
    {
        Teacher::query()->create($request->validated());

        return redirect()
            ->route('tenant.teachers.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data guru berhasil ditambahkan.');
    }

    public function edit(string $tenant, Teacher $teacher): Response
    {
        $this->authorize('update', $teacher);

        return Inertia::render('Teachers/Edit', [
            'teacher' => $teacher->only(['id', 'nip', 'full_name', 'position', 'phone', 'status', 'can_input_teacher_attendance']),
        ]);
    }

    public function update(UpdateTeacherRequest $request, string $tenant, Teacher $teacher): RedirectResponse
    {
        $teacher->update($request->validated());

        return redirect()
            ->route('tenant.teachers.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(string $tenant, Teacher $teacher): RedirectResponse
    {
        $this->authorize('delete', $teacher);

        $teacher->delete();

        return redirect()
            ->route('tenant.teachers.index', ['tenant' => TenantContext::school()->slug])
            ->with('success', 'Data guru berhasil dihapus.');
    }
}
