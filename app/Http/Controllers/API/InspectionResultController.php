<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InspectionResultController extends Controller
{
    public function update(Request $request, Inspection $inspection, InspectionResult $result): JsonResponse
    {
        // Verify result belongs to this inspection
        if ($result->inspection_id !== $inspection->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'result' => 'required|in:pass,fail,na',
            'remark' => 'nullable|string|max:1000',
        ]);

        $result->update($validated);

        return response()->json($result->load('item'));
    }

    public function uploadPhoto(Request $request, Inspection $inspection, InspectionResult $result): JsonResponse
    {
        if ($result->inspection_id !== $inspection->id) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($result->photo_path) {
            Storage::disk('public')->delete($result->photo_path);
        }

        $path = $request->file('photo')->store(
            'inspections/' . $inspection->id . '/photos',
            'public'
        );

        $result->update(['photo_path' => $path]);

        return response()->json(['photo_path' => $path, 'photo_url' => Storage::url($path)]);
    }

    public function bulkUpdate(Request $request, Inspection $inspection): JsonResponse
    {
        $request->validate([
            'results'                  => 'required|array',
            'results.*.id'             => 'required|exists:inspection_results,id',
            'results.*.result'         => 'required|in:pass,fail,na',
            'results.*.remark'         => 'nullable|string|max:1000',
        ]);

        foreach ($request->results as $data) {
            InspectionResult::where('id', $data['id'])
                ->where('inspection_id', $inspection->id)
                ->update([
                    'result' => $data['result'],
                    'remark' => $data['remark'] ?? null,
                ]);
        }

        // Mark inspection as in_progress if it was pending
        if ($inspection->status === 'pending') {
            $inspection->update(['status' => 'in_progress']);
        }

        return response()->json(['message' => 'Results saved successfully.']);
    }

    public function complete(Request $request, Inspection $inspection): JsonResponse
    {
        $request->validate([
            'next_due_date' => 'nullable|date|after:today',
            'notes'         => 'nullable|string',
        ]);

        $allAnswered = $inspection->results()
            ->whereNull('result')
            ->doesntExist();

        if (!$allAnswered) {
            return response()->json(['message' => 'All checklist items must be answered before completing.'], 422);
        }

        $hasFail = $inspection->results()->where('result', 'fail')->exists();

        $inspection->update([
            'status'        => $hasFail ? 'failed' : 'completed',
            'next_due_date' => $request->next_due_date,
            'notes'         => $request->notes,
        ]);

        return response()->json($inspection->load(['lift', 'inspector', 'results.item']));
    }
}
