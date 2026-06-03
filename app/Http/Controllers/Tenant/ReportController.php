<?php

namespace App\Http\Controllers\Tenant;

use App\Exports\Reports\CharacterPointExport;
use App\Exports\Reports\StudentAttendanceExport;
use App\Exports\Reports\TeacherAttendanceExport;
use App\Exports\Reports\ViolationExport;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentCharacterPoint;
use App\Models\StudentViolation;
use App\Models\TeacherAttendance;
use App\Support\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('reports.print'), 403);

        return Inertia::render('Reports/Index', [
            'classes' => SchoolClass::query()
                ->with('academicLevel:id,name,numeric_value')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get(),
            'students' => Student::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'nis']),
            'filters' => [
                'from' => today()->startOfMonth()->toDateString(),
                'to' => today()->toDateString(),
            ],
        ]);
    }

    public function studentAttendance(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);
        $rows = StudentAttendance::query()
            ->with(['student.schoolClass.academicLevel', 'status'])
            ->when($data['class_id'] ?? null, fn ($query, $classId) => $query->where('class_id', $classId))
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return $this->download('reports.student-attendance', compact('rows', 'data'), 'laporan-absensi-siswa.pdf');
    }

    public function studentAttendanceExcel(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);
        $rows = StudentAttendance::query()
            ->with(['student.schoolClass.academicLevel', 'status'])
            ->when($data['class_id'] ?? null, fn ($query, $classId) => $query->where('class_id', $classId))
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return Excel::download(new StudentAttendanceExport($rows), "laporan-absensi-siswa-{$data['from']}-{$data['to']}.xlsx");
    }

    public function teacherAttendance(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);
        $rows = TeacherAttendance::query()
            ->with(['teacher', 'status'])
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return $this->download('reports.teacher-attendance', compact('rows', 'data'), 'laporan-absensi-guru.pdf');
    }

    public function teacherAttendanceExcel(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);
        $rows = TeacherAttendance::query()
            ->with(['teacher', 'status'])
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return Excel::download(new TeacherAttendanceExport($rows), "laporan-absensi-guru-{$data['from']}-{$data['to']}.xlsx");
    }

    public function violations(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'status' => ['nullable', 'in:pending,validated,rejected'],
        ]);
        $rows = StudentViolation::query()
            ->with(['student.schoolClass.academicLevel', 'violationType'])
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return $this->download('reports.violations', compact('rows', 'data'), 'laporan-pelanggaran-siswa.pdf');
    }

    public function violationsExcel(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'status' => ['nullable', 'in:pending,validated,rejected'],
        ]);
        $rows = StudentViolation::query()
            ->with(['student.schoolClass.academicLevel', 'violationType'])
            ->when($data['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return Excel::download(new ViolationExport($rows), "laporan-pelanggaran-siswa-{$data['from']}-{$data['to']}.xlsx");
    }

    public function characterPointsExcel(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
        ]);
        $rows = StudentCharacterPoint::query()
            ->with(['student.schoolClass.academicLevel', 'characterPointType', 'recorder'])
            ->whereBetween('date', [$data['from'], $data['to']])
            ->orderBy('date')
            ->get();

        return Excel::download(new CharacterPointExport($rows), "laporan-poin-karakter-{$data['from']}-{$data['to']}.xlsx");
    }

    public function parentCallLetter(Request $request)
    {
        abort_unless($request->user()->can('reports.print'), 403);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
        ]);
        $student = Student::query()->with('schoolClass.academicLevel')->findOrFail($data['student_id']);
        $violations = StudentViolation::query()->with('violationType')
            ->where('student_id', $student->id)
            ->where('status', 'validated')
            ->latest('date')
            ->get();
        $totalPoints = $violations->sum('points_snapshot');

        return $this->download('reports.parent-call-letter', compact('student', 'violations', 'totalPoints'), 'surat-panggilan-orang-tua.pdf');
    }

    private function download(string $view, array $data, string $filename)
    {
        $data['school'] = TenantContext::school();

        return Pdf::loadView($view, $data)->download($filename);
    }
}
