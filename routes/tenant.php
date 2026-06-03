<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\AcademicCalendarHolidayController;
use App\Http\Controllers\Tenant\AcademicSetupController;
use App\Http\Controllers\Tenant\AttendanceRecapController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\SchoolClassController;
use App\Http\Controllers\Tenant\StudentAttendanceController;
use App\Http\Controllers\Tenant\QrStudentAttendanceController;
use App\Http\Controllers\Tenant\StudentController;
use App\Http\Controllers\Tenant\CharacterPointTypeController;
use App\Http\Controllers\Tenant\StudentCharacterPointController;
use App\Http\Controllers\Tenant\TeacherAttendanceController;
use App\Http\Controllers\Tenant\TeacherController;
use App\Http\Controllers\Tenant\ViolationTypeController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\StudentViolationController;
use Illuminate\Support\Facades\Route;

Route::middleware('tenant')->group(function () {
    Route::get('/', function () {
        $tenant = request()->route('tenant');

        return auth()->check()
            ? redirect()->route('tenant.dashboard', ['tenant' => $tenant])
            : redirect()->route('tenant.login', ['tenant' => $tenant]);
    });

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
        Route::put('password', [PasswordController::class, 'update'])->name('password.update');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('guardian/dashboard', [\App\Http\Controllers\Tenant\GuardianDashboardController::class, '__invoke'])->name('guardian.dashboard');
        Route::get('guardian/students/{student}', [\App\Http\Controllers\Tenant\GuardianStudentReportController::class, '__invoke'])->name('guardian.students.show');
    });

    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('onboarding', [\App\Http\Controllers\Tenant\OnboardingController::class, 'show'])->name('onboarding.show');
        Route::patch('onboarding/profile', [\App\Http\Controllers\Tenant\OnboardingController::class, 'updateProfile'])->name('onboarding.profile');
        Route::post('onboarding/invite', [\App\Http\Controllers\Tenant\OnboardingController::class, 'invite'])->name('onboarding.invite');
        Route::post('onboarding/finish', [\App\Http\Controllers\Tenant\OnboardingController::class, 'finish'])->name('onboarding.finish');

        Route::resource('students', StudentController::class);
        Route::resource('teachers', TeacherController::class)->except(['show']);
        Route::resource('classes', SchoolClassController::class)->except(['show']);

        Route::get('academic', [AcademicSetupController::class, 'index'])->name('academic.index');

        Route::get('attendance/students', [StudentAttendanceController::class, 'index'])->name('attendance.students.index');
        Route::get('attendance/students/recap', [AttendanceRecapController::class, 'students'])->name('attendance.students.recap');
        Route::post('attendance/students', [StudentAttendanceController::class, 'store'])->name('attendance.students.store');
        Route::post('attendance/students/finalize', [StudentAttendanceController::class, 'finalize'])->name('attendance.students.finalize');
        Route::patch('attendance/students/{studentAttendance}', [StudentAttendanceController::class, 'correct'])->name('attendance.students.correct');

        Route::get('attendance/students/qr', [QrStudentAttendanceController::class, 'index'])->name('attendance.students.qr.index');
        Route::post('attendance/students/qr/session', [QrStudentAttendanceController::class, 'session'])->name('attendance.students.qr.session');
        Route::post('attendance/students/qr/scan', [QrStudentAttendanceController::class, 'scan'])->name('attendance.students.qr.scan');
        Route::get('students/{student}/qr', [QrStudentAttendanceController::class, 'studentQr'])->name('attendance.students.qr.token');
        Route::post('students/{student}/qr/regenerate', [QrStudentAttendanceController::class, 'regenerateStudentQr'])->name('attendance.students.qr.token.regenerate');

        Route::get('attendance/teachers', [TeacherAttendanceController::class, 'index'])->name('attendance.teachers.index');
        Route::post('attendance/teachers', [TeacherAttendanceController::class, 'store'])->name('attendance.teachers.store');
        Route::patch('attendance/teachers/{teacherAttendance}', [TeacherAttendanceController::class, 'correct'])->name('attendance.teachers.correct');

        Route::post('academic/levels', [AcademicSetupController::class, 'storeLevel'])->name('academic.levels.store');
        Route::patch('academic/levels/{level}', [AcademicSetupController::class, 'updateLevel'])->name('academic.levels.update');
        Route::post('academic/years', [AcademicSetupController::class, 'storeYear'])->name('academic.years.store');
        Route::patch('academic/years/{year}', [AcademicSetupController::class, 'updateYear'])->name('academic.years.update');
        Route::post('academic/semesters', [AcademicSetupController::class, 'storeSemester'])->name('academic.semesters.store');
        Route::patch('academic/semesters/{semester}', [AcademicSetupController::class, 'updateSemester'])->name('academic.semesters.update');

        Route::resource('academic-calendar/holidays', AcademicCalendarHolidayController::class)
            ->parameters(['holidays' => 'holiday'])
            ->names('academic-calendar.holidays')
            ->except(['show']);

        Route::resource('violation-types', ViolationTypeController::class)
            ->names('violation-types')
            ->except(['show']);
        Route::get('student-violations/pending', [StudentViolationController::class, 'pending'])->name('student-violations.pending');
        Route::patch('student-violations/{studentViolation}/validate', [StudentViolationController::class, 'validateViolation'])->name('student-violations.validate');
        Route::patch('student-violations/{studentViolation}/reject', [StudentViolationController::class, 'reject'])->name('student-violations.reject');
        Route::resource('student-violations', StudentViolationController::class)
            ->names('student-violations')
            ->only(['index', 'create', 'store']);

        Route::resource('character-point-types', CharacterPointTypeController::class)
            ->names('character-point-types')
            ->except(['show']);
        Route::resource('student-character-points', StudentCharacterPointController::class)
            ->names('student-character-points')
            ->only(['index', 'create', 'store']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/student-attendance', [ReportController::class, 'studentAttendance'])->name('reports.student-attendance');
        Route::get('reports/student-attendance/excel', [ReportController::class, 'studentAttendanceExcel'])->name('reports.student-attendance.excel');
        Route::get('reports/teacher-attendance', [ReportController::class, 'teacherAttendance'])->name('reports.teacher-attendance');
        Route::get('reports/teacher-attendance/excel', [ReportController::class, 'teacherAttendanceExcel'])->name('reports.teacher-attendance.excel');
        Route::get('reports/violations', [ReportController::class, 'violations'])->name('reports.violations');
        Route::get('reports/violations/excel', [ReportController::class, 'violationsExcel'])->name('reports.violations.excel');
        Route::get('reports/character-points/excel', [ReportController::class, 'characterPointsExcel'])->name('reports.character-points.excel');
        Route::get('reports/parent-call-letter', [ReportController::class, 'parentCallLetter'])->name('reports.parent-call-letter');
    });
});
