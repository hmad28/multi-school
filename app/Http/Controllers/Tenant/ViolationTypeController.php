<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Violations\StoreViolationTypeRequest;
use App\Http\Requests\Violations\UpdateViolationTypeRequest;
use App\Models\ViolationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ViolationTypeController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('violations.manage-types'), 403);

        return Inertia::render('Violations/Types/Index', [
            'types' => ViolationType::query()
                ->when($request->string('category')->toString(), fn ($query, string $category) => $query->where('category', $category))
                ->orderBy('sort_order')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('category'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('violations.manage-types'), 403);

        return Inertia::render('Violations/Types/Create');
    }

    public function store(StoreViolationTypeRequest $request): RedirectResponse
    {
        ViolationType::create($request->validated());

        return redirect()->route('tenant.violation-types.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Jenis pelanggaran berhasil dibuat.');
    }

    public function edit(Request $request, ViolationType $violationType): Response
    {
        abort_unless($request->user()->can('violations.manage-types'), 403);

        return Inertia::render('Violations/Types/Edit', ['type' => $violationType]);
    }

    public function update(UpdateViolationTypeRequest $request, ViolationType $violationType): RedirectResponse
    {
        $violationType->update($request->validated());

        return redirect()->route('tenant.violation-types.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy(Request $request, ViolationType $violationType): RedirectResponse
    {
        abort_unless($request->user()->can('violations.manage-types'), 403);

        $violationType->delete();

        return redirect()->route('tenant.violation-types.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }
}
