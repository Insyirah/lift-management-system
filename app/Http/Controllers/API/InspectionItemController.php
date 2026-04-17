<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\InspectionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InspectionItemController extends Controller
{
    public function index(): JsonResponse
    {
        $items = InspectionItem::where('is_active', true)
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $item = InspectionItem::create($validated);

        return response()->json($item, 201);
    }

    public function update(Request $request, InspectionItem $inspectionItem): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'category'    => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'sometimes|boolean',
        ]);

        $inspectionItem->update($validated);

        return response()->json($inspectionItem);
    }

    public function destroy(InspectionItem $inspectionItem): JsonResponse
    {
        $inspectionItem->delete();
        return response()->json(['message' => 'Checklist item deleted successfully.']);
    }
}
