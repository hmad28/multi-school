<?php

namespace App\Http\Controllers\Platform;

use App\Enums\SchoolStatus;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $schools = School::query()
            ->withCount('users', 'students')
            ->latest()
            ->get();

        return Inertia::render('Platform/Dashboard', [
            'stats' => [
                'total' => $schools->count(),
                'trial' => $schools->where('status', SchoolStatus::Trial)->count(),
                'active' => $schools->where('status', SchoolStatus::Active)->count(),
                'suspended' => $schools->where('status', SchoolStatus::Suspended)->count(),
                'students' => $schools->sum('students_count'),
                'users' => $schools->sum('users_count'),
                'monthlyRevenue' => (int) Subscription::query()->where('status', 'active')->sum('amount'),
            ],
            'trialEndingSoon' => School::query()
                ->where('status', SchoolStatus::Trial)
                ->whereNotNull('trial_ends_at')
                ->whereBetween('trial_ends_at', [now(), now()->addDays(7)])
                ->orderBy('trial_ends_at')
                ->get(['id', 'name', 'slug', 'email', 'trial_ends_at'])
                ->map(fn (School $school): array => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slug' => $school->slug,
                    'email' => $school->email,
                    'trial_ends_at' => $school->trial_ends_at?->toIso8601String(),
                ]),
            'recentSchools' => $schools
                ->take(5)
                ->map(fn (School $school): array => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'slug' => $school->slug,
                    'email' => $school->email,
                    'status' => $school->status->value,
                    'trial_ends_at' => $school->trial_ends_at?->toIso8601String(),
                    'users_count' => $school->users_count,
                    'students_count' => $school->students_count,
                ]),
        ]);
    }
}
