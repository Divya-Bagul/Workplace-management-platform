<?php

namespace App\Http\Controllers;

use App\Models\Desk;
use App\Models\Floor;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Desk::query()->with(['floor.building']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $desks = $query->orderBy('floor_id')->orderBy('code')->get();

        $counts = [
            'available' => Desk::query()->where('status', 'available')->count(),
            'occupied' => Desk::query()->where('status', 'occupied')->count(),
            'reserved' => Desk::query()->where('status', 'reserved')->count(),
        ];

        return view('workspace.desks.index', compact('desks', 'counts'));
    }

    public function show(Desk $desk): View
    {
        $desk->load([
            'floor.building',
            'allocations' => fn ($q) => $q->with('employee')->orderByDesc('valid_from'),
        ]);

        return view('workspace.desks.show', compact('desk'));
    }

    public function create(): View
    {
        $floors = Floor::query()->with('building')->orderBy('building_id')->orderBy('level')->get();

        return view('workspace.desks.create', compact('floors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'floor_id' => ['required', 'exists:floors,id'],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('desks')->where(fn ($q) => $q->where('floor_id', $request->integer('floor_id'))),
            ],
            'status' => ['nullable', 'in:available,occupied,reserved'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $desk = Desk::query()->create([
            'floor_id' => $data['floor_id'],
            'code' => $data['code'],
            'status' => $data['status'] ?? 'available',
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLogger::log($request->user()->id, 'desk.created', $desk, null, $desk->toArray());

        return redirect()->route('desks.index')->with('status', __('Desk created.'));
    }

    public function edit(Desk $desk): View
    {
        $floors = Floor::query()->with('building')->orderBy('building_id')->orderBy('level')->get();

        return view('workspace.desks.edit', compact('desk', 'floors'));
    }

    public function update(Request $request, Desk $desk): RedirectResponse
    {
        $data = $request->validate([
            'floor_id' => ['required', 'exists:floors,id'],
            'code' => [
                'required',
                'string',
                'max:64',
                Rule::unique('desks')->where(fn ($q) => $q->where('floor_id', $request->integer('floor_id')))->ignore($desk->id),
            ],
            'status' => ['required', 'in:available,occupied,reserved'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = $desk->toArray();
        $desk->update($data);

        AuditLogger::log($request->user()->id, 'desk.updated', $desk, $before, $desk->toArray());

        return redirect()->route('desks.show', $desk)->with('status', __('Desk updated.'));
    }

    public function destroy(Request $request, Desk $desk): RedirectResponse
    {
        if ($desk->status === 'occupied') {
            return back()->withErrors(['desk' => __('Cannot delete an occupied desk.')]);
        }

        AuditLogger::log($request->user()->id, 'desk.deleted', $desk, $desk->toArray(), null);
        $desk->delete();

        return redirect()->route('desks.index')->with('status', __('Desk removed.'));
    }
}
