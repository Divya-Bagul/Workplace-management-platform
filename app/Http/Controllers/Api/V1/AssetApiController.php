<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignAssetRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Asset::query()->with('assetType');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = min((int) $request->get('per_page', 25), 100);

        return response()->json($query->orderBy('asset_tag')->paginate($perPage));
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['assetType', 'assignments.employee']);

        return response()->json($asset);
    }

    public function store(StoreAssetRequest $request): JsonResponse
    {
        $data = array_merge(['status' => 'in_stock'], $request->validated());
        $asset = Asset::query()->create($data);

        AuditLogger::log($request->user()->id, 'asset.created', $asset, null, $asset->toArray());

        return response()->json($asset->load('assetType'), 201);
    }

    public function assign(AssignAssetRequest $request, Asset $asset): JsonResponse
    {
        if ($asset->status !== 'in_stock') {
            return response()->json(['message' => 'Only in-stock assets can be assigned.'], 422);
        }

        $active = AssetAssignment::query()
            ->where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->exists();

        if ($active) {
            return response()->json(['message' => 'Asset already has an active assignment.'], 422);
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

        return response()->json(['asset' => $asset->fresh('assetType'), 'assignment' => $assignment]);
    }

    public function returnActive(Request $request, Asset $asset): JsonResponse
    {
        $assignment = AssetAssignment::query()
            ->where('asset_id', $asset->id)
            ->whereNull('returned_at')
            ->latest('id')
            ->first();

        if (! $assignment) {
            return response()->json(['message' => 'No active assignment for this asset.'], 422);
        }

        $before = $assignment->toArray();
        $assignment->update([
            'returned_at' => now(),
            'status' => 'returned',
        ]);

        $asset->update(['status' => 'in_stock']);
        AuditLogger::log($request->user()->id, 'asset.returned', $asset, $before, $assignment->toArray());

        return response()->json(['asset' => $asset->fresh('assetType'), 'assignment' => $assignment]);
    }
}
