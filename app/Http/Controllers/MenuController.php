<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use App\Models\{Menu, MenuItem, Region, MealType};
use Carbon\Carbon;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $canSwitchRegion = $user->isGS() || $user->isCatering() || $user->isSysAdmin();

        $today = Carbon::today();
        $allowedDates = [
            'kemarin'  => $today->copy()->subDay(),
            'hari_ini' => $today,
            'besok'    => $today->copy()->addDay(),
            'lusa'     => $today->copy()->addDays(2),
        ];

        $requestedDate = $request->filled('date') ? Carbon::parse($request->date)->startOfDay() : $today;
        
        // Ensure requested date is clamped to the allowed 4-day window (Kemarin s/d Lusa)
        $minDate = $today->copy()->subDay();
        $maxDate = $today->copy()->addDays(2);
        if ($requestedDate->lt($minDate) || $requestedDate->gt($maxDate)) {
            $requestedDate = $today;
        }

        $date = $requestedDate;

        if (!$canSwitchRegion) {
            // Regular user is strictly restricted to their homebase region
            $regionId = $user->homebase_region_id ?: ($user->region_id ?: 1);
            $userRegion = Region::find($regionId);
            $userRegionName = $userRegion?->name ?? 'Ramba';
        } else {
            // GS, Catering, SysAdmin can view all regions or filter by specific region
            $regionId = $request->filled('region_id') ? $request->integer('region_id') : 0;
            $userRegionName = null;
        }

        $menus = Menu::with(['region', 'mealType', 'items', 'ratings'])
            ->whereDate('menu_date', $date->toDateString())
            ->when($regionId, fn($q) => $q->where('region_id', $regionId))
            ->when(!$canSwitchRegion && !$user->is_shift, function($q) {
                $q->whereHas('mealType', fn($mq) => $mq->where('slug', '!=', 'supper'));
            })
            ->orderBy('meal_type_id')
            ->get();

        $regions   = Region::where('is_active', true)->get();
        $mealTypes = MealType::active()->get();

        return view('menu.index', compact('menus', 'regions', 'mealTypes', 'date', 'regionId', 'today', 'canSwitchRegion', 'userRegionName'));
    }

    public function create()
    {
        abort_unless(auth()->user()->isCatering() || auth()->user()->isGS() || auth()->user()->isSysAdmin(), 403);
        $regions   = Region::where('is_active', true)->get();
        $mealTypes = MealType::active()->get();
        return view('menu.create', compact('regions', 'mealTypes'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isCatering() || auth()->user()->isGS() || auth()->user()->isSysAdmin(), 403);

        $validated = $request->validate([
            'menu_date'    => 'required|date',
            'region_id'    => 'required|exists:regions,id',
            'meal_type_id' => 'required|exists:meal_types,id',
            'description'  => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.name' => 'required|string|max:200',
            'items.*.category' => 'nullable|string|max:100',
        ]);

        $menu = Menu::updateOrCreate(
            [
                'menu_date'    => $validated['menu_date'],
                'region_id'    => $validated['region_id'],
                'meal_type_id' => $validated['meal_type_id'],
            ],
            ['description' => $validated['description'], 'created_by' => auth()->id()]
        );

        $menu->items()->delete();
        foreach ($validated['items'] as $i => $item) {
            MenuItem::create([
                'menu_id'    => $menu->id,
                'name'       => $item['name'],
                'category'   => $item['category'] ?? null,
                'sort_order' => $i,
            ]);
        }

        $this->audit->log('create_menu', 'menu', "Menu {$menu->menu_date} dibuat", $menu);

        return redirect()->route('menu.index')->with('success', 'Menu berhasil disimpan.');
    }
}
