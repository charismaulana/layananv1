<?php

namespace App\Services;

use App\Models\{MealPlan, Region, MealType, MealLocation};
use Carbon\Carbon;

class PredictedPobService
{
    /**
     * Calculate Predicted POB.
     * Formula: count of Final Active Meal Plans (status=active, cancelled=false, outside_meal=false)
     * Grouped by: date, region, location, meal_type
     */
    public function calculate(Carbon $date, ?int $regionId = null, ?int $mealTypeId = null, ?int $locationId = null): array
    {
        $query = MealPlan::finalActive()
            ->whereDate('meal_date', $date)
            ->with(['user.workerStatus', 'user.department', 'region', 'mealType', 'mealLocation']);

        if ($regionId) $query->where('region_id', $regionId);
        if ($mealTypeId) $query->where('meal_type_id', $mealTypeId);
        if ($locationId) $query->where('meal_location_id', $locationId);

        $plans = $query->get();

        return $plans->groupBy(fn($p) =>
            "{$p->region_id}_{$p->meal_location_id}_{$p->meal_type_id}"
        )->map(fn($group) => [
            'region'        => $group->first()->region,
            'meal_location' => $group->first()->mealLocation,
            'meal_type'     => $group->first()->mealType,
            'total_pax'     => $group->count(),
            'plans'         => $group,
        ])->values()->toArray();
    }

    /**
     * Summary table: region × meal_type → pax count
     */
    public function summaryTable(Carbon $date): array
    {
        $regions    = Region::where('is_active', true)->get();
        $mealTypes  = MealType::active()->get();

        $counts = MealPlan::finalActive()
            ->whereDate('meal_date', $date)
            ->selectRaw('region_id, meal_type_id, COUNT(*) as pax')
            ->groupBy('region_id', 'meal_type_id')
            ->get()
            ->keyBy(fn($r) => "{$r->region_id}_{$r->meal_type_id}");

        $visitorMeals = \App\Models\VisitorMeal::whereDate('meal_date', $date)
            ->where('status', 'confirmed')
            ->get();

        $table = [];
        foreach ($regions as $region) {
            $row = ['region' => $region, 'totals' => []];
            $regVisitors = $visitorMeals->where('region_id', $region->id);

            foreach ($mealTypes as $mealType) {
                $key = "{$region->id}_{$mealType->id}";
                $planPax = $counts->get($key)?->pax ?? 0;

                $visitorPax = 0;
                $slug = strtolower($mealType->slug);
                if ($slug === 'breakfast') $visitorPax = $regVisitors->where('has_breakfast', true)->sum('pax_count');
                elseif ($slug === 'lunch') $visitorPax = $regVisitors->where('has_lunch', true)->sum('pax_count');
                elseif ($slug === 'dinner') $visitorPax = $regVisitors->where('has_dinner', true)->sum('pax_count');
                elseif ($slug === 'supper') $visitorPax = $regVisitors->where('has_supper', true)->sum('pax_count');

                $row['totals'][$mealType->slug] = $planPax + $visitorPax;
            }
            $row['grand_total'] = array_sum($row['totals']);
            $table[] = $row;
        }

        return [
            'meal_types' => $mealTypes,
            'rows'       => $table,
        ];
    }
}
