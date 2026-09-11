<?php

namespace App\Http\Controllers;

use App\Services\MealCardService;
use App\Models\MealCard;
use Illuminate\Http\Request;

class MealCardController extends Controller
{
    public function __construct(private MealCardService $service) {}

    public function show(Request $request)
    {
        $user = $request->user();
        $card = $this->service->getOrCreate($user);
        $qrSvg = $this->service->generateQrSvg($card);
        return view('meal-card.show', compact('user', 'card', 'qrSvg'));
    }

    public function regenerate(Request $request)
    {
        $card = $this->service->regenerateToken($request->user(), $request->user());
        return back()->with('success', 'Token Meal Card berhasil diperbarui.');
    }
}
