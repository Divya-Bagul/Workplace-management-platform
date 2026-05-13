<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(): View
    {
        $buildings = Building::query()
            ->with(['floors' => fn ($q) => $q->orderBy('level')])
            ->withCount('floors')
            ->orderBy('name')
            ->get();

        return view('admin.buildings.index', compact('buildings'));
    }

    public function create(): View
    {
        return view('admin.buildings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $building = Building::query()->create($data);

        AuditLogger::log($request->user()->id, 'building.created', $building, null, $building->only(['name', 'address']));

        return redirect()->route('admin.buildings.index')->with('status', __('Building created.'));
    }

    public function edit(Building $building): View
    {
        return view('admin.buildings.edit', compact('building'));
    }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $before = $building->only(['name', 'address']);
        $building->update($data);

        AuditLogger::log($request->user()->id, 'building.updated', $building, $before, $building->only(['name', 'address']));

        return redirect()->route('admin.buildings.index')->with('status', __('Building updated.'));
    }

    public function destroy(Request $request, Building $building): RedirectResponse
    {
        AuditLogger::log($request->user()->id, 'building.deleted', $building, $building->only(['name']), null);
        $building->delete();

        return redirect()->route('admin.buildings.index')->with('status', __('Building removed.'));
    }
}
