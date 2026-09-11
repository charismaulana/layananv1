<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'nomor_pegawai', 'name', 'email', 'password',
        'must_change_password',
        'company_id', 'company_name', 'department_id', 'jabatan',
        'worker_status_id', 'homebase_region_id', 'meal_location_id',
        'breakfast_location_id', 'lunch_location_id', 'dinner_location_id', 'supper_location_id',
        'is_shift', 'shift_type', 'supervisor_id', 'role_id',
        'room_id',
        'is_active', 'registration_status', 'meal_card_token',
    ];

    protected $hidden = ['password', 'remember_token', 'meal_card_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'must_change_password' => 'boolean',
        'is_shift'          => 'boolean',
        'is_active'         => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function workerStatus(): BelongsTo
    {
        return $this->belongsTo(WorkerStatus::class);
    }

    public function homebaseRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'homebase_region_id');
    }

    public function mealLocation(): BelongsTo
    {
        return $this->belongsTo(MealLocation::class, 'meal_location_id');
    }

    public function breakfastLocation(): BelongsTo
    {
        return $this->belongsTo(MealLocation::class, 'breakfast_location_id');
    }

    public function lunchLocation(): BelongsTo
    {
        return $this->belongsTo(MealLocation::class, 'lunch_location_id');
    }

    public function dinnerLocation(): BelongsTo
    {
        return $this->belongsTo(MealLocation::class, 'dinner_location_id');
    }

    public function supperLocation(): BelongsTo
    {
        return $this->belongsTo(MealLocation::class, 'supper_location_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function rosters(): HasMany
    {
        return $this->hasMany(Roster::class);
    }

    public function mealPlans(): HasMany
    {
        return $this->hasMany(MealPlan::class);
    }

    public function mealCard(): HasOne
    {
        return $this->hasOne(MealCard::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    // ── Role Helpers ───────────────────────────────────────────────────────────

    public function hasRole(string $slug): bool
    {
        if (!$this->role) return false;
        if ($this->role->slug === $slug) return true;

        return in_array($slug, match($this->role->slug) {
            'user'     => ['user', 'pep', 'user-pep', 'user-non-pep', 'kontraktor', 'management'],
            'gs'       => ['gs', 'admin-departemen', 'system-admin'],
            'catering' => ['catering'],
            default    => []
        });
    }

    public function isUser(): bool       { return $this->hasRole('user'); }
    public function isGS(): bool         { return $this->hasRole('gs'); }
    public function isCatering(): bool   { return $this->hasRole('catering'); }
    public function isPEP(): bool        { return $this->isUser(); }
    public function isNonPEP(): bool     { return $this->isUser(); }
    public function isSysAdmin(): bool   { return $this->isGS(); }
    public function isAdminDept(): bool  { return $this->isGS(); }
    public function isManagement(): bool { return $this->isUser() || $this->isGS(); }
    public function isKontraktor(): bool { return $this->isUser(); }

    public function isApproved(): bool
    {
        return $this->registration_status === 'active';
    }

    public function canHaveRoster(): bool
    {
        return !$this->isCatering();
    }

    public function canApproveContractors(): bool
    {
        return $this->isPEP() || $this->isGS();
    }

    public function canRateMenu(Menu $menu): bool
    {
        // 1. Cannot rate future date menus
        $today = \Carbon\Carbon::today();
        if ($menu->menu_date->gt($today)) {
            return false;
        }

        // 2. User must have had an active, non-cancelled meal plan on that date for that meal type & region
        return \App\Models\MealPlan::finalActive()
            ->where('user_id', $this->id)
            ->whereDate('meal_date', $menu->menu_date)
            ->where('meal_type_id', $menu->meal_type_id)
            ->where('region_id', $menu->region_id)
            ->exists();
    }
}
