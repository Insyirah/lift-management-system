<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Organisation::withCount(['buildings', 'lifts']);

        if ($request->user()->isAdmin()) {
            $query->where('company_id', $request->user()->company_id);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'registration_no' => 'nullable|string',
            'address'         => 'required|string',
            'contact_person'  => 'required|string|max:255',
            'contact_phone'   => 'required|string|max:20',
            'email'           => 'nullable|email',
        ]);

        $validated['company_id'] = $request->user()->isSuperAdmin()
            ? $request->input('company_id')
            : $request->user()->company_id;

        $organisation = Organisation::create($validated);

        return response()->json($organisation, 201);
    }

    public function show(Request $request, Organisation $organisation): JsonResponse
    {
        $this->authorizeCompany($request, $organisation->company_id);

        $organisation->load(['buildings.lifts', 'company']);
        return response()->json($organisation);
    }

    public function update(Request $request, Organisation $organisation): JsonResponse
    {
        $this->authorizeCompany($request, $organisation->company_id);

        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'registration_no' => 'nullable|string',
            'address'         => 'sometimes|string',
            'contact_person'  => 'sometimes|string|max:255',
            'contact_phone'   => 'sometimes|string|max:20',
            'email'           => 'nullable|email',
            'is_active'       => 'sometimes|boolean',
        ]);

        $organisation->update($validated);

        return response()->json($organisation);
    }

    public function destroy(Request $request, Organisation $organisation): JsonResponse
    {
        $this->authorizeCompany($request, $organisation->company_id);

        $organisation->delete();

        return response()->json(['message' => 'Organisation deleted successfully.']);
    }

    private function authorizeCompany(Request $request, int $companyId): void
    {
        if ($request->user()->isAdmin() && $request->user()->company_id !== $companyId) {
            abort(403, 'Forbidden.');
        }
    }
}
