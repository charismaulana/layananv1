<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Region, Department, MealLocation, Company, Room};
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MasterDataController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'departments');
        if ($tab === 'regions') {
            $tab = 'departments';
        }

        $regions = Region::withCount(['users', 'mealLocations'])
            ->orderBy('name')
            ->get();

        $departments = Department::withCount('users')
            ->with('company')
            ->orderBy('name')
            ->get();

        $mealLocations = MealLocation::with('region')
            ->withCount('users')
            ->orderBy('region_id')
            ->orderBy('name')
            ->get();

        $companies = Company::where('is_active', true)->orderBy('name')->get();

        $rooms = Room::with('region')
            ->withCount('users')
            ->orderBy('region_id')
            ->orderBy('block')
            ->orderBy('name')
            ->get();

        return view('admin.master.index', compact('tab', 'regions', 'departments', 'mealLocations', 'companies', 'rooms'));
    }

    // ── 1. REGIONS (Wilayah Homebase Utama) ───────────────────────────────────

    public function storeRegion(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:regions,name',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $slug = Str::slug($validated['name']);
        $region = Region::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active', true),
        ]);

        // Automatically create a default "Mess Hall" location for new regions if none exists
        MealLocation::firstOrCreate(
            ['region_id' => $region->id, 'slug' => 'mess-hall'],
            ['name' => 'Mess Hall', 'is_active' => true]
        );

        $this->audit->log('create_region', 'region', "Menambahkan wilayah master: {$region->name}", $region);

        return redirect()->route('admin.master.index', ['tab' => 'regions'])
            ->with('success', "Wilayah '{$region->name}' berhasil ditambahkan ke masterlist.");
    }

    public function updateRegion(Request $request, Region $region)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:regions,name,' . $region->id,
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable',
        ]);

        $old = $region->toArray();
        $region->update([
            'name'        => $validated['name'],
            'slug'        => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        $this->audit->log('update_region', 'region', "Mengubah wilayah master: {$region->name}", $region, $old, $region->toArray());

        return redirect()->route('admin.master.index', ['tab' => 'regions'])
            ->with('success', "Wilayah '{$region->name}' berhasil diperbarui.");
    }

    public function toggleRegion(Region $region)
    {
        $region->is_active = !$region->is_active;
        $region->save();

        $status = $region->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->audit->log('toggle_region', 'region', "Status wilayah '{$region->name}' diubah menjadi {$status}", $region);

        return redirect()->route('admin.master.index', ['tab' => 'regions'])
            ->with('success', "Wilayah '{$region->name}' berhasil {$status}.");
    }

    // ── 2. DEPARTMENTS (Departemen / Fungsi) ──────────────────────────────────

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'nullable|string|max:20',
            'company_id' => 'nullable|exists:companies,id',
            'is_active'  => 'nullable|boolean',
        ]);

        $department = Department::create([
            'name'       => $validated['name'],
            'code'       => $validated['code'] ?? Str::upper(substr($validated['name'], 0, 4)),
            'company_id' => $validated['company_id'] ?? (Company::first()?->id ?? 1),
            'is_active'  => $request->boolean('is_active', true),
        ]);

        $this->audit->log('create_department', 'department', "Menambahkan departemen/fungsi: {$department->name}", $department);

        return redirect()->route('admin.master.index', ['tab' => 'departments'])
            ->with('success', "Departemen/Fungsi '{$department->name}' berhasil ditambahkan.");
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'code'       => 'nullable|string|max:20',
            'company_id' => 'nullable|exists:companies,id',
            'is_active'  => 'nullable',
        ]);

        $old = $department->toArray();
        $department->update([
            'name'       => $validated['name'],
            'code'       => $validated['code'] ?? $department->code,
            'company_id' => $validated['company_id'] ?? $department->company_id,
            'is_active'  => $request->boolean('is_active'),
        ]);

        $this->audit->log('update_department', 'department', "Mengubah departemen/fungsi: {$department->name}", $department, $old, $department->toArray());

        return redirect()->route('admin.master.index', ['tab' => 'departments'])
            ->with('success', "Departemen/Fungsi '{$department->name}' berhasil diperbarui.");
    }

    public function toggleDepartment(Department $department)
    {
        $department->is_active = !$department->is_active;
        $department->save();

        $status = $department->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->audit->log('toggle_department', 'department', "Status departemen '{$department->name}' diubah menjadi {$status}", $department);

        return redirect()->route('admin.master.index', ['tab' => 'departments'])
            ->with('success', "Departemen '{$department->name}' berhasil {$status}.");
    }

    // ── 3. MEAL LOCATIONS (Lokasi Tempat Makan) ───────────────────────────────

    public function storeMealLocation(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name'      => 'required|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = Str::slug($validated['name']);
        $location = MealLocation::create([
            'region_id' => $validated['region_id'],
            'name'      => $validated['name'],
            'slug'      => $slug,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->audit->log('create_meal_location', 'meal_location', "Menambahkan lokasi makan: {$location->name} ({$location->region?->name})", $location);

        return redirect()->route('admin.master.index', ['tab' => 'meal-locations'])
            ->with('success', "Lokasi tempat makan '{$location->name}' berhasil ditambahkan.");
    }

    public function updateMealLocation(Request $request, MealLocation $mealLocation)
    {
        $validated = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'name'      => 'required|string|max:100',
            'is_active' => 'nullable',
        ]);

        $old = $mealLocation->toArray();
        $mealLocation->update([
            'region_id' => $validated['region_id'],
            'name'      => $validated['name'],
            'slug'      => Str::slug($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->log('update_meal_location', 'meal_location', "Mengubah lokasi makan: {$mealLocation->name}", $mealLocation, $old, $mealLocation->toArray());

        return redirect()->route('admin.master.index', ['tab' => 'meal-locations'])
            ->with('success', "Lokasi tempat makan '{$mealLocation->name}' berhasil diperbarui.");
    }

    public function toggleMealLocation(MealLocation $mealLocation)
    {
        $mealLocation->is_active = !$mealLocation->is_active;
        $mealLocation->save();

        $status = $mealLocation->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->audit->log('toggle_meal_location', 'meal_location', "Status lokasi makan '{$mealLocation->name}' diubah menjadi {$status}", $mealLocation);

        return redirect()->route('admin.master.index', ['tab' => 'meal-locations'])
            ->with('success', "Lokasi makan '{$mealLocation->name}' berhasil {$status}.");
    }

    // ── 4. ROOMS (Kamar Mess / Penginapan) ───────────────────────────────────────

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'region_id' => 'nullable|exists:regions,id',
            'name'      => 'required|string|max:100',
            'block'     => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $room = Room::create([
            'region_id' => $validated['region_id'] ?? null,
            'name'      => $validated['name'],
            'block'     => $validated['block'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->audit->log('create_room', 'room', "Menambahkan kamar: {$room->full_name}", $room);

        return redirect()->route('admin.master.index', ['tab' => 'rooms'])
            ->with('success', "Kamar '{$room->full_name}' berhasil ditambahkan.");
    }

    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'region_id' => 'nullable|exists:regions,id',
            'name'      => 'required|string|max:100',
            'block'     => 'nullable|string|max:50',
            'is_active' => 'nullable',
        ]);

        $old = $room->toArray();
        $room->update([
            'region_id' => $validated['region_id'] ?? null,
            'name'      => $validated['name'],
            'block'     => $validated['block'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->log('update_room', 'room', "Mengubah kamar: {$room->full_name}", $room, $old, $room->toArray());

        return redirect()->route('admin.master.index', ['tab' => 'rooms'])
            ->with('success', "Kamar '{$room->full_name}' berhasil diperbarui.");
    }

    public function toggleRoom(Room $room)
    {
        $room->is_active = !$room->is_active;
        $room->save();

        $status = $room->is_active ? 'diaktifkan' : 'dinonaktifkan';
        $this->audit->log('toggle_room', 'room', "Status kamar '{$room->full_name}' diubah menjadi {$status}", $room);

        return redirect()->route('admin.master.index', ['tab' => 'rooms'])
            ->with('success', "Kamar '{$room->full_name}' berhasil {$status}.");
    }
}
