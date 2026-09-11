<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Role, Company, Department, WorkerStatus, Region};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private \App\Services\MealPlanService $mealPlanService
    ) {}

    public function index(Request $request)
    {
        $users = User::with(['role', 'company', 'department', 'homebaseRegion', 'workerStatus'])
            ->when($request->search, function ($q, $s) {
                $q->where(function ($sq) use ($s) {
                    $sq->where('name', 'like', "%$s%")
                       ->orWhere('email', 'like', "%$s%")
                       ->orWhere('nomor_pegawai', 'like', "%$s%")
                       ->orWhereHas('department', fn($dq) => $dq->where('name', 'like', "%$s%"))
                       ->orWhereHas('company', fn($cq) => $cq->where('name', 'like', "%$s%"));
                });
            })
            ->when($request->role_id, fn($q, $r) => $q->where('role_id', $r))
            ->when($request->region_id, fn($q, $r) => $q->where('homebase_region_id', $r))
            ->when($request->registration_status, fn($q, $s) => $q->where('registration_status', $s))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles   = Role::all();
        $regions = Region::where('is_active', true)->get();

        return view('admin.users.index', compact('users', 'roles', 'regions'));
    }

    public function create()
    {
        return view('admin.users.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:200',
            'name'                  => 'required|string|max:200',
            'email'                 => 'required|email|unique:users,email',
            'nomor_pegawai'         => 'nullable|string|unique:users,nomor_pegawai',
            'role_id'               => 'required|exists:roles,id',
            'company_name'          => 'nullable|string|max:150',
            'company_id'            => 'nullable|exists:companies,id',
            'department_id'         => 'nullable|exists:departments,id',
            'jabatan'               => 'nullable|string|max:200',
            'worker_status_id'      => ['nullable', 'exists:worker_statuses,id'],
            'homebase_region_id'    => 'nullable|exists:regions,id',
            'meal_location_id'      => 'nullable|exists:meal_locations,id',
            'breakfast_location_id' => 'nullable|exists:meal_locations,id',
            'lunch_location_id'     => 'nullable|exists:meal_locations,id',
            'dinner_location_id'    => 'nullable|exists:meal_locations,id',
            'supper_location_id'    => 'nullable|exists:meal_locations,id',
            'is_shift'              => 'nullable',
            'shift_type'            => 'nullable|string|in:non_shift,shift_pagi,shift_malam',
            'supervisor_id'         => 'nullable|exists:users,id',
            'password'              => 'nullable|string|min:6',
        ]);

        $password = $request->filled('password') ? $request->password : 'password';
        $shiftType = $validated['shift_type'] ?? 'non_shift';
        $isShift = ($shiftType === 'shift_pagi' || $shiftType === 'shift_malam');

        $user = User::create([
            'name'                  => $validated['name'],
            'email'                 => $validated['email'],
            'nomor_pegawai'         => $validated['nomor_pegawai'] ?? null,
            'role_id'               => $validated['role_id'],
            'company_name'          => $validated['company_name'] ?? null,
            'company_id'            => $validated['company_id'] ?? null,
            'department_id'         => $validated['department_id'] ?? null,
            'jabatan'               => $validated['jabatan'] ?? null,
            'worker_status_id'      => $validated['worker_status_id'] ?? null,
            'homebase_region_id'    => $validated['homebase_region_id'] ?? null,
            'meal_location_id'      => $validated['meal_location_id'] ?? null,
            'breakfast_location_id' => $validated['breakfast_location_id'] ?? null,
            'lunch_location_id'     => $validated['lunch_location_id'] ?? null,
            'dinner_location_id'    => $validated['dinner_location_id'] ?? null,
            'supper_location_id'    => $validated['supper_location_id'] ?? null,
            'is_shift'              => $isShift,
            'shift_type'            => $shiftType,
            'supervisor_id'         => $validated['supervisor_id'] ?? null,
            'password'              => $password,
            'must_change_password'  => false,
            'is_active'             => true,
            'registration_status'   => 'active',
            'meal_card_token'       => hash('sha256', Str::uuid()),
        ]);

        $this->audit->log('create_user', 'user', "User {$user->name} ({$user->email}) dibuat oleh admin", $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User <strong>{$user->name}</strong> berhasil dibuat dengan password: <code>{$password}</code>. Akun langsung aktif dan dapat digunakan login.");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', array_merge($this->formData(), compact('user')));
    }

    public function update(Request $request, User $user)
    {
        // Handle password reset request
        if ($request->filled('reset_password') || $request->filled('new_password')) {
            $newPassword = $request->filled('new_password') ? $request->new_password : 'password';
            $user->update([
                'password'             => $newPassword,
                'must_change_password' => false,
            ]);

            $this->audit->log('reset_password', 'user', "Password user {$user->name} di-reset oleh admin", $user);
            return back()->with('success', "Password user <strong>{$user->name}</strong> berhasil di-reset menjadi: <code>{$newPassword}</code>");
        }

        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'email'                 => "required|email|unique:users,email,{$user->id}",
            'nomor_pegawai'         => "nullable|string|unique:users,nomor_pegawai,{$user->id}",
            'role_id'               => 'required|exists:roles,id',
            'company_name'          => 'nullable|string|max:150',
            'company_id'            => 'nullable|exists:companies,id',
            'department_id'         => 'nullable|exists:departments,id',
            'jabatan'               => 'nullable|string|max:200',
            'worker_status_id'      => 'nullable|exists:worker_statuses,id',
            'homebase_region_id'    => 'nullable|exists:regions,id',
            'meal_location_id'      => 'nullable|exists:meal_locations,id',
            'breakfast_location_id' => 'nullable|exists:meal_locations,id',
            'lunch_location_id'     => 'nullable|exists:meal_locations,id',
            'dinner_location_id'    => 'nullable|exists:meal_locations,id',
            'supper_location_id'    => 'nullable|exists:meal_locations,id',
            'is_shift'              => 'nullable',
            'shift_type'            => 'nullable|string|in:non_shift,shift_pagi,shift_malam',
            'supervisor_id'         => 'nullable|exists:users,id',
            'is_active'             => 'nullable|boolean',
        ]);

        $shiftType = $validated['shift_type'] ?? 'non_shift';
        $validated['is_shift'] = ($shiftType === 'shift_pagi' || $shiftType === 'shift_malam');
        $validated['shift_type'] = $shiftType;

        $oldValues = $user->toArray();
        $user->update($validated);
        
        // Otomatis sinkronkan rencana makan besok dan hari seterusnya ke wilayah & dapur baru
        $this->mealPlanService->syncFutureMealPlansForUser($user, $request->user());

        $this->audit->log('update_user', 'user', "User {$user->name} diperbarui", $user, $oldValues, $user->fresh()->toArray());

        return back()->with('success', 'Data pengguna berhasil diperbarui. Rencana makan masa depan otomatis disesuaikan.');
    }

    public function approveContractor(Request $request, User $user)
    {
        abort_unless($user->isKontraktor() && $user->registration_status === 'pending_approval', 422);
        abort_unless($request->user()->isGS() || $request->user()->isPEP() || $request->user()->isSysAdmin(), 403);

        $isSuper = $request->user()->isGS();
        $user->update([
            'registration_status'  => 'active',
            'is_active'            => true,
            'must_change_password' => false,
        ]);

        $this->audit->log('approve_contractor', 'user',
            "Kontraktor {$user->name} di-approve oleh {$request->user()->name}" . ($isSuper ? ' (Super Approval)' : ''),
            $user
        );

        return back()->with('success', "Kontraktor {$user->name} berhasil disetujui dan akunnya langsung aktif.");
    }

    public function rejectContractor(Request $request, User $user)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $user->update(['registration_status' => 'rejected', 'is_active' => false]);
        $this->audit->log('reject_contractor', 'user', "Kontraktor {$user->name} ditolak: " . ($request->reason ?? 'Tidak memenuhi syarat'), $user);

        return back()->with('success', "Kontraktor {$user->name} berhasil ditolak.");
    }

    private function formData(): array
    {
        return [
            'roles'          => Role::all(),
            'companies'      => Company::where('is_active', true)->orderBy('name')->get(),
            'departments'    => Department::where('is_active', true)->orderBy('name')->get(),
            'workerStatuses' => WorkerStatus::where('is_active', true)->get(),
            'regions'        => Region::where('is_active', true)->orderBy('name')->get(),
            'mealLocations'  => \App\Models\MealLocation::where('is_active', true)->with('region')->orderBy('name')->get(),
        ];
    }
}
