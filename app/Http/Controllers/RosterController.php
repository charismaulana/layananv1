<?php

namespace App\Http\Controllers;

use App\Services\{RosterService, PlanningCutoffService, AuditService};
use App\Models\{Roster, Region, User};
use Carbon\Carbon;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function __construct(
        private RosterService $rosterService,
        private PlanningCutoffService $cutoff
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();

        // Catering role does not manage or eat meals from roster
        if ($user->isCatering()) {
            return redirect()->route('dashboard')->with('warning', 'Pihak Catering tidak mengelola jadwal roster makan.');
        }

        $month = $request->filled('month') ? Carbon::parse($request->month) : Carbon::now();

        // GS can view/manage other users' rosters
        $targetUser = $user;
        if ($user->isGS() && $request->filled('user_id')) {
            $targetUser = User::findOrFail($request->user_id);
        }

        $rosters = Roster::where('user_id', $targetUser->id)
            ->whereBetween('roster_date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->with('region')
            ->get()
            ->keyBy(fn($r) => $r->roster_date->format('Y-m-d'));

        $regions = Region::where('is_active', true)->get();
        $users   = $this->getSelectableUsers($user);

        return view('roster.index', compact('rosters', 'regions', 'month', 'targetUser', 'users'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'roster_date' => 'required|date',
            'status'      => 'required|in:Kerja,Libur',
            'shift_type'  => 'nullable|string|in:non_shift,shift_pagi,shift_malam',
            'region_id'   => 'nullable|exists:regions,id',
            'notes'       => 'nullable|string|max:500',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        // Prevent roster on Catering
        if (!$targetUser->canHaveRoster()) {
            return back()->with('error', "Role {$targetUser->role?->name} tidak dapat memiliki jadwal roster makan.");
        }

        // Authorization check
        $this->authorizeRosterInput($user, (int)$validated['user_id']);

        $mealDate = Carbon::parse($validated['roster_date']);
        if ($this->cutoff->isClosed($mealDate) && !$user->isGS()) {
            return back()->with('error', "Batas waktu pengisian roster mandiri (Cut-off H-1 19:00 WIB) telah berakhir untuk tanggal {$mealDate->translatedFormat('d M Y')}. Silakan hubungi GS untuk bantuan perubahan.");
        }

        $validated['region_id'] = $validated['region_id'] ?? $targetUser->homebase_region_id ?? Region::first()?->id ?? 1;
        $validated['shift_type'] = $validated['shift_type'] ?? $targetUser->shift_type ?? ($targetUser->is_shift ? 'shift_pagi' : 'non_shift');

        $roster = $this->rosterService->upsert($validated, $user);

        $dateFormatted = $roster->roster_date->translatedFormat('d M Y');
        $shiftLabel = match($roster->shift_type) {
            'shift_malam' => ' [Shift Malam (+Supper)]',
            'shift_pagi'  => ' [Shift Pagi]',
            default       => '',
        };
        $statusText = $roster->status === 'Kerja' ? "Kerja{$shiftLabel}" : 'Libur (Tidak Makan / Dinas Luar)';
        return redirect()->route('roster.index', ['month' => $mealDate->format('Y-m-d'), 'user_id' => $validated['user_id']])
            ->with('success', "Roster tanggal {$dateFormatted} berhasil disimpan sebagai [{$statusText}].");
    }

    /**
     * Bulk update roster for a date range (e.g. 4 days dinas luar / tidak makan).
     */
    public function storeBulk(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'user_id'    => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'status'     => 'required|in:Kerja,Libur',
            'shift_type' => 'nullable|string|in:non_shift,shift_pagi,shift_malam',
            'region_id'  => 'nullable|exists:regions,id',
            'notes'      => 'nullable|string|max:500',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        if (!$targetUser->canHaveRoster()) {
            return back()->with('error', "Role {$targetUser->role?->name} tidak dapat memiliki jadwal roster makan.");
        }

        $this->authorizeRosterInput($user, (int)$validated['user_id']);

        $regionId = $validated['region_id'] ?? $targetUser->homebase_region_id ?? Region::first()?->id ?? 1;
        $shiftType = $validated['shift_type'] ?? $targetUser->shift_type ?? ($targetUser->is_shift ? 'shift_pagi' : 'non_shift');

        $start = Carbon::parse($validated['start_date']);
        $end   = Carbon::parse($validated['end_date']);

        $processedCount = 0;
        $skippedPastCutoff = 0;

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if ($this->cutoff->isClosed($d) && !$user->isGS()) {
                $skippedPastCutoff++;
                continue;
            }

            $this->rosterService->upsert([
                'user_id'     => $targetUser->id,
                'roster_date' => $d->toDateString(),
                'status'      => $validated['status'],
                'shift_type'  => $shiftType,
                'region_id'   => $regionId,
                'notes'       => $validated['notes'] ?? null,
            ], $user);

            $processedCount++;
        }

        if ($processedCount === 0 && $skippedPastCutoff > 0) {
            return back()->with('error', "Semua tanggal yang dipilih ({$start->translatedFormat('d M Y')} s/d {$end->translatedFormat('d M Y')}) telah melewati batas waktu cut-off. Hubungi GS untuk bantuan.");
        }

        $msg = "Berhasil memperbarui roster {$processedCount} hari untuk {$targetUser->name}.";
        if ($skippedPastCutoff > 0) {
        }

        return redirect()->route('roster.index', ['month' => $start->format('Y-m-d'), 'user_id' => $targetUser->id])
            ->with('success', $msg);
    }

    public function copyRoster(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'source_dates'   => 'required|array',
            'source_dates.*' => 'date',
            'target_dates'   => 'required|array',
            'target_dates.*' => 'date',
        ]);

        $targetUser = User::findOrFail($validated['target_user_id']);
        if (!$targetUser->canHaveRoster()) {
            return back()->with('error', "Role {$targetUser->role?->name} tidak dapat memiliki jadwal roster makan.");
        }

        $this->authorizeRosterInput($user, (int)$validated['target_user_id']);

        $count = $this->rosterService->copyRoster(
            $targetUser,
            $validated['source_dates'],
            $validated['target_dates'],
            $user
        );

        return back()->with('success', "$count data roster berhasil disalin.");
    }

    private function authorizeRosterInput(User $actor, int $targetUserId): void
    {
        // System admin and GS have administrative roster override
        if ($actor->isGS() || $actor->isSysAdmin()) return;

        if ($actor->isAdminDept()) {
            $isSubordinate = User::where('id', $targetUserId)
                ->where('supervisor_id', $actor->id)->exists();
            if ($actor->id !== $targetUserId && !$isSubordinate) {
                abort(403, 'Anda hanya dapat mengelola roster anggota departemen Anda.');
            }
            return;
        }

        // All other roles (PEP, Kontraktor, Management) can manage their own roster
        if ($actor->id !== $targetUserId) {
            abort(403, 'Anda hanya dapat mengelola roster Anda sendiri.');
        }
    }

    private function getSelectableUsers(User $user)
    {
        if ($user->isGS() || $user->isSysAdmin()) {
            return User::with(['department', 'workerStatus', 'role'])
                ->whereHas('role', fn($q) => $q->where('slug', '!=', 'catering'))
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        if ($user->isAdminDept()) {
            return User::with(['department', 'workerStatus', 'role'])
                ->whereHas('role', fn($q) => $q->where('slug', '!=', 'catering'))
                ->where(function ($q) use ($user) {
                    $q->where('supervisor_id', $user->id)->orWhere('id', $user->id);
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        return collect([$user->load(['department', 'workerStatus', 'role'])]);
    }
}
