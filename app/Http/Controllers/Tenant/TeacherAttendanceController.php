<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Attendance\CorrectTeacherAttendanceAction;
use App\Actions\Attendance\SubmitTeacherAttendanceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CorrectTeacherAttendanceRequest;
use App\Http\Requests\Attendance\StoreTeacherAttendanceRequest;
use App\Models\AttendanceStatus;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use App\Models\TeacherAttendanceSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('attendance.teachers.input'), 403);

        $date = $request->string('date')->toString() ?: today()->toDateString();

        return Inertia::render('Attendance/Teachers/Index', [
            'date' => $date,
            'statuses' => AttendanceStatus::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'code', 'name', 'slug', 'color']),
            'teachers' => Teacher::query()->where('status', 'active')->orderBy('full_name')->get(['id', 'full_name', 'nip', 'position']),
            'attendances' => TeacherAttendance::query()
                ->with('status:id,code,name,color')
                ->where('date', $date)
                ->get()
                ->keyBy('teacher_id'),
            'submitted' => TeacherAttendanceSubmission::where('date', $date)->exists(),
        ]);
    }

    public function store(StoreTeacherAttendanceRequest $request, SubmitTeacherAttendanceAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated('date'), $request->validated('attendances'));

        return redirect()
            ->route('tenant.attendance.teachers.index', ['tenant' => $request->route('tenant'), 'date' => $request->validated('date')])
            ->with('success', 'Absensi guru berhasil dikirim.');
    }

    public function correct(CorrectTeacherAttendanceRequest $request, TeacherAttendance $teacherAttendance, CorrectTeacherAttendanceAction $action): RedirectResponse
    {
        $action->execute($request->user(), $teacherAttendance, $request->validated('attendance_status_id'), $request->validated('note'));

        return back()->with('success', 'Absensi guru berhasil dikoreksi.');
    }
}
