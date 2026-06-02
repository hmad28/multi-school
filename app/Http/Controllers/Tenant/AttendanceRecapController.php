<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AttendanceStatus;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceRecapController extends Controller
{
    public function students(Request $request): Response
    {
        abort_unless($request->user()->can('attendance.students.input') || $request->user()->can('attendance.students.correct'), 403);

        $from = $request->string('from')->toString() ?: today()->startOfMonth()->toDateString();
        $to = $request->string('to')->toString() ?: today()->toDateString();
        $classId = $request->string('class_id')->toString();

        $rows = StudentAttendance::query()
            ->selectRaw('student_id, attendance_status_id, count(*) as total')
            ->with(['student:id,name,nis,class_id', 'status:id,code,name'])
            ->when($classId, fn ($query) => $query->where('class_id', $classId))
            ->whereBetween('date', [$from, $to])
            ->groupBy('student_id', 'attendance_status_id')
            ->get();

        return Inertia::render('Attendance/Students/Recap', [
            'classes' => SchoolClass::query()->with('academicLevel:id,name,numeric_value')->where('status', 'active')->orderBy('sort_order')->get(),
            'statuses' => AttendanceStatus::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'code', 'name']),
            'rows' => $rows,
            'filters' => ['from' => $from, 'to' => $to, 'class_id' => $classId],
        ]);
    }
}
