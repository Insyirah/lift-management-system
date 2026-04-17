<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BuildingController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\InspectionController;
use App\Http\Controllers\API\InspectionItemController;
use App\Http\Controllers\API\InspectionResultController;
use App\Http\Controllers\API\LiftController;
use App\Http\Controllers\API\OrganisationController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Inspection items (checklist templates) — super_admin and admin manage
    Route::apiResource('inspection-items', InspectionItemController::class)->except(['show']);

    // Super Admin only
    Route::middleware('role:super_admin')->group(function () {
        Route::apiResource('companies', CompanyController::class);
    });

    // Super Admin + Admin
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('organisations', OrganisationController::class);
        Route::apiResource('buildings', BuildingController::class);
        Route::apiResource('lifts', LiftController::class);

        // Schedule inspections
        Route::apiResource('inspections', InspectionController::class)->except(['show']);

        // Reports
        Route::post('/inspections/{inspection}/generate-report', [ReportController::class, 'generate']);
        Route::get('/reports/{report}', [ReportController::class, 'show']);
        Route::get('/reports/{report}/download', [ReportController::class, 'download']);
    });

    // All authenticated users (super_admin + admin + inspector)
    Route::get('/inspections/{inspection}', [InspectionController::class, 'show']);

    // Inspection results (inspectors and admins fill these)
    Route::middleware('role:super_admin,admin,inspector')->group(function () {
        Route::put(
            '/inspections/{inspection}/results/{result}',
            [InspectionResultController::class, 'update']
        );
        Route::post(
            '/inspections/{inspection}/results/{result}/photo',
            [InspectionResultController::class, 'uploadPhoto']
        );
        Route::post(
            '/inspections/{inspection}/results/bulk',
            [InspectionResultController::class, 'bulkUpdate']
        );
        Route::post(
            '/inspections/{inspection}/complete',
            [InspectionResultController::class, 'complete']
        );
    });
});
