<?php

namespace App\Http\Controllers;

use App\Models\{Rating, Suggestion, Region, MealType};
use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function __construct(private ExportService $exportService) {}

    public function index(Request $request)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isCatering() || auth()->user()->isSysAdmin(), 403, 'Akses terbatas untuk General Services (GS) & Catering.');

        $today = Carbon::today();
        $from  = $request->filled('from') ? Carbon::parse($request->from) : $today->copy()->startOfMonth();
        $to    = $request->filled('to')   ? Carbon::parse($request->to)   : $today->copy();

        $fromStr = $from->format('Y-m-d');
        $toStr   = $to->format('Y-m-d');

        $regionId   = $request->filled('region_id') ? (int)$request->region_id : null;
        $mealTypeId = $request->filled('meal_type_id') ? (int)$request->meal_type_id : null;
        $starFilter = $request->filled('star') ? (int)$request->star : null;
        $activeTab  = $request->input('tab', 'ratings');

        $regions   = Region::where('is_active', true)->get();
        $mealTypes = MealType::active()->get();

        // ── 1. KPI & Summary Metrics (within Date Range & Region) ──
        $baseRatingQuery = Rating::whereBetween('rating_date', [$fromStr, $toStr])
            ->when($regionId, fn($q, $v) => $q->where('region_id', $v));

        $totalRatings = (clone $baseRatingQuery)->count();
        $avgScore     = $totalRatings > 0 ? round((clone $baseRatingQuery)->avg('score'), 1) : 0;

        $starCounts = [];
        for ($i = 5; $i >= 1; $i--) {
            $c = (clone $baseRatingQuery)->where('score', $i)->count();
            $starCounts[$i] = [
                'count' => $c,
                'pct'   => $totalRatings > 0 ? round(($c / $totalRatings) * 100) : 0,
            ];
        }

        // Region score breakdown
        $regionAverages = [];
        foreach ($regions as $r) {
            $regQuery = Rating::whereBetween('rating_date', [$fromStr, $toStr])->where('region_id', $r->id);
            $regCount = (clone $regQuery)->count();
            $regAvg   = $regCount > 0 ? round((clone $regQuery)->avg('score'), 1) : null;
            $regionAverages[$r->id] = [
                'name'  => $r->name,
                'count' => $regCount,
                'avg'   => $regAvg,
            ];
        }

        // ── 2. Suggestions Metrics ──
        $baseSuggestionQuery = Suggestion::whereBetween('created_at', [
            $from->copy()->startOfDay()->toDateTimeString(),
            $to->copy()->endOfDay()->toDateTimeString(),
        ])->when($regionId, fn($q, $v) => $q->where('region_id', $v));

        $totalSuggestions  = (clone $baseSuggestionQuery)->count();
        $unreadSuggestions = (clone $baseSuggestionQuery)->where('is_read', false)->count();

        // ── 3. Paginated Data ──
        $ratings = Rating::with(['user.department', 'user.company', 'menu.items', 'region', 'mealType'])
            ->whereBetween('rating_date', [$fromStr, $toStr])
            ->when($regionId, fn($q, $v) => $q->where('region_id', $v))
            ->when($mealTypeId, fn($q, $v) => $q->where('meal_type_id', $v))
            ->when($starFilter, fn($q, $v) => $q->where('score', $v))
            ->latest('rating_date')
            ->latest('id')
            ->paginate(20, ['*'], 'ratings_page')
            ->withQueryString();

        $suggestions = Suggestion::with(['user.department', 'user.company', 'region'])
            ->whereBetween('created_at', [
                $from->copy()->startOfDay()->toDateTimeString(),
                $to->copy()->endOfDay()->toDateTimeString(),
            ])
            ->when($regionId, fn($q, $v) => $q->where('region_id', $v))
            ->when($request->filled('category'), fn($q) => $q->where('category', $request->category))
            ->latest()
            ->paginate(20, ['*'], 'suggestions_page')
            ->withQueryString();

        return view('feedback.index', compact(
            'from', 'to', 'regions', 'mealTypes', 'regionId', 'mealTypeId', 'starFilter', 'activeTab',
            'totalRatings', 'avgScore', 'starCounts', 'regionAverages',
            'totalSuggestions', 'unreadSuggestions',
            'ratings', 'suggestions'
        ));
    }

    public function export(Request $request)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isCatering() || auth()->user()->isSysAdmin(), 403, 'Akses terbatas untuk General Services (GS) & Catering.');

        $today = Carbon::today();
        $from  = $request->input('from', $today->copy()->startOfMonth()->toDateString());
        $to    = $request->input('to',   $today->toDateString());
        $regionId = $request->input('region_id');

        $filters = compact('from', 'to', 'regionId');

        return $this->exportService->exportFeedback($filters);
    }

    public function toggleRead(Request $request, Suggestion $suggestion)
    {
        abort_unless(auth()->user()->isGS() || auth()->user()->isCatering() || auth()->user()->isSysAdmin(), 403);

        $suggestion->update([
            'is_read' => !$suggestion->is_read,
        ]);

        $statusText = $suggestion->is_read ? 'ditandai sudah ditinjau' : 'ditandai belum ditinjau';
        return back()->with('success', "Saran dari {$suggestion->user?->name} berhasil {$statusText}.");
    }
}
