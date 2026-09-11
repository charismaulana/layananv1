<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\{Company, Department, WorkerStatus, Region, MealLocation, Room};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private \App\Services\MealPlanService $mealPlanService) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $companies = Company::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $workerStatuses = WorkerStatus::where('is_active', true)->get();
        $regions = Region::where('is_active', true)->orderBy('name')->get();
        $mealLocations = MealLocation::where('is_active', true)->with('region')->get();
        $rooms = Room::where('is_active', true)->with('region')->orderBy('region_id')->orderBy('block')->orderBy('name')->get();

        return view('profile.edit', compact(
            'user', 'companies', 'departments', 'workerStatuses', 'regions', 'mealLocations', 'rooms'
        ));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $isShift = $request->boolean('is_shift');
        $validated['is_shift'] = $isShift;
        $validated['shift_type'] = $isShift ? ($user->shift_type === 'shift_malam' ? 'shift_malam' : 'shift_pagi') : 'non_shift';

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Otomatis sinkronkan rencana makan besok dan hari seterusnya ke wilayah & dapur baru
        $this->mealPlanService->syncFutureMealPlansForUser($user, $user);

        return Redirect::route('profile.edit')->with('success', 'Profil dan identitas berhasil diperbarui. Rencana makan besok dan hari seterusnya otomatis disesuaikan ke wilayah/dapur baru.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
