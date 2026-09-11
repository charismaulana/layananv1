<?php

namespace App\Http\Controllers;

use App\Services\PredictedPobService;
use App\Services\MealCardService;
use App\Models\{MealPlan, Roster, Movement, OutsideMeal, User, Menu, Suggestion, Rating};
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private PredictedPobService $pobService,
        private MealCardService $cardService,
        private \App\Services\ManifestService $manifestService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        return match(true) {
            $user->isGS()       => $this->gs($request),
            $user->isCatering() => $this->catering($request),
            default             => $this->user($request),
        };
    }

    public function beranda(Request $request)
    {
        return $this->user($request);
    }

    private function user(Request $request)
    {
        $user     = $request->user();
        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $todayPlans = MealPlan::where('user_id', $user->id)
            ->whereDate('meal_date', $today)
            ->with(['mealType', 'region', 'mealLocation'])
            ->orderBy('meal_type_id')
            ->get();

        $tomorrowPlans = MealPlan::where('user_id', $user->id)
            ->whereDate('meal_date', $tomorrow)
            ->with(['mealType', 'region', 'mealLocation'])
            ->orderBy('meal_type_id')
            ->get();

        $todayRoster = Roster::where('user_id', $user->id)
            ->whereDate('roster_date', $today)->first();

        $tomorrowRoster = Roster::where('user_id', $user->id)
            ->whereDate('roster_date', $tomorrow)->first();

        $upcomingWeek = collect(range(0, 6))->map(fn($d) => [
            'date' => $today->copy()->addDays($d),
            'roster' => Roster::where('user_id', $user->id)
                ->whereDate('roster_date', $today->copy()->addDays($d))->first(),
        ]);

        $todayMenu = Menu::whereDate('menu_date', $today)
            ->whereIn('region_id', $todayPlans->pluck('region_id')->unique())
            ->with(['mealType', 'region', 'items'])
            ->get();

        // Check recent upcoming movement / outside meal requests (Unique per date)
        $myRecentMovements = Movement::where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhereHas('people', fn($pq) => $pq->where('user_id', $user->id));
            })
            ->whereDate('movement_date', '>=', $today)
            ->with(['fromRegion', 'toRegion'])
            ->orderBy('movement_date')
            ->orderByDesc('id')
            ->get()
            ->unique(fn($m) => $m->movement_date->format('Y-m-d'))
            ->take(2);

        $myRecentOutsideMeals = OutsideMeal::where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhereHas('people', fn($pq) => $pq->where('user_id', $user->id));
            })
            ->whereDate('meal_date', '>=', $today)
            ->with(['mealType'])
            ->orderBy('meal_date')
            ->orderByDesc('id')
            ->get()
            ->unique(fn($o) => $o->meal_date->format('Y-m-d'))
            ->take(2);

        $deliveryLocations = \App\Models\MealLocation::where('is_active', true)
            ->with('region')
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();

        return view('dashboard.user', compact(
            'user', 'today', 'tomorrow', 'todayPlans', 'tomorrowPlans',
            'todayRoster', 'tomorrowRoster', 'upcomingWeek',
            'todayMenu', 'myRecentMovements', 'myRecentOutsideMeals',
            'deliveryLocations'
        ));
    }

    private function gs(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();

        $pobSummary   = $this->pobService->summaryTable($date);
        $pendingMovements  = Movement::pending()->whereDate('movement_date', '>=', $date)->with(['requester', 'fromRegion', 'toRegion'])->latest()->take(10)->get();
        $pendingOutside    = OutsideMeal::pending()->whereDate('meal_date', '>=', $date)->with(['requester', 'mealType'])->latest()->take(10)->get();
        $pendingKontraktor = User::where('registration_status', 'pending')->latest()->take(10)->get();

        $totalUsers  = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', '!=', 'catering'))
            ->count();
        $totalActive = $totalUsers;
        $rosterToday = Roster::whereDate('roster_date', $date)
            ->whereHas('user', fn($uq) => $uq->where('is_active', true)->whereHas('role', fn($rq) => $rq->where('slug', '!=', 'catering')))
            ->count();
        $rosterCompletion = $totalActive > 0 ? round(($rosterToday / $totalActive) * 100) : 0;

        $usersWithoutRoster = User::where('is_active', true)
            ->whereHas('role', fn($q) => $q->where('slug', '!=', 'catering'))
            ->whereDoesntHave('rosters', fn($rq) => $rq->whereDate('roster_date', $date))
            ->with(['department', 'role', 'homebaseRegion'])
            ->orderBy('name')
            ->get();

        $avgRating = Rating::whereDate('rating_date', $date)->avg('score');

        $visitorMeals = \App\Models\VisitorMeal::whereDate('meal_date', $date)
            ->with(['region', 'mealLocation', 'creator'])
            ->latest()
            ->get()
            ->unique(fn($v) => $v->group_id ?: $v->id)
            ->values();

        $messHallLocations = \App\Models\MealLocation::where('is_active', true)
            ->where('name', 'like', '%Mess Hall%')
            ->with('region')
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();

        return view('dashboard.gs', compact(
            'date', 'pobSummary', 'pendingMovements', 'pendingOutside',
            'pendingKontraktor', 'totalUsers', 'totalActive', 'rosterToday', 'rosterCompletion', 'usersWithoutRoster', 'avgRating',
            'visitorMeals', 'messHallLocations'
        ));
    }

    private function catering(Request $request)
    {
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $messHalls = \App\Services\ManifestService::getMessHalls();

        // 1. Data 5 Mess Hall untuk Tanggal Terpilih (B'fast, Lunch, Dinner, Supper, Total)
        $messHallData = [];
        $overallTotals = ['breakfast' => 0, 'lunch' => 0, 'dinner' => 0, 'supper' => 0, 'total' => 0, 'workers' => 0];

        foreach ($messHalls as $key => $cfg) {
            $data = $this->manifestService->getMessHallManifestData($date, $key);
            $messHallData[$key] = [
                'config'      => $cfg,
                'name'        => $cfg['name'],
                'region_name' => $cfg['region_name'],
                'color'       => $cfg['color'] ?? '#006738',
                'bg'          => $cfg['bg'] ?? '#f0fdf4',
                'totals'      => $data['totals'],
                'grand_total' => $data['grand_total'],
                'workers'     => count($data['rows']),
            ];

            $overallTotals['breakfast'] += $data['totals']['breakfast'];
            $overallTotals['lunch']     += $data['totals']['lunch'];
            $overallTotals['dinner']    += $data['totals']['dinner'];
            $overallTotals['supper']    += $data['totals']['supper'];
            $overallTotals['total']     += $data['grand_total'];
            $overallTotals['workers']   += count($data['rows']);
        }

        // 2. Trend 7 Hari per 5 Mess Hall
        $dailyTrend = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $date->copy()->addDays($i);
            $dayMhTotals = [];
            $dayGrandTotal = 0;

            foreach ($messHalls as $key => $cfg) {
                $dData = $this->manifestService->getMessHallManifestData($d, $key);
                $dayMhTotals[$key] = $dData['grand_total'];
                $dayGrandTotal += $dData['grand_total'];
            }

            $dailyTrend[] = [
                'date'        => $d,
                'is_today'    => $d->isToday(),
                'messhalls'   => $dayMhTotals,
                'total_pax'   => $dayGrandTotal,
            ];
        }

        return view('dashboard.catering', compact('date', 'messHalls', 'messHallData', 'overallTotals', 'dailyTrend'));
    }

    private function management(Request $request)
    {
        $date   = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        $pobSummary = $this->pobService->summaryTable($date);

        // Trend for last 7 days
        $trend = collect(range(6, 0))->map(fn($d) => [
            'date' => $date->copy()->subDays($d)->format('d/m'),
            'pob'  => MealPlan::finalActive()
                ->whereDate('meal_date', $date->copy()->subDays($d))
                ->count(),
        ]);

        $totalMovements  = Movement::whereDate('movement_date', $date)->count();
        $totalOutside    = OutsideMeal::whereDate('meal_date', $date)->count();
        $totalCancelled  = MealPlan::where('cancelled', true)->whereDate('meal_date', $date)->count();
        $avgRating       = Rating::whereDate('rating_date', $date)->avg('score');

        return view('dashboard.management', compact(
            'date', 'pobSummary', 'trend', 'totalMovements', 'totalOutside', 'totalCancelled', 'avgRating'
        ));
    }
}
