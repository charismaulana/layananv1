<?php

namespace App\Http\Controllers;

use App\Models\MealLocation;
use App\Models\VisitorMeal;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VisitorMealController extends Controller
{
    /**
     * Store new Visitor Meal(s) with support for date ranges and per-day meal details.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isSysAdmin(), 403, 'Hanya role GS yang memiliki akses menambahkan visitor.');

        $validated = $request->validate([
            'visitor_name'     => 'required|string|max:255',
            'institution'      => 'nullable|string|max:255',
            'start_date'       => 'required|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'meal_location_id' => 'required|exists:meal_locations,id',
            'pax_count'        => 'required|integer|min:1|max:100',
            'has_breakfast'    => 'nullable|boolean',
            'has_lunch'        => 'nullable|boolean',
            'has_dinner'       => 'nullable|boolean',
            'has_supper'       => 'nullable|boolean',
            'notes'            => 'nullable|string|max:500',
            'days'             => 'nullable|array',
            'allow_cutoff'     => 'nullable|boolean',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate   = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy();

        // 1. Validate Cut-Off (H-1 pukul 19:00 WIB)
        $now = Carbon::now();
        $cutoffPassedDates = [];

        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $d) {
            $cutoffDateTime = $d->copy()->subDay()->setTime(19, 0, 0);
            if ($now->greaterThanOrEqualTo($cutoffDateTime)) {
                $cutoffPassedDates[] = $d->translatedFormat('d F Y');
            }
        }

        if (!empty($cutoffPassedDates) && !$request->boolean('allow_cutoff')) {
            $datesStr = implode(', ', $cutoffPassedDates);
            return back()->with('error', "Gagal mendaftarkan visitor. Batas cut-off (H-1 pukul 19:00 WIB) telah lewat untuk tanggal: {$datesStr}.");
        }

        $mealLocation = MealLocation::findOrFail($validated['meal_location_id']);
        $groupId = (string) Str::uuid();
        $createdCount = 0;

        foreach ($period as $d) {
            $dateStr = $d->format('Y-m-d');
            $dayData = $request->input("days.{$dateStr}");

            if (is_array($dayData)) {
                $hasBf = !empty($dayData['has_breakfast']);
                $hasLu = !empty($dayData['has_lunch']);
                $hasDi = !empty($dayData['has_dinner']);
                $hasSu = !empty($dayData['has_supper']);
                $pax   = !empty($dayData['pax_count']) ? (int)$dayData['pax_count'] : (int)$validated['pax_count'];
            } else {
                $hasBf = $request->boolean('has_breakfast');
                $hasLu = $request->boolean('has_lunch');
                $hasDi = $request->boolean('has_dinner');
                $hasSu = $request->boolean('has_supper');
                $pax   = (int)$validated['pax_count'];
            }

            // If no meal selected for this day, skip this day
            if (!$hasBf && !$hasLu && !$hasDi && !$hasSu) {
                continue;
            }

            VisitorMeal::create([
                'group_id'         => $groupId,
                'visitor_name'     => trim($validated['visitor_name']),
                'institution'      => trim($validated['institution'] ?? 'Visitor / Tamu'),
                'meal_date'        => $d,
                'region_id'        => $mealLocation->region_id,
                'meal_location_id' => $mealLocation->id,
                'pax_count'        => $pax,
                'has_breakfast'    => $hasBf,
                'has_lunch'        => $hasLu,
                'has_dinner'       => $hasDi,
                'has_supper'       => $hasSu,
                'notes'            => $validated['notes'] ?? null,
                'created_by'       => auth()->id(),
                'status'           => 'confirmed',
            ]);

            $createdCount++;
        }

        if ($createdCount === 0) {
            return back()->with('error', 'Pilih minimal satu waktu makan (B\'fast, Lunch, Dinner, atau Supper) untuk visitor.');
        }

        $locName = $mealLocation->name;
        $rangeInfo = $startDate->eq($endDate)
            ? $startDate->translatedFormat('d F Y')
            : "{$startDate->translatedFormat('d F Y')} s/d {$endDate->translatedFormat('d F Y')}";

        return back()->with('success', "Visitor \"{$validated['visitor_name']}\" ({$createdCount} hari, {$locName}) berhasil didaftarkan untuk periode {$rangeInfo}.");
    }

    /**
     * Update an existing Visitor Meal record (supports updating date range).
     */
    public function update(Request $request, VisitorMeal $visitorMeal)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isSysAdmin(), 403, 'Hanya role GS yang memiliki akses mengedit visitor.');

        $validated = $request->validate([
            'visitor_name'     => 'required|string|max:255',
            'institution'      => 'nullable|string|max:255',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'meal_date'        => 'nullable|date',
            'meal_location_id' => 'required|exists:meal_locations,id',
            'pax_count'        => 'required|integer|min:1|max:100',
            'has_breakfast'    => 'nullable|boolean',
            'has_lunch'        => 'nullable|boolean',
            'has_dinner'       => 'nullable|boolean',
            'has_supper'       => 'nullable|boolean',
            'notes'            => 'nullable|string|max:500',
            'days'             => 'nullable|array',
            'allow_cutoff'     => 'nullable|boolean',
        ]);

        $startDate = !empty($validated['start_date']) 
            ? Carbon::parse($validated['start_date']) 
            : (!empty($validated['meal_date']) ? Carbon::parse($validated['meal_date']) : $visitorMeal->meal_date);
            
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy();

        // 1. Validate Cut-Off (H-1 pukul 19:00 WIB)
        $now = Carbon::now();
        $cutoffPassedDates = [];

        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $d) {
            $cutoffDateTime = $d->copy()->subDay()->setTime(19, 0, 0);
            if ($now->greaterThanOrEqualTo($cutoffDateTime)) {
                $cutoffPassedDates[] = $d->translatedFormat('d F Y');
            }
        }

        if (!empty($cutoffPassedDates) && !$request->boolean('allow_cutoff')) {
            $datesStr = implode(', ', $cutoffPassedDates);
            return back()->with('error', "Gagal mengedit visitor. Batas cut-off (H-1 pukul 19:00 WIB) telah lewat untuk tanggal: {$datesStr}.");
        }

        $mealLocation = MealLocation::findOrFail($validated['meal_location_id']);

        // Remove old records for this group or single visitor
        $groupId = $visitorMeal->group_id ?: (string) Str::uuid();
        if ($visitorMeal->group_id) {
            VisitorMeal::where('group_id', $visitorMeal->group_id)->delete();
        } else {
            $visitorMeal->delete();
        }

        $createdCount = 0;
        foreach ($period as $d) {
            $dateStr = $d->format('Y-m-d');
            $dayData = $request->input("days.{$dateStr}");

            if (is_array($dayData)) {
                $hasBf = !empty($dayData['has_breakfast']);
                $hasLu = !empty($dayData['has_lunch']);
                $hasDi = !empty($dayData['has_dinner']);
                $hasSu = !empty($dayData['has_supper']);
                $pax   = !empty($dayData['pax_count']) ? (int)$dayData['pax_count'] : (int)$validated['pax_count'];
            } else {
                $hasBf = $request->boolean('has_breakfast');
                $hasLu = $request->boolean('has_lunch');
                $hasDi = $request->boolean('has_dinner');
                $hasSu = $request->boolean('has_supper');
                $pax   = (int)$validated['pax_count'];
            }

            if (!$hasBf && !$hasLu && !$hasDi && !$hasSu) {
                continue;
            }

            VisitorMeal::create([
                'group_id'         => $groupId,
                'visitor_name'     => trim($validated['visitor_name']),
                'institution'      => trim($validated['institution'] ?? 'Visitor / Tamu'),
                'meal_date'        => $d,
                'region_id'        => $mealLocation->region_id,
                'meal_location_id' => $mealLocation->id,
                'pax_count'        => $pax,
                'has_breakfast'    => $hasBf,
                'has_lunch'        => $hasLu,
                'has_dinner'       => $hasDi,
                'has_supper'       => $hasSu,
                'notes'            => $validated['notes'] ?? null,
                'created_by'       => auth()->id(),
                'status'           => 'confirmed',
            ]);

            $createdCount++;
        }

        $rangeInfo = $startDate->eq($endDate)
            ? $startDate->translatedFormat('d F Y')
            : "{$startDate->translatedFormat('d F Y')} s/d {$endDate->translatedFormat('d F Y')}";

        return back()->with('success', "Data visitor \"{$validated['visitor_name']}\" ({$createdCount} hari) berhasil diperbarui untuk periode {$rangeInfo}.");
    }

    /**
     * Delete/cancel a Visitor Meal record.
     */
    public function destroy(Request $request, VisitorMeal $visitorMeal)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isSysAdmin(), 403, 'Hanya role GS yang memiliki akses menghapus visitor.');

        $mealDate = $visitorMeal->meal_date;
        $now = Carbon::now();
        $cutoffDateTime = $mealDate->copy()->subDay()->setTime(19, 0, 0);

        if ($now->greaterThanOrEqualTo($cutoffDateTime) && !$request->boolean('allow_cutoff')) {
            return back()->with('error', "Gagal menghapus visitor. Batas cut-off (H-1 pukul 19:00 WIB) telah lewat untuk tanggal {$mealDate->translatedFormat('d F Y')}.");
        }

        $name = $visitorMeal->visitor_name;
        if ($visitorMeal->group_id) {
            VisitorMeal::where('group_id', $visitorMeal->group_id)->delete();
        } else {
            $visitorMeal->delete();
        }

        return back()->with('success', "Data visitor \"{$name}\" berhasil dihapus.");
    }
}
