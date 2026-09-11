<?php

namespace App\Services;

use App\Models\{Roster, MealPlan, MealType, MealLocation, User};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MealPlanService
{
    public function __construct(private AuditService $audit) {}

    /**
     * Generate MealPlans from a Roster entry.
     * Non-shift: Breakfast + Lunch + Dinner
     * Shift: Breakfast + Lunch + Dinner + Supper
     */
    public function generateFromRoster(Roster $roster, User $actor): void
    {
        if ($roster->status !== 'Kerja') {
            // Non-working / Dinas / Libur days → cancel all meal plans for that date
            MealPlan::where('user_id', $roster->user_id)
                ->whereDate('meal_date', $roster->roster_date)
                ->update(['status' => 'cancelled', 'cancelled' => true, 'updated_by' => $actor->id]);
            return;
        }

        $user = $roster->user ?? User::with(['breakfastLocation', 'lunchLocation', 'dinnerLocation', 'supperLocation', 'mealLocation'])->find($roster->user_id);

        // Determine Shift Type: non_shift, shift_pagi, shift_malam
        $shiftType = $roster->shift_type ?? $user->shift_type ?? ($user->is_shift ? 'shift_pagi' : 'non_shift');

        // Active meal slugs for this shift type:
        // - Shift Malam: Dinner, Supper, Breakfast (Mendapatkan jatah Supper!)
        // - Shift Pagi & Non-Shift: Breakfast, Lunch, Dinner (Tidak mendapatkan Supper)
        $activeSlugs = match($shiftType) {
            'shift_malam' => ['breakfast', 'dinner', 'supper'],
            default       => ['breakfast', 'lunch', 'dinner'],
        };

        $allMealTypes = MealType::active()->get();
        $fallbackLocation = MealLocation::active()->where('region_id', $roster->region_id)->first();

        DB::transaction(function () use ($roster, $user, $allMealTypes, $activeSlugs, $fallbackLocation, $actor) {
            foreach ($allMealTypes as $mealType) {
                $slug = strtolower($mealType->slug ?? '');
                $isActiveForShift = in_array($slug, $activeSlugs);

                if (!$isActiveForShift) {
                    // Cancel this meal if not eligible for current shift
                    MealPlan::where('user_id', $roster->user_id)
                        ->whereDate('meal_date', $roster->roster_date)
                        ->where('meal_type_id', $mealType->id)
                        ->update(['status' => 'cancelled', 'cancelled' => true, 'updated_by' => $actor->id]);
                    continue;
                }

                // Determine per-meal location preference
                $preferredLocId = match($slug) {
                    'breakfast' => $user->breakfast_location_id,
                    'lunch'     => $user->lunch_location_id,
                    'dinner'    => $user->dinner_location_id,
                    'supper'    => $user->supper_location_id,
                    default     => null,
                } ?? $user->meal_location_id;

                $targetLoc = $preferredLocId ? MealLocation::find($preferredLocId) : null;
                if (!$targetLoc || !$targetLoc->is_active) {
                    $targetLoc = $fallbackLocation;
                }

                MealPlan::updateOrCreate(
                    [
                        'user_id'      => $roster->user_id,
                        'meal_date'    => $roster->roster_date,
                        'meal_type_id' => $mealType->id,
                    ],
                    [
                        'region_id'       => $targetLoc?->region_id ?? $roster->region_id,
                        'meal_location_id'=> $targetLoc?->id,
                        'status'          => 'active',
                        'source'          => 'roster',
                        'cancelled'       => false,
                        'outside_meal'    => false,
                        'roster_id'       => $roster->id,
                        'updated_by'      => $actor->id,
                    ]
                );
            }
        });

        $this->audit->log('generate_meal_plan', 'meal_plan',
            "Meal plan dibuat dari roster {$roster->roster_date->format('d/m/Y')} ({$shiftType}) untuk {$user->name}",
            $roster, [], [], $actor->id
        );
    }

    /**
     * Apply approved movement to meal plans.
     * Move specific meal types from old region to new region.
     */
    public function applyMovement(\App\Models\Movement $movement): void
    {
        DB::transaction(function () use ($movement) {
            foreach ($movement->people as $person) {
                $mealTypeIds = $person->meal_type_ids ?? MealType::active()->pluck('id')->toArray();

                foreach ($mealTypeIds as $mealTypeId) {
                    $mealPlan = MealPlan::where('user_id', $person->user_id)
                        ->whereDate('meal_date', $movement->movement_date)
                        ->where('meal_type_id', $mealTypeId)
                        ->first();

                    if ($mealPlan) {
                        $oldValues = $mealPlan->toArray();
                        $newLocation = MealLocation::active()
                            ->where('region_id', $movement->to_region_id)
                            ->first();

                        $mealPlan->update([
                            'region_id'       => $movement->to_region_id,
                            'meal_location_id'=> $newLocation?->id,
                            'source'          => 'movement',
                            'status'          => 'active',
                            'cancelled'       => false,
                            'outside_meal'    => false,
                            'updated_by'      => $movement->approved_by,
                        ]);

                        $this->audit->log('apply_movement', 'meal_plan',
                            "Meal plan dipindah ke wilayah {$movement->toRegion->name}",
                            $mealPlan, $oldValues, $mealPlan->fresh()->toArray(),
                            $movement->approved_by
                        );
                    }
                }
            }
        });
    }

    /**
     * Apply approved outside meal — mark meal plan as outside_meal.
     */
    public function applyOutsideMeal(\App\Models\OutsideMeal $outsideMeal): void
    {
        DB::transaction(function () use ($outsideMeal) {
            foreach ($outsideMeal->people as $person) {
                $mealPlan = MealPlan::where('user_id', $person->user_id)
                    ->whereDate('meal_date', $outsideMeal->meal_date)
                    ->where('meal_type_id', $outsideMeal->meal_type_id)
                    ->first();

                if ($mealPlan) {
                    $mealPlan->update([
                        'status'       => 'outside_meal',
                        'outside_meal' => true,
                        'updated_by'   => $outsideMeal->approved_by,
                    ]);
                }
            }
        });
    }

    /**
     * Cancel a meal plan.
     */
    public function cancel(MealPlan $mealPlan, string $reason, User $actor): void
    {
        $oldValues = $mealPlan->toArray();

        DB::transaction(function () use ($mealPlan, $reason, $actor) {
            $mealPlan->update([
                'status'     => 'cancelled',
                'cancelled'  => true,
                'updated_by' => $actor->id,
            ]);

            \App\Models\MealCancellation::create([
                'user_id'      => $mealPlan->user_id,
                'meal_plan_id' => $mealPlan->id,
                'meal_date'    => $mealPlan->meal_date,
                'meal_type_id' => $mealPlan->meal_type_id,
                'reason'       => $reason,
            ]);
        });

        $this->audit->log('cancel_meal', 'meal_plan',
            "Meal plan dibatalkan: {$mealPlan->mealType->name} tanggal {$mealPlan->meal_date->format('d/m/Y')}",
            $mealPlan, $oldValues, $mealPlan->fresh()->toArray(), $actor->id
        );
    }

    /**
     * Automatically sync all future rosters and meal plans when user changes homebase/identity.
     */
    public function syncFutureMealPlansForUser(User $user, User $actor): void
    {
        $tomorrow = Carbon::tomorrow();

        // 1. Ensure user has a valid default meal location for new homebase region
        if (!$user->meal_location_id || $user->mealLocation?->region_id != $user->homebase_region_id) {
            $defaultMh = MealLocation::where('region_id', $user->homebase_region_id)
                ->where('is_active', true)
                ->where('name', 'like', '%Mess Hall%')
                ->first();

            if ($defaultMh) {
                $user->update(['meal_location_id' => $defaultMh->id]);
                $user->refresh();
            }
        }

        // 2. Update future working rosters to the new homebase region
        Roster::where('user_id', $user->id)
            ->whereDate('roster_date', '>=', $tomorrow)
            ->update([
                'region_id'  => $user->homebase_region_id,
                'updated_by' => $actor->id,
            ]);

        // 3. Regenerate all future meal plans based on updated user settings & rosters
        $futureRosters = Roster::where('user_id', $user->id)
            ->whereDate('roster_date', '>=', $tomorrow)
            ->get();

        foreach ($futureRosters as $roster) {
            $this->generateFromRoster($roster, $actor);
        }

        // 4. Also update any active meal plans directly from tomorrow onwards
        $targetLocId = $user->meal_location_id;
        if (!$targetLocId) {
            $targetLocId = MealLocation::where('region_id', $user->homebase_region_id)->where('is_active', true)->value('id');
        }

        MealPlan::where('user_id', $user->id)
            ->whereDate('meal_date', '>=', $tomorrow)
            ->where('outside_meal', false)
            ->where('status', 'active')
            ->update([
                'region_id'        => $user->homebase_region_id,
                'meal_location_id' => $targetLocId,
                'updated_by'       => $actor->id,
            ]);

        $this->audit->log('sync_future_meal_plans', 'meal_plan',
            "Rencana makan besok dan hari seterusnya untuk {$user->name} disinkronkan ke wilayah {$user->homebaseRegion?->name}",
            $user, [], [], $actor->id
        );
    }
}
