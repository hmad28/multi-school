<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CharacterPoints\StoreCharacterPointTypeRequest;
use App\Http\Requests\CharacterPoints\UpdateCharacterPointTypeRequest;
use App\Models\CharacterPointType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CharacterPointTypeController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('character-points.manage-types'), 403);

        return Inertia::render('CharacterPoints/Types/Index', [
            'types' => CharacterPointType::query()
                ->when($request->string('category')->toString(), fn ($query, string $category) => $query->where('category', $category))
                ->orderBy('sort_order')
                ->paginate(15)
                ->withQueryString(),
            'filters' => $request->only('category'),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('character-points.manage-types'), 403);

        return Inertia::render('CharacterPoints/Types/Create');
    }

    public function store(StoreCharacterPointTypeRequest $request): RedirectResponse
    {
        CharacterPointType::create($request->validated());

        return redirect()->route('tenant.character-point-types.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Jenis poin karakter berhasil dibuat.');
    }

    public function edit(Request $request, CharacterPointType $characterPointType): Response
    {
        abort_unless($request->user()->can('character-points.manage-types'), 403);

        return Inertia::render('CharacterPoints/Types/Edit', ['type' => $characterPointType]);
    }

    public function update(UpdateCharacterPointTypeRequest $request, CharacterPointType $characterPointType): RedirectResponse
    {
        $characterPointType->update($request->validated());

        return redirect()->route('tenant.character-point-types.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Jenis poin karakter berhasil diperbarui.');
    }

    public function destroy(Request $request, CharacterPointType $characterPointType): RedirectResponse
    {
        abort_unless($request->user()->can('character-points.manage-types'), 403);

        $characterPointType->delete();

        return redirect()->route('tenant.character-point-types.index', ['tenant' => $request->route('tenant')])
            ->with('success', 'Jenis poin karakter berhasil dihapus.');
    }
}
