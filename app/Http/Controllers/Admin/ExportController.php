<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(private ExportService $exportService) {}

    public function index()
    {
        return view('admin.export.index');
    }

    public function download(string $type, Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to',   now()->toDateString());
        $date = $request->input('date', now()->toDateString());
        $filters = compact('from', 'to', 'date');

        return match($type) {
            'users'          => $this->exportService->exportUsers($filters),
            'roster'         => $this->exportService->exportRosters($filters),
            'meal-plan'      => $this->exportService->exportMealPlans($filters),
            'pob'            => $this->exportService->exportPredictedPob($filters),
            'activity'       => $this->exportService->exportUserActivities($filters),
            'movement'       => $this->exportService->exportMovements($filters),
            'feedback'       => $this->exportService->exportFeedback($filters),
            'menu'           => $this->exportService->exportMenus($filters),
            'missing-roster' => $this->exportService->exportMissingRoster($filters),
            default          => abort(404, "Tipe export '$type' tidak tersedia"),
        };
    }
}
