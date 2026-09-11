<?php

namespace App\Http\Controllers;

use App\Models\{MealPlan, MealLocation, MealType};
use App\Services\{PlanningCutoffService, AuditService};
use Carbon\Carbon;
use Illuminate\Http\Request;

class MealPlanController extends Controller
{
    public function __construct(
        private PlanningCutoffService $cutoff,
        private AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $from = $request->filled('from') ? Carbon::parse($request->from) : $today;
        $to   = $request->filled('to')   ? Carbon::parse($request->to)   : $today->copy()->addDays(6);

        $plans = MealPlan::where('user_id', $user->id)
            ->whereBetween('meal_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with(['mealType', 'region', 'mealLocation'])
            ->orderBy('meal_date')
            ->orderBy('meal_type_id')
            ->paginate(30)
            ->withQueryString();

        $deliveryLocations = MealLocation::where('is_active', true)
            ->with('region')
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();

        return view('meal-plan.index', compact('plans', 'deliveryLocations', 'from', 'to', 'today'));
    }

    /**
     * Update delivery location or toggle outside meal for a specific meal plan.
     */
    public function updateLocation(Request $request, MealPlan $mealPlan)
    {
        $user = $request->user();
        abort_unless($mealPlan->user_id === $user->id || $user->isGS() || $user->isSysAdmin(), 403);

        $isClosed = $this->cutoff->isClosed($mealPlan->meal_date);
        if ($isClosed && !$user->isGS() && !$user->isSysAdmin()) {
            return back()->with('error', 'Batas waktu cut-off (H-1 pukul 19:00 WIB) telah lewat. Hubungi GS untuk perubahan lokasi.');
        }

        $validated = $request->validate([
            'meal_location_id' => 'nullable|exists:meal_locations,id',
            'is_outside_meal'  => 'nullable|boolean',
        ]);

        $oldValues = $mealPlan->toArray();

        $isOutside = $request->boolean('is_outside_meal');
        $locationId = $validated['meal_location_id'] ?? $mealPlan->meal_location_id;
        $location = $locationId ? MealLocation::find($locationId) : null;
        $regionId = $location ? $location->region_id : $mealPlan->region_id;

        $mealPlan->update([
            'region_id'        => $regionId,
            'meal_location_id' => $locationId,
            'outside_meal'     => $isOutside,
            'updated_by'       => $user->id,
        ]);

        $locName = $isOutside ? 'Outside Meal (Makan di Luar)' : ($location?->name ?? 'Mess Hall');

        $this->audit->log('update_meal_location', 'meal_plan',
            "Lokasi makan {$mealPlan->mealType?->name} tanggal {$mealPlan->meal_date->format('d/m/Y')} diubah menjadi: {$locName}",
            $mealPlan, $oldValues, $mealPlan->fresh()->toArray(), $user->id
        );

        return back()->with('success', "Titik makan/pengantaran untuk {$mealPlan->mealType?->name} tanggal {$mealPlan->meal_date->translatedFormat('d M Y')} berhasil diubah ke: <strong>{$locName}</strong>");
    }
}
