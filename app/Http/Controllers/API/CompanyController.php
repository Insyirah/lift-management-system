<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::withCount(['users', 'organisations'])->paginate(15);
        return response()->json($companies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'registration_no' => 'required|string|unique:companies',
            'address'         => 'required|string',
            'phone'           => 'required|string|max:20',
            'email'           => 'required|email|unique:companies',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company = Company::create($validated);

        return response()->json($company, 201);
    }

    public function show(Company $company): JsonResponse
    {
        $company->load(['users', 'organisations.buildings.lifts']);
        return response()->json($company);
    }

    public function update(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'registration_no' => 'sometimes|string|unique:companies,registration_no,' . $company->id,
            'address'         => 'sometimes|string',
            'phone'           => 'sometimes|string|max:20',
            'email'           => 'sometimes|email|unique:companies,email,' . $company->id,
            'is_active'       => 'sometimes|boolean',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $company->update($validated);

        return response()->json($company);
    }

    public function destroy(Company $company): JsonResponse
    {
        $company->delete();
        return response()->json(['message' => 'Company deleted successfully.']);
    }
}
