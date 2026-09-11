<?php

namespace App\Http\Controllers;

use App\Models\{Rating, Suggestion, Menu, Region, MealType};
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function create(Request $request)
    {
        $user = $request->user();
        $menu = $request->filled('menu_id')
            ? Menu::with(['region', 'mealType', 'items', 'ratings'])->find($request->menu_id)
            : null;

        if ($menu && !$user->canRateMenu($menu)) {
            return redirect()->route('menu.index')->with('error', 'Anda hanya dapat memberikan rating untuk makanan yang telah Anda santap.');
        }

        // Recent menus the user has actually eaten in the last 3 days
        $eatenMenuDates = \App\Models\MealPlan::finalActive()
            ->where('user_id', $user->id)
            ->whereBetween('meal_date', [now()->subDays(3)->toDateString(), now()->toDateString()])
            ->get();

        $recentMenus = Menu::with(['region', 'mealType'])
            ->whereBetween('menu_date', [now()->subDays(3)->toDateString(), now()->toDateString()])
            ->get()
            ->filter(fn($m) => $user->canRateMenu($m));

        return view('rating.create', compact('menu', 'recentMenus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_id'      => 'required|exists:menus,id',
            'score'        => 'required|integer|min:1|max:5',
            'comment'      => 'nullable|string|max:1000',
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $user = $request->user();

        if (!$user->canRateMenu($menu)) {
            return back()->with('error', 'Anda hanya dapat memberikan rating untuk makanan yang telah Anda santap.');
        }

        $rating = Rating::updateOrCreate(
            ['user_id' => $user->id, 'menu_id' => $menu->id],
            [
                'region_id'    => $menu->region_id,
                'meal_type_id' => $menu->meal_type_id,
                'rating_date'  => $menu->menu_date,
                'score'        => $validated['score'],
                'comment'      => $validated['comment'],
            ]
        );

        $this->audit->log('rate_menu', 'rating', "Rating {$validated['score']}/5 untuk menu {$menu->id}", $rating);

        return back()->with('success', 'Rating berhasil disimpan. Terima kasih!');
    }
}

class SuggestionController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function create(Request $request)
    {
        $user = $request->user();
        $mySuggestions = \App\Models\Suggestion::where('user_id', $user->id)
            ->latest()->take(5)->get();
        return view('suggestion.create', compact('mySuggestions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'     => 'required|string|max:50',
            'title'        => 'nullable|string|max:200',
            'content'      => 'required|string|min:3|max:2000',
            'is_anonymous' => 'nullable',
        ]);

        $fullContent = $validated['content'];
        if ($request->filled('title')) {
            $fullContent = "[" . trim($request->title) . "]\n" . $fullContent;
        }
        if ($request->filled('is_anonymous') && $request->is_anonymous) {
            $fullContent .= "\n\n(Catatan: Dikirim secara Anonim)";
        }

        $regionId = $request->user()->homebase_region_id ?? \App\Models\Region::first()?->id;

        $suggestion = Suggestion::create([
            'user_id'   => $request->user()->id,
            'region_id' => $regionId,
            'category'  => $validated['category'],
            'content'   => $fullContent,
            'is_read'   => false,
        ]);

        $this->audit->log('submit_suggestion', 'suggestion', "Saran kategori {$validated['category']} dikirim", $suggestion);

        return redirect()->route('dashboard')->with('success', 'Saran berhasil dikirim kepada pihak catering & GS. Terima kasih!');
    }
}
