<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Inspection;
use App\Models\Lift;
use App\Models\Organisation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $orgQuery   = Organisation::query();
        $buildQuery = Building::query();
        $liftQuery  = Lift::query();
        $inspQuery  = Inspection::query();

        if ($user->isAdmin()) {
            $companyId = $user->company_id;
            $orgQuery->where('company_id', $companyId);
            $buildQuery->whereHas('organisation', fn ($q) => $q->where('company_id', $companyId));
            $liftQuery->whereHas('building.organisation', fn ($q) => $q->where('company_id', $companyId));
            $inspQuery->whereHas('lift.building.organisation', fn ($q) => $q->where('company_id', $companyId));
        } elseif ($user->isInspector()) {
            $inspQuery->where('user_id', $user->id);
        }

        $dueSoon = (clone $inspQuery)
            ->where('status', 'pending')
            ->whereBetween('inspection_date', [now(), now()->addDays(7)])
            ->count();

        $failedCount = (clone $inspQuery)
            ->where('status', 'failed')
            ->whereMonth('inspection_date', now()->month)
            ->count();

        $recentInspections = (clone $inspQuery)
            ->with(['lift.building.organisation', 'inspector'])
            ->latest('inspection_date')
            ->limit(5)
            ->get();

        return response()->json([
            'total_organisations' => $orgQuery->count(),
            'total_buildings'     => $buildQuery->count(),
            'total_lifts'         => $liftQuery->count(),
            'total_inspections'   => $inspQuery->count(),
            'due_soon'            => $dueSoon,
            'failed_this_month'   => $failedCount,
            'recent_inspections'  => $recentInspections,
        ]);
    }
}
