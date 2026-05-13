<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Desk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeskApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Desk::query()->with(['floor.building']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $perPage = min((int) $request->get('per_page', 25), 100);

        return response()->json($query->orderBy('id')->paginate($perPage));
    }

    public function show(Desk $desk): JsonResponse
    {
        $desk->load(['floor.building', 'allocations.employee']);

        return response()->json($desk);
    }
}
