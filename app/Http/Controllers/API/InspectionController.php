<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\Lift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Inspection::with(['lift.building.organisation', 'inspector']);

        $user = $request->user();

        if ($user->isInspector()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isAdmin()) {
            $query->whereHas('lift.building.organisation', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('lift_id')) {
            $query->where('lift_id', $request->lift_id);
        }

        return response()->json($query->latest('inspection_date')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lift_id'         => 'required|exists:lifts,id',
            'user_id'         => 'required|exists:users,id',
            'inspection_date' => 'required|date',
            'next_due_date'   => 'nullable|date|after:inspection_date',
            'inspection_type' => 'required|in:routine,annual,special,follow_up',
            'notes'           => 'nullable|string',
        ]);

        $validated['assigned_by'] = $request->user()->id;
        $validated['status'] = 'pending';

        $inspection = Inspection::create($validated);

        // Pre-populate inspection results with all active checklist items
        $items = InspectionItem::where('is_active', true)->get();
        $results = $items->map(fn ($item) => [
            'inspection_id'      => $inspection->id,
            'inspection_item_id' => $item->id,
            'result'             => null,
            'remark'             => null,
            'photo_path'         => null,
            'created_at'         => now(),
            'updated_at'         => now(),
        ])->toArray();

        $inspection->results()->insert($results);

        return response()->json($inspection->load(['lift.building.organisation', 'inspector', 'results.item']), 201);
    }

    public function show(Request $request, Inspection $inspection): JsonResponse
    {
        $this->authorizeInspection($request, $inspection);

        $inspection->load([
            'lift.building.organisation',
            'inspector',
            'assignedBy',
            'results.item',
            'report',
        ]);

        return response()->json($inspection);
    }

    public function update(Request $request, Inspection $inspection): JsonResponse
    {
        $this->authorizeInspection($request, $inspection);

        $validated = $request->validate([
            'inspection_date' => 'sometimes|date',
            'next_due_date'   => 'nullable|date',
            'inspection_type' => 'sometimes|in:routine,annual,special,follow_up',
            'status'          => 'sometimes|in:pending,in_progress,completed,failed',
            'notes'           => 'nullable|string',
        ]);

        $inspection->update($validated);

        return response()->json($inspection);
    }

    public function destroy(Request $request, Inspection $inspection): JsonResponse
    {
        $this->authorizeInspection($request, $inspection);

        $inspection->delete();

        return response()->json(['message' => 'Inspection deleted successfully.']);
    }

    private function authorizeInspection(Request $request, Inspection $inspection): void
    {
        $user = $request->user();
        if ($user->isInspector() && $inspection->user_id !== $user->id) {
            abort(403, 'Forbidden.');
        }
        if ($user->isAdmin()) {
            $inspection->load('lift.building.organisation');
            if ($inspection->lift->building->organisation->company_id !== $user->company_id) {
                abort(403, 'Forbidden.');
            }
        }
    }
}
