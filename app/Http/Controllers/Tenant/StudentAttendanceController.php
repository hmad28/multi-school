<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Attendance\CorrectStudentAttendanceAction;
use App\Actions\Attendance\SubmitStudentAttendanceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CorrectStudentAttendanceRequest;
use App\Http\Requests\Attendance\StoreStudentAttendanceRequest;
use App\Models\AttendanceClassSubmission;
use App\Models\AttendanceStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\AttendanceCalendarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentAttendanceController extends Controller
{
    public function index(Request $request, AttendanceCalendarService $calendar): Response
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        $date = $request->string('date')->toString() ?: today()->toDateString();
        $classId = $request->string('class_id')->toString();
        $statusId = $request->string('status_id')->toString();
        $scanState = $request->string('scan_state')->toString() ?: 'scanned';
        $scanState = in_array($scanState, ['scanned', 'unscanned', 'all'], true) ? $scanState : 'scanned';
        $schoolClass = $classId ? SchoolClass::query()->with('academicLevel:id,name,numeric_value')->find($classId) : null;
        $alpha = AttendanceStatus::query()->where('code', 'A')->first();
        $holiday = $calendar->holidayFor($date);
        $lateCutoffPassed = $calendar->hasLateCutoffPassed($date);
        $effectiveSchoolDay = ! $holiday;

        $attendanceByStudent = StudentAttendance::query()
            ->with(['status:id,code,name,color'])
            ->when($schoolClass, fn ($query) => $query->where('class_id', $schoolClass->id))
            ->whereDate('date', $date)
            ->get()
            ->keyBy('student_id');

        $attendances = Student::query()
            ->with('schoolClass.academicLevel:id,name,numeric_value')
            ->where('status', 'active')
            ->when($schoolClass, fn ($query) => $query->where('class_id', $schoolClass->id))
            ->orderBy('name')
            ->get(['id', 'name', 'nis', 'class_id'])
            ->map(function (Student $student) use ($attendanceByStudent, $alpha, $effectiveSchoolDay, $holiday, $lateCutoffPassed) {
                $attendance = $attendanceByStudent->get($student->id);
                $scanned = (bool) $attendance;
                $countsAsAlpha = ! $scanned && $effectiveSchoolDay && $lateCutoffPassed;
                $status = $attendance?->status ?? ($countsAsAlpha ? $alpha : null);

                return [
                    'id' => $attendance?->id ?? 'student-'.$student->id,
                    'student' => $student,
                    'scanned' => $scanned,
                    'counts_as_alpha' => $countsAsAlpha,
                    'scan_state_label' => $scanned ? 'Sudah scan' : ($holiday ? 'Hari libur' : ($lateCutoffPassed ? 'Alpha' : 'Belum scan')),
                    'attendance_status_id' => $attendance?->attendance_status_id ?? ($countsAsAlpha ? $alpha?->id : null),
                    'arrival_time' => $attendance?->arrival_time,
                    'departure_time' => $attendance?->departure_time,
                    'status' => $status,
                    'note' => $attendance?->note,
                ];
            })
            ->filter(fn (array $row) => $scanState === 'all' || ($scanState === 'scanned' ? $row['scanned'] : ! $row['scanned']))
            ->filter(fn (array $row) => blank($statusId) || $row['attendance_status_id'] === $statusId)
            ->values();

        return Inertia::render('Attendance/Students/Index', [
            'classes' => SchoolClass::query()->with('academicLevel:id,name,numeric_value')->where('status', 'active')->orderBy('sort_order')->get(),
            'statuses' => AttendanceStatus::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'code', 'name', 'slug', 'color']),
            'selectedClass' => $schoolClass,
            'date' => $date,
            'attendances' => $attendances,
            'filters' => [
                'class_id' => $classId,
                'date' => $date,
                'status_id' => $statusId,
                'scan_state' => $scanState,
            ],
            'submitted' => $schoolClass ? AttendanceClassSubmission::where('class_id', $schoolClass->id)->where('date', $date)->exists() : false,
            'holiday' => $holiday,
            'lateCutoffPassed' => $lateCutoffPassed,
            'effectiveSchoolDay' => $effectiveSchoolDay,
        ]);
    }

    public function store(StoreStudentAttendanceRequest $request, SubmitStudentAttendanceAction $action): RedirectResponse
    {
        $schoolClass = SchoolClass::findOrFail($request->validated('class_id'));
        $action->execute($request->user(), $schoolClass, $request->validated('date'), $request->validated('attendances'));

        return redirect()
            ->route('tenant.attendance.students.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Absensi siswa berhasil dikirim.');
    }

    public function finalize(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        $data = $request->validate([
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'date' => ['required', 'date'],
        ]);

        $exists = StudentAttendance::query()
            ->where('class_id', $data['class_id'])
            ->whereDate('date', $data['date'])
            ->exists();

        if (! $exists) {
            return back()->withErrors(['date' => 'Belum ada siswa yang tercatat untuk tanggal ini.']);
        }

        AttendanceClassSubmission::firstOrCreate(
            ['class_id' => $data['class_id'], 'date' => $data['date']],
            ['submitted_by' => $request->user()->id, 'submitted_at' => now()]
        );

        StudentAttendance::query()
            ->where('class_id', $data['class_id'])
            ->whereDate('date', $data['date'])
            ->whereNull('locked_at')
            ->update(['locked_at' => now(), 'submitted_by' => $request->user()->id]);

        return back()->with('success', 'Absensi siswa berhasil dikunci.');
    }

    public function correct(CorrectStudentAttendanceRequest $request, StudentAttendance $studentAttendance, CorrectStudentAttendanceAction $action): RedirectResponse
    {
        $action->execute($request->user(), $studentAttendance, $request->validated('attendance_status_id'), $request->validated('note'));

        return back()->with('success', 'Absensi siswa berhasil dikoreksi.');
    }
}
