<?php

namespace App\Http\Controllers\Registration;

use App\Actions\Registration\RegisterSchoolAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\RegisterSchoolRequest;
use App\Support\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SchoolRegistrationController extends Controller
{
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/RegisterSchool', [
            'plan' => $request->query('paket'),
            'period' => $request->query('billing'),
            'trialDays' => (int) config('platform.trial_days', 14),
        ]);
    }

    public function store(RegisterSchoolRequest $request, RegisterSchoolAction $action): RedirectResponse
    {
        $admin = $action->execute($request->registrationData());

        ActivityLogger::log(
            'school.registered',
            "Sekolah {$admin->school->name} mendaftar mandiri oleh {$admin->name} ({$admin->email}).",
            $admin,
            $admin->school,
            ['slug' => $admin->school->slug, 'plan' => $request->input('plan', 'standar')],
        );

        event(new Registered($admin));

        Auth::login($admin);

        return redirect()->route('verification.notice');
    }
}
