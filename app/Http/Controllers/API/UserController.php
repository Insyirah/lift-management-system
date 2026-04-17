<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('company');

        // Super admin sees all; admin sees only their company's users
        if ($request->user()->isAdmin()) {
            $query->where('company_id', $request->user()->company_id)
                  ->whereIn('role', ['admin', 'inspector']);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|string|min:8',
            'role'       => 'required|in:admin,inspector' . ($isSuperAdmin ? ',super_admin' : ''),
            'company_id' => $isSuperAdmin ? 'required|exists:companies,id' : 'nullable',
            'phone'      => 'nullable|string|max:20',
            'cert_number'=> 'nullable|string',
            'cert_expiry'=> 'nullable|date',
        ]);

        // Admins can only create users for their own company
        if (!$isSuperAdmin) {
            $validated['company_id'] = $request->user()->company_id;
        }

        $user = User::create($validated);

        return response()->json($user->load('company'), 201);
    }

    public function show(Request $request, User $user): JsonResponse
    {
        // Admins can only view users within their company
        if ($request->user()->isAdmin() && $user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($user->load('company'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        if ($request->user()->isAdmin() && $user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'email'       => 'sometimes|email|unique:users,email,' . $user->id,
            'password'    => 'sometimes|string|min:8',
            'phone'       => 'nullable|string|max:20',
            'cert_number' => 'nullable|string',
            'cert_expiry' => 'nullable|date',
            'is_active'   => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json($user->load('company'));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->isAdmin() && $user->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }
}
