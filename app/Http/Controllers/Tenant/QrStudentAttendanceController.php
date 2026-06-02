<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\QrAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QrStudentAttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        return Inertia::render('Attendance/Students/QrScanner', [
            'classes' => SchoolClass::query()
                ->with('academicLevel:id,name,numeric_value')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get(['id', 'academic_level_id', 'name']),
            'filters' => [
                'class_id' => $request->string('class_id')->toString(),
                'date' => $request->string('date')->toString() ?: today()->toDateString(),
                'scan_type' => $request->string('scan_type')->toString() ?: 'arrival',
            ],
        ]);
    }

    public function session(Request $request, QrAttendanceService $service): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        $data = $request->validate([
            'class_id' => ['required', 'uuid', 'exists:classes,id'],
            'date' => ['required', 'date'],
            'scan_type' => ['required', 'in:arrival,departure'],
        ]);

        $schoolClass = SchoolClass::findOrFail($data['class_id']);
        $issued = $service->createSession($request->user(), $schoolClass, $data['date'], $data['scan_type']);

        return redirect()->route('tenant.attendance.students.qr.index', ['tenant' => $request->route('tenant'), ...$data])
            ->with('success', 'Sesi QR aktif selama 10 menit.')
            ->with('qrSessionToken', $issued['token'])
            ->with('qrSessionExpiresAt', $issued['session']->expires_at->toIso8601String());
    }

    public function scan(Request $request, QrAttendanceService $service): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        $data = $request->validate([
            'student_token' => ['required', 'string'],
            'date' => ['required', 'date'],
            'scan_type' => ['required', 'in:arrival,departure'],
            'force_update' => ['sometimes', 'boolean'],
        ]);

        $attendance = $service->scanStudent($request->user(), $data['student_token'], $data['date'], $data['scan_type'], (bool) ($data['force_update'] ?? false));

        $scanLabel = $data['scan_type'] === 'arrival' ? 'datang' : 'pulang';
        $scanTime = $data['scan_type'] === 'arrival' ? $attendance->arrival_time?->format('H:i') : $attendance->departure_time?->format('H:i');

        return back()->with('success', 'Scan '.$scanLabel.' berhasil: '.$attendance->student->name.' - '.$attendance->student->schoolClass?->name.' pukul '.$scanTime.'.');
    }

    public function studentQr(Request $request, string $tenant, Student $student, QrAttendanceService $service): Response
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        $student->load('schoolClass.academicLevel');
        $hadToken = filled($student->qr_token);

        return Inertia::render('Attendance/Students/QrToken', [
            'student' => $student,
            'token' => $service->studentToken($student),
            'hadToken' => $hadToken,
        ]);
    }

    public function regenerateStudentQr(Request $request, string $tenant, Student $student, QrAttendanceService $service): RedirectResponse
    {
        abort_unless($request->user()->can('attendance.students.input'), 403);

        $service->issueStudentToken($student);

        return redirect()->route('tenant.attendance.students.qr.token', ['tenant' => $tenant, 'student' => $student->id])
            ->with('success', 'QR siswa berhasil dibuat ulang. QR lama tidak berlaku lagi.');
    }
}
