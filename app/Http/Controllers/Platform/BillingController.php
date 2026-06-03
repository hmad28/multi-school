<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Subscription::query()->with('school');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $subscriptions = $query->latest()->get()->map(fn (Subscription $s): array => [
            'id' => $s->id,
            'school_name' => $s->school->name,
            'school_slug' => $s->school->slug,
            'plan' => $s->plan,
            'period' => $s->period,
            'starts_at' => $s->starts_at?->toIso8601String(),
            'ends_at' => $s->ends_at?->toIso8601String(),
            'status' => $s->status,
            'amount' => $s->amount,
            'payment_reference' => $s->payment_reference,
        ]);

        return Inertia::render('Platform/Billing/Index', [
            'subscriptions' => $subscriptions,
            'filters' => $request->only(['status', 'plan']),
        ]);
    }

    public function show(Subscription $subscription): Response
    {
        $subscription->load('school');

        return Inertia::render('Platform/Billing/Show', [
            'subscription' => [
                'id' => $subscription->id,
                'school_name' => $subscription->school->name,
                'school_slug' => $subscription->school->slug,
                'school_id' => $subscription->school->id,
                'plan' => $subscription->plan,
                'period' => $subscription->period,
                'starts_at' => $subscription->starts_at?->toIso8601String(),
                'ends_at' => $subscription->ends_at?->toIso8601String(),
                'status' => $subscription->status,
                'amount' => $subscription->amount,
                'payment_reference' => $subscription->payment_reference,
            ],
        ]);
    }

    public function updateStatus(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'past_due', 'canceled'])],
        ]);

        $oldStatus = $subscription->status;
        $subscription->update(['status' => $validated['status']]);

        ActivityLogger::log(
            'billing.status_changed',
            "Subscription {$subscription->school->name}: {$oldStatus} → {$validated['status']}",
            $request->user(),
            $subscription->school,
            ['from' => $oldStatus, 'to' => $validated['status'], 'plan' => $subscription->plan],
        );

        return back()->with('success', 'Status subscription diperbarui.');
    }
}
