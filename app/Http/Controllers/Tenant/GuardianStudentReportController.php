<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentReportData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardianStudentReportController extends Controller
{
    public function __invoke(Request $request, StudentReportData $reportData): Response
    {
        abort_unless($request->user()->can('guardians.view-child-reports'), 403);

        $student = Student::findOrFail($request->route('student'));
        $this->authorizeAccess($request, $student);

        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        return Inertia::render('Guardian/StudentShow', $reportData->build(
            $student,
            $data['from'] ?? null,
            $data['to'] ?? null,
        ));
    }

    private function authorizeAccess(Request $request, Student $student): void
    {
        $isLinked = $request->user()
            ->guardianStudents()
            ->whereKey($student->id)
            ->exists();

        abort_unless($isLinked, 403);
    }
}
