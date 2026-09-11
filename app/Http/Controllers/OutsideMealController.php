<?php

namespace App\Http\Controllers;

use App\Services\OutsideMealService;
use App\Models\{OutsideMeal, MealType, User};
use Illuminate\Http\Request;

class OutsideMealController extends Controller
{
    public function __construct(private OutsideMealService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $records = OutsideMeal::with(['requester', 'mealType', 'people.user', 'approver'])
            ->whereDate('meal_date', '>=', \Carbon\Carbon::today())
            ->when(!$user->isGS() && !$user->isSysAdmin(),
                fn($q) => $q->where('requester_id', $user->id)
            )->latest('meal_date')->paginate(15);

        return view('outside-meal.index', compact('records'));
    }

    public function create()
    {
        $mealTypes = MealType::active()->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('outside-meal.create', compact('mealTypes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'meal_date'    => 'required|date|after_or_equal:today',
            'meal_type_id' => 'required|exists:meal_types,id',
            'reason'       => 'required|string|max:1000',
            'user_ids'     => 'required|array|min:1',
            'user_ids.*'   => 'exists:users,id',
        ]);

        try {
            $om = $this->service->create($validated, $validated['user_ids'], $request->user());
            return redirect()->route('outside-meal.show', $om)
                ->with('success', 'Permohonan outside meal berhasil diajukan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(OutsideMeal $outsideMeal)
    {
        $outsideMeal->load('requester', 'mealType', 'people.user', 'approver');
        return view('outside-meal.show', compact('outsideMeal'));
    }

    public function approve(Request $request, OutsideMeal $outsideMeal)
    {
        abort_unless($request->user()->isGS() || $request->user()->isSysAdmin(), 403);
        $this->service->approve($outsideMeal, $request->user());
        return back()->with('success', 'Outside meal disetujui.');
    }

    public function reject(Request $request, OutsideMeal $outsideMeal)
    {
        abort_unless($request->user()->isGS() || $request->user()->isSysAdmin(), 403);
        $request->validate(['reason' => 'required|string|max:500']);
        $this->service->reject($outsideMeal, $request->user(), $request->reason);
        return back()->with('success', 'Outside meal ditolak.');
    }
}
