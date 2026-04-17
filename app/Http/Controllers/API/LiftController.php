<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Lift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiftController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Lift::with(['building.organisation']);

        if ($request->user()->isAdmin()) {
            $query->whereHas('building.organisation', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            });
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'building_id'      => 'required|exists:buildings,id',
            'lift_code'        => 'required|string|unique:lifts',
            'lift_type'        => 'required|in:passenger,cargo,service',
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'serial_number'    => 'nullable|string|max:100',
            'capacity'         => 'nullable|integer|min:1',
            'installation_date'=> 'nullable|date',
            'status'           => 'required|in:active,inactive,under_maintenance',
        ]);

        $building = Building::with('organisation')->findOrFail($validated['building_id']);

        if ($request->user()->isAdmin() &&
            $building->organisation->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $lift = Lift::create($validated);

        return response()->json($lift->load('building.organisation'), 201);
    }

    public function show(Request $request, Lift $lift): JsonResponse
    {
        $this->authorizeLift($request, $lift);

        $lift->load(['building.organisation', 'latestInspection.inspector', 'inspections' => function ($q) {
            $q->latest('inspection_date')->limit(5);
        }]);

        return response()->json($lift);
    }

    public function update(Request $request, Lift $lift): JsonResponse
    {
        $this->authorizeLift($request, $lift);

        $validated = $request->validate([
            'lift_code'        => 'sometimes|string|unique:lifts,lift_code,' . $lift->id,
            'lift_type'        => 'sometimes|in:passenger,cargo,service',
            'brand'            => 'nullable|string|max:100',
            'model'            => 'nullable|string|max:100',
            'serial_number'    => 'nullable|string|max:100',
            'capacity'         => 'nullable|integer|min:1',
            'installation_date'=> 'nullable|date',
            'status'           => 'sometimes|in:active,inactive,under_maintenance',
        ]);

        $lift->update($validated);

        return response()->json($lift);
    }

    public function destroy(Request $request, Lift $lift): JsonResponse
    {
        $this->authorizeLift($request, $lift);

        $lift->delete();

        return response()->json(['message' => 'Lift deleted successfully.']);
    }

    private function authorizeLift(Request $request, Lift $lift): void
    {
        $lift->load('building.organisation');
        if ($request->user()->isAdmin() &&
            $lift->building->organisation->company_id !== $request->user()->company_id) {
            abort(403, 'Forbidden.');
        }
    }
}
