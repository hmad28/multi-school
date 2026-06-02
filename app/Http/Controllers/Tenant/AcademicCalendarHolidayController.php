<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicCalendar\StoreAcademicCalendarHolidayRequest;
use App\Http\Requests\AcademicCalendar\UpdateAcademicCalendarHolidayRequest;
use App\Models\AcademicCalendarHoliday;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicCalendarHolidayController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('academic-calendar.manage'), 403);

        $month = $request->string('month')->toString();
        $startsAt = filled($month)
            ? CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->toImmutable()->startOfMonth();
        $calendarStart = $startsAt->startOfWeek();
        $calendarEnd = $startsAt->endOfMonth()->endOfWeek();

        return Inertia::render('AcademicCalendar/Holidays/Index', [
            'holidays' => AcademicCalendarHoliday::query()
                ->whereBetween('date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
                ->orderBy('date')
                ->get()
                ->map(fn (AcademicCalendarHoliday $holiday): array => [
                    'id' => $holiday->id,
                    'date' => $holiday->date?->toDateString(),
                    'name' => $holiday->name,
                    'description' => $holiday->description,
                    'status' => $holiday->status,
                ]),
            'month' => $startsAt->format('Y-m'),
            'monthLabel' => $startsAt->translatedFormat('F Y'),
            'previousMonth' => $startsAt->subMonth()->format('Y-m'),
            'nextMonth' => $startsAt->addMonth()->format('Y-m'),
            'calendarStart' => $calendarStart->toDateString(),
            'calendarEnd' => $calendarEnd->toDateString(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', AcademicCalendarHoliday::class);

        return Inertia::render('AcademicCalendar/Holidays/Create');
    }

    public function store(StoreAcademicCalendarHolidayRequest $request): RedirectResponse
    {
        AcademicCalendarHoliday::query()->create($request->validated());

        return redirect()->route('tenant.academic-calendar.holidays.index', [
            'tenant' => $request->route('tenant'),
        ])->with('success', 'Hari libur berhasil dibuat.');
    }

    public function edit(string $tenant, AcademicCalendarHoliday $holiday): Response
    {
        $this->authorize('update', $holiday);

        return Inertia::render('AcademicCalendar/Holidays/Edit', [
            'holiday' => [
                'id' => $holiday->id,
                'date' => $holiday->date?->toDateString(),
                'name' => $holiday->name,
                'description' => $holiday->description,
                'status' => $holiday->status,
            ],
        ]);
    }

    public function update(UpdateAcademicCalendarHolidayRequest $request, string $tenant, AcademicCalendarHoliday $holiday): RedirectResponse
    {
        $holiday->update($request->validated());

        return redirect()->route('tenant.academic-calendar.holidays.index', [
            'tenant' => $tenant,
        ])->with('success', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(Request $request, string $tenant, AcademicCalendarHoliday $holiday): RedirectResponse
    {
        $this->authorize('delete', $holiday);
        $holiday->delete();

        return redirect()->route('tenant.academic-calendar.holidays.index', [
            'tenant' => $tenant,
        ])->with('success', 'Hari libur berhasil dihapus.');
    }
}
