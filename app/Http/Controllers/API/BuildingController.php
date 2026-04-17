<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Building::with('organisation')->withCount('lifts');

        if ($request->user()->isAdmin()) {
            $query->whereHas('organisation', function ($q) use ($request) {
                $q->where('company_id', $request->user()->company_id);
            });
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'name'            => 'required|string|max:255',
            'address'         => 'required|string',
            'number_of_floors'=> 'required|integer|min:1',
            'year_built'      => 'nullable|integer|min:1800|max:' . date('Y'),
        ]);

        $organisation = Organisation::findOrFail($validated['organisation_id']);

        if ($request->user()->isAdmin() && $organisation->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $building = Building::create($validated);

        return response()->json($building->load('organisation'), 201);
    }

    public function show(Request $request, Building $building): JsonResponse
    {
        $this->authorizeBuilding($request, $building);

        $building->load(['organisation', 'lifts']);
        return response()->json($building);
    }

    public function update(Request $request, Building $building): JsonResponse
    {
        $this->authorizeBuilding($request, $building);

        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'address'          => 'sometimes|string',
            'number_of_floors' => 'sometimes|integer|min:1',
            'year_built'       => 'nullable|integer|min:1800|max:' . date('Y'),
            'is_active'        => 'sometimes|boolean',
        ]);

        $building->update($validated);

        return response()->json($building);
    }

    public function destroy(Request $request, Building $building): JsonResponse
    {
        $this->authorizeBuilding($request, $building);

        $building->delete();

        return response()->json(['message' => 'Building deleted successfully.']);
    }

    private function authorizeBuilding(Request $request, Building $building): void
    {
        if ($request->user()->isAdmin() &&
            $building->organisation->company_id !== $request->user()->company_id) {
            abort(403, 'Forbidden.');
        }
    }
}
