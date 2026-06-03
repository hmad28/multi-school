<?php

namespace App\Http\Controllers\Platform;

use App\Enums\SchoolStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\School;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    public function index(Request $request): Response
    {
        $schools = School::query()
            ->withCount('users', 'students')
            ->orderBy('name')
            ->get()
            ->map(fn (School $school) => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'email' => $school->email,
                'status' => $school->status->value,
                'trial_ends_at' => $school->trial_ends_at?->toIso8601String(),
                'users_count' => $school->users_count,
                'students_count' => $school->students_count,
            ]);

        return Inertia::render('Platform/Tenants/Index', [
            'schools' => $schools,
        ]);
    }

    public function updateStatus(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                SchoolStatus::Trial->value,
                SchoolStatus::Active->value,
                SchoolStatus::Suspended->value,
            ])],
        ]);

        $oldStatus = $school->status->value;
        $school->update(['status' => $validated['status']]);

        ActivityLogger::logStatusChange($school, $oldStatus, $validated['status'], $request->user());

        return back()->with('success', 'Status sekolah diperbarui.');
    }

    public function resetPassword(Request $request, School $school): RedirectResponse
    {
        $previousTeam = getPermissionsTeamId();
        setPermissionsTeamId($school->id);

        try {
            $admin = User::query()
                ->where('school_id', $school->id)
                ->whereHas('roles', fn ($q) => $q
                    ->where('roles.name', 'admin-sekolah')
                    ->where('roles.school_id', $school->id))
                ->first();
        } finally {
            setPermissionsTeamId($previousTeam);
        }

        if ($admin === null) {
            return back()->with('error', 'Admin sekolah tidak ditemukan.');
        }

        $password = Str::password(12);
        $admin->update(['password' => Hash::make($password)]);

        ActivityLogger::logPasswordReset($admin, $request->user());

        return back()->with('success', "Password admin direset. Password sementara: {$password}");
    }

    public function show(School $school): Response
    {
        $school->load(['subscriptions' => fn ($query) => $query->latest(), 'users' => fn ($query) => $query->latest()]);
        $school->loadCount('users', 'students');

        $activityLogs = ActivityLog::query()
            ->with('user')
            ->where('school_id', $school->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'performed_by' => $log->user?->name ?? 'Sistem',
                'created_at' => $log->created_at->toIso8601String(),
            ]);

        return Inertia::render('Platform/Tenants/Show', [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'slug' => $school->slug,
                'email' => $school->email,
                'phone' => $school->phone,
                'address' => $school->address,
                'status' => $school->status->value,
                'trial_ends_at' => $school->trial_ends_at?->toIso8601String(),
                'users_count' => $school->users_count,
                'students_count' => $school->students_count,
            ],
            'subscription' => $school->subscriptions->first()?->only(['plan', 'period', 'starts_at', 'ends_at', 'status', 'amount']),
            'admins' => $school->users
                ->take(5)
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->toIso8601String(),
                ]),
            'activityLogs' => $activityLogs,
        ]);
    }
}
