<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\StudentCharacterPoint;
use App\Models\StudentViolation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardianDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless($request->user()->can('guardians.view-dashboard'), 403);

        $children = $this->childrenFor($request);

        $childIds = $children->pluck('id');
        $monthStart = today()->startOfMonth()->toDateString();
        $monthEnd = today()->endOfMonth()->toDateString();

        $attendanceSummary = StudentAttendance::query()
            ->with('status:id,code,name')
            ->whereIn('student_id', $childIds)
            ->whereDate('date', '>=', $monthStart)
            ->whereDate('date', '<=', $monthEnd)
            ->get()
            ->groupBy(fn (StudentAttendance $attendance) => $attendance->status?->code ?? '-')
            ->map(fn ($rows, string $code) => [
                'code' => $code,
                'name' => $rows->first()->status?->name ?? 'Tidak diketahui',
                'total' => $rows->count(),
            ])
            ->values();

        $validatedViolations = StudentViolation::query()
            ->whereIn('student_id', $childIds)
            ->where('status', 'validated');

        $characterPoints = StudentCharacterPoint::query()
            ->whereIn('student_id', $childIds);

        return Inertia::render('Guardian/Dashboard', [
            'children' => $children->map(fn ($student) => [
                'id' => $student->id,
                'nis' => $student->nis,
                'full_name' => $student->name,
                'class_name' => $student->schoolClass?->display_name,
            ]),
            'summary' => [
                'child_count' => $children->count(),
                'attendance_month' => $attendanceSummary,
                'total_points' => (clone $validatedViolations)->sum('points_snapshot'),
                'validated_violation_count' => (clone $validatedViolations)->count(),
                'character_points' => (clone $characterPoints)->sum('points_snapshot'),
            ],
            'latestAttendances' => StudentAttendance::query()
                ->with(['student:id,name', 'status:id,code,name'])
                ->whereIn('student_id', $childIds)
                ->latest('date')
                ->limit(5)
                ->get()
                ->map(fn (StudentAttendance $attendance) => [
                    'id' => $attendance->id,
                    'student_name' => $attendance->student?->name,
                    'date' => $attendance->date?->toDateString(),
                    'code' => $attendance->status?->code,
                    'status' => $attendance->status?->name,
                    'note' => $attendance->note,
                ]),
            'latestCharacterPoints' => (clone $characterPoints)
                ->with(['student:id,name', 'characterPointType:id,name'])
                ->latest('date')
                ->limit(5)
                ->get()
                ->map(fn (StudentCharacterPoint $point) => [
                    'id' => $point->id,
                    'student_name' => $point->student?->name,
                    'type' => $point->characterPointType?->name,
                    'date' => $point->date?->toDateString(),
                    'points' => $point->points_snapshot,
                ]),
            'latestViolations' => (clone $validatedViolations)
                ->with(['student:id,name', 'violationType:id,name'])
                ->latest('date')
                ->limit(5)
                ->get()
                ->map(fn (StudentViolation $violation) => [
                    'id' => $violation->id,
                    'student_name' => $violation->student?->name,
                    'violation_type' => $violation->violationType?->name,
                    'date' => $violation->date?->toDateString(),
                    'points' => $violation->points_snapshot,
                ]),
        ]);
    }

    private function childrenFor(Request $request)
    {
        return $request->user()
            ->guardianStudents()
            ->with('schoolClass.academicLevel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
