<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Floor;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FloorController extends Controller
{
    public function create(Building $building): View
    {
        return view('admin.floors.create', compact('building'));
    }

    public function store(Request $request, Building $building): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $floor = $building->floors()->create([
            'name' => $data['name'],
            'level' => $data['level'] ?? 0,
        ]);

        AuditLogger::log($request->user()->id, 'floor.created', $floor, null, $floor->only(['building_id', 'name', 'level']));

        return redirect()->route('admin.buildings.index')->with('status', __('Floor created.'));
    }

    public function edit(Building $building, Floor $floor): View
    {
        abort_unless($floor->building_id === $building->id, 404);

        return view('admin.floors.edit', compact('building', 'floor'));
    }

    public function update(Request $request, Building $building, Floor $floor): RedirectResponse
    {
        abort_unless($floor->building_id === $building->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'integer', 'min:0', 'max:32767'],
        ]);

        $before = $floor->only(['name', 'level']);
        $floor->update([
            'name' => $data['name'],
            'level' => $data['level'] ?? $floor->level,
        ]);

        AuditLogger::log($request->user()->id, 'floor.updated', $floor, $before, $floor->only(['name', 'level']));

        return redirect()->route('admin.buildings.index')->with('status', __('Floor updated.'));
    }

    public function destroy(Request $request, Building $building, Floor $floor): RedirectResponse
    {
        abort_unless($floor->building_id === $building->id, 404);

        AuditLogger::log($request->user()->id, 'floor.deleted', $floor, $floor->only(['name']), null);
        $floor->delete();

        return redirect()->route('admin.buildings.index')->with('status', __('Floor removed.'));
    }
}
