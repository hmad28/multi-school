<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Violations\CreateStudentViolationAction;
use App\Actions\Violations\RejectStudentViolationAction;
use App\Actions\Violations\ValidateStudentViolationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Violations\RejectStudentViolationRequest;
use App\Http\Requests\Violations\StoreStudentViolationRequest;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentViolation;
use App\Models\ViolationThreshold;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentViolationController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('violations.input') || $request->user()->can('violations.validate'), 403);

        $semester = Semester::query()->where('is_active', true)->first();

        return Inertia::render('Violations/Students/Index', [
            'violations' => StudentViolation::query()
                ->with(['student:id,full_name,nis,class_id', 'student.schoolClass.academicLevel:id,name,numeric_value', 'violationType:id,name', 'reporter:id,name', 'validator:id,name'])
                ->when($request->string('status')->toString(), fn ($query, string $status) => $query->where('status', $status))
                ->when($request->string('student_id')->toString(), fn ($query, string $studentId) => $query->where('student_id', $studentId))
                ->latest('date')
                ->paginate(15)
                ->withQueryString(),
            'students' => Student::query()->with('schoolClass.academicLevel:id,name,numeric_value')->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'nis', 'class_id']),
            'filters' => $request->only('status', 'student_id'),
            'thresholds' => ViolationThreshold::query()->orderBy('sort_order')->get(['points', 'label']),
            'activeSemesterId' => $semester?->id,
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('violations.input'), 403);

        return Inertia::render('Violations/Students/Create', [
            'students' => Student::query()->with('schoolClass.academicLevel:id,name,numeric_value')->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'nis', 'class_id']),
            'types' => ViolationType::query()->where('status', 'active')->orderBy('sort_order')->get(['id', 'category', 'name', 'points']),
        ]);
    }

    public function store(StoreStudentViolationRequest $request, CreateStudentViolationAction $action): RedirectResponse
    {
        $student = Student::findOrFail($request->validated('student_id'));
        $type = ViolationType::findOrFail($request->validated('violation_type_id'));
        $action->execute($request->user(), $student, $type, $request->validated('date'), $request->validated('note'), $request->file('evidence'));

        return redirect()->route('tenant.student-violations.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Pelanggaran siswa berhasil dicatat.');
    }

    public function pending(Request $request): Response
    {
        abort_unless($request->user()->can('violations.validate'), 403);

        return Inertia::render('Violations/Students/Pending', [
            'violations' => StudentViolation::query()
                ->with(['student:id,full_name,nis,class_id', 'student.schoolClass.academicLevel:id,name,numeric_value', 'violationType:id,name', 'reporter:id,name'])
                ->where('status', 'pending')
                ->latest('date')
                ->paginate(15),
        ]);
    }

    public function validateViolation(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('violations.validate'), 403);

        $studentViolation = StudentViolation::findOrFail($request->route('studentViolation'));
        app(ValidateStudentViolationAction::class)->execute($request->user(), $studentViolation);

        return back()->with('success', 'Pelanggaran berhasil divalidasi.');
    }

    public function reject(RejectStudentViolationRequest $request): RedirectResponse
    {
        $studentViolation = StudentViolation::findOrFail($request->route('studentViolation'));
        app(RejectStudentViolationAction::class)->execute($request->user(), $studentViolation, $request->validated('rejection_reason'));

        return back()->with('success', 'Pelanggaran berhasil ditolak.');
    }
}
