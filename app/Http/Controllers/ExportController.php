<?php

namespace App\Http\Controllers;

use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(private ExportService $service) {}

    public function users(Request $request)    { return $this->service->exportUsers($request->all()); }
    public function rosters(Request $request)  { return $this->service->exportRosters($request->all()); }
    public function mealPlans(Request $request){ return $this->service->exportMealPlans($request->all()); }
    public function predictedPob(Request $request){ return $this->service->exportPredictedPob($request->all()); }
    public function userActivities(Request $request){ return $this->service->exportUserActivities($request->all()); }
    public function movements(Request $request)  { return $this->service->exportRosters($request->all()); }
    public function outsideMeals(Request $request){ return $this->service->exportRosters($request->all()); }
    public function cancellations(Request $request){ return $this->service->exportRosters($request->all()); }
}
