<?php

namespace App\Http\Controllers;

use App\Services\CancellationService;
use App\Services\PlanningCutoffService;
use App\Models\MealPlan;
use Illuminate\Http\Request;

class CancellationController extends Controller
{
    public function __construct(
        private CancellationService $service,
        private PlanningCutoffService $cutoff
    ) {}

    public function create(MealPlan $mealPlan)
    {
        abort_unless(
            $mealPlan->user_id === auth()->id() || auth()->user()->isGS(),
            403, 'Akses ditolak.'
        );
        $mealPlan->load('mealType', 'region', 'mealLocation');
        $isCutoffPassed = $this->cutoff->isClosed($mealPlan->meal_date);
        return view('cancellation.create', compact('mealPlan', 'isCutoffPassed'));
    }

    public function store(Request $request, MealPlan $mealPlan)
    {
        abort_unless(
            $mealPlan->user_id === auth()->id() || auth()->user()->isGS(),
            403
        );
        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $this->service->cancel($mealPlan, $request->input('reason'), $request->user());
            return redirect()->route('meal-plan.index')
                ->with('success', 'Makan berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reactivate(Request $request, MealPlan $mealPlan)
    {
        abort_unless(
            $mealPlan->user_id === auth()->id() || auth()->user()->isGS() || auth()->user()->isSysAdmin(),
            403, 'Akses ditolak.'
        );

        try {
            $this->service->reactivate($mealPlan, $request->user());
            $name = $mealPlan->mealType?->name ?? 'Makan';
            $tgl = $mealPlan->meal_date->translatedFormat('d M Y');
            return back()->with('success', "Jatah {$name} tanggal {$tgl} berhasil diaktifkan kembali.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
