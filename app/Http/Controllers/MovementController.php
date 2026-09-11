<?php

namespace App\Http\Controllers;

use App\Services\{MovementService, OutsideMealService, CancellationService};
use App\Models\{Movement, OutsideMeal, MealPlan, User, MealType, Region};
use Carbon\Carbon;
use Illuminate\Http\Request;

class MovementController extends Controller
{
    public function __construct(private MovementService $service) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Movement::with(['requester', 'fromRegion', 'toRegion', 'people.user'])
            ->whereDate('movement_date', '>=', Carbon::today())
            ->when(!$user->isGS() && !$user->isSysAdmin(),
                fn($q) => $q->where('requester_id', $user->id)
            )
            ->latest('movement_date');

        $movements = $query->paginate(15);
        return view('movement.index', compact('movements'));
    }

    public function create()
    {
        $regions   = Region::where('is_active', true)->get();
        $mealTypes = MealType::active()->get();
        $users     = User::where('is_active', true)->orderBy('name')->get();
        return view('movement.create', compact('regions', 'mealTypes', 'users'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'movement_date'  => 'required|date|after_or_equal:today',
            'from_region_id' => 'required|exists:regions,id',
            'to_region_id'   => 'required|exists:regions,id|different:from_region_id',
            'reason'         => 'required|string|max:1000',
            'user_ids'       => 'required|array|min:1',
            'user_ids.*'     => 'exists:users,id',
            'meal_type_ids'  => 'nullable|array',
            'meal_type_ids.*'=> 'exists:meal_types,id',
        ]);

        try {
            $movement = $this->service->create(
                $validated,
                $validated['user_ids'],
                $validated['meal_type_ids'] ?? [],
                $user
            );
            return redirect()->route('movement.show', $movement)
                ->with('success', 'Permohonan movement berhasil diajukan. Menunggu persetujuan GS.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Movement $movement)
    {
        $movement->load('requester', 'fromRegion', 'toRegion', 'people.user', 'approver');
        return view('movement.show', compact('movement'));
    }

    public function approve(Request $request, Movement $movement)
    {
        abort_unless($request->user()->isGS() || $request->user()->isSysAdmin(), 403);
        abort_unless($movement->isPending(), 422, 'Status tidak valid.');

        $this->service->approve($movement, $request->user());
        return back()->with('success', 'Movement disetujui dan meal plan diperbarui.');
    }

    public function reject(Request $request, Movement $movement)
    {
        abort_unless($request->user()->isGS() || $request->user()->isSysAdmin(), 403);
        $request->validate(['reason' => 'required|string|max:500']);

        $this->service->reject($movement, $request->user(), $request->reason);
        return back()->with('success', 'Movement ditolak.');
    }
}
