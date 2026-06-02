<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CharacterPoints\CreateStudentCharacterPointAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CharacterPoints\StoreStudentCharacterPointRequest;
use App\Models\CharacterPointType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentCharacterPoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StudentCharacterPointController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('character-points.view') || $request->user()->can('character-points.input'), 403);

        $semester = Semester::query()->where('is_active', true)->first();
        $studentId = $request->string('student_id')->toString();
        $category = $request->string('category')->toString();

        return Inertia::render('CharacterPoints/Students/Index', [
            'points' => StudentCharacterPoint::query()
                ->with(['student:id,full_name,nis,class_id', 'student.schoolClass.academicLevel:id,name,numeric_value', 'characterPointType:id,name', 'recorder:id,name'])
                ->when($studentId, fn ($query) => $query->where('student_id', $studentId))
                ->when($category, fn ($query) => $query->where('category_snapshot', $category))
                ->latest('date')
                ->paginate(15)
                ->withQueryString(),
            'students' => Student::query()->with('schoolClass.academicLevel:id,name,numeric_value')->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'nis', 'class_id']),
            'filters' => $request->only('student_id', 'category'),
            'categories' => CharacterPointType::query()->where('status', 'active')->orderBy('category')->distinct()->pluck('category'),
            'totals' => $semester ? StudentCharacterPoint::query()
                ->select('student_id', DB::raw('sum(points_snapshot) as total'))
                ->where('semester_id', $semester->id)
                ->groupBy('student_id')
                ->pluck('total', 'student_id') : [],
            'activeSemesterId' => $semester?->id,
            'canInput' => $request->user()->can('character-points.input'),
            'canManageTypes' => $request->user()->can('character-points.manage-types'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('character-points.input'), 403);

        return Inertia::render('CharacterPoints/Students/Create', [
            'students' => Student::query()->with('schoolClass.academicLevel:id,name,numeric_value')->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'nis', 'class_id']),
            'types' => CharacterPointType::query()->where('status', 'active')->orderBy('sort_order')->get(['id', 'category', 'name', 'points']),
        ]);
    }

    public function store(StoreStudentCharacterPointRequest $request, CreateStudentCharacterPointAction $action): RedirectResponse
    {
        $student = Student::findOrFail($request->validated('student_id'));
        $type = CharacterPointType::findOrFail($request->validated('character_point_type_id'));
        $action->execute($request->user(), $student, $type, $request->validated('date'), $request->validated('note'));

        return redirect()->route('tenant.student-character-points.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Poin karakter siswa berhasil dicatat.');
    }
}
