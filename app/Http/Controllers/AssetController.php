<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignAssetRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetType;
use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asset::query()->with('assetType');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $assets = $query->orderBy('asset_tag')->get();
        $assetTypes = AssetType::query()->orderBy('name')->get();
        $employees = Employee::query()->where('employment_status', 'active')->orderBy('name')->get();

        return view('it.assets.index', compact('assets', 'assetTypes', 'employees'));
    }

    public function create(): View
    {
        $assetTypes = AssetType::query()->orderBy('name')->get();

        return view('it.assets.create', compact('assetTypes'));
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        $asset = Asset::query()->create(array_merge(['status' => 'in_stock'], $request->validated()));

        AuditLogger::log($request->user()->id, 'asset.created', $asset, null, $asset->toArray());

        return redirect()->route('assets.index')->with('status', __('Asset created.'));
    }

    public function assign(AssignAssetRequest $request, Asset $asset): RedirectResponse
    {
        if ($asset->status !== 'in_stock') {
            return back()->withErrors(['asset' => __('Only in-stock assets can be assigned.')]);
        }

        $active = AssetAssignment::query()
            ->where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->exists();

        if ($active) {
            return back()->withErrors(['asset' => __('Asset already has an active assignment.')]);
        }

        $assignment = AssetAssignment::query()->create([
            'asset_id' => $asset->id,
            'employee_id' => $request->integer('employee_id'),
            'assigned_at' => now(),
            'status' => 'assigned',
            'notes' => $request->input('notes'),
        ]);

        $asset->update(['status' => 'assigned']);

        AuditLogger::log($request->user()->id, 'asset.assigned', $asset, ['status' => 'in_stock'], $asset->toArray());

        return back()->with('status', __('Asset assigned.').' #'.$assignment->id);
    }

    public function returnActive(Request $request, Asset $asset): RedirectResponse
    {
        $assignment = AssetAssignment::query()
            ->where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->latest('id')
            ->first();

        if (! $assignment) {
            return back()->withErrors(['asset' => __('No active assignment for this asset.')]);
        }

        $before = $assignment->toArray();
        $assignment->update([
            'returned_at' => now(),
            'status' => 'returned',
        ]);

        $asset->update(['status' => 'in_stock']);

        AuditLogger::log($request->user()->id, 'asset.returned', $asset, $before, $assignment->toArray());

        return back()->with('status', __('Asset marked returned and back in stock.'));
    }
}
