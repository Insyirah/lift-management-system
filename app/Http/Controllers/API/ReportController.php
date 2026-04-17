<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function generate(Request $request, Inspection $inspection): JsonResponse
    {
        if (!in_array($inspection->status, ['completed', 'failed'])) {
            return response()->json(['message' => 'Inspection must be completed before generating report.'], 422);
        }

        $inspection->load([
            'lift.building.organisation.company',
            'inspector',
            'assignedBy',
            'results' => fn ($q) => $q->with('item')->orderBy('inspection_item_id'),
        ]);

        $reportNumber = 'RPT-' . strtoupper(Str::random(6)) . '-' . date('Ymd');

        $pdf = Pdf::loadView('reports.inspection', [
            'inspection'   => $inspection,
            'reportNumber' => $reportNumber,
        ])->setPaper('a4', 'portrait');

        $fileName  = 'reports/' . $reportNumber . '.pdf';
        $pdfOutput = $pdf->output();

        Storage::disk('public')->put($fileName, $pdfOutput);

        $report = InspectionReport::updateOrCreate(
            ['inspection_id' => $inspection->id],
            [
                'report_number' => $reportNumber,
                'file_path'     => $fileName,
                'generated_at'  => now(),
            ]
        );

        return response()->json([
            'report'    => $report,
            'file_url'  => Storage::url($fileName),
        ]);
    }

    public function download(InspectionReport $report)
    {
        if (!Storage::disk('public')->exists($report->file_path)) {
            return response()->json(['message' => 'Report file not found.'], 404);
        }

        return Storage::disk('public')->download($report->file_path, $report->report_number . '.pdf');
    }

    public function show(InspectionReport $report): JsonResponse
    {
        return response()->json($report->load('inspection'));
    }
}
