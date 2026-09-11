<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MealPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'meal_date', 'meal_type_id', 'region_id', 'meal_location_id',
        'status', 'source', 'cancelled', 'outside_meal', 'roster_id',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'meal_date'    => 'date',
        'cancelled'    => 'boolean',
        'outside_meal' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function mealType() { return $this->belongsTo(MealType::class); }
    public function region() { return $this->belongsTo(Region::class); }
    public function mealLocation() { return $this->belongsTo(MealLocation::class); }
    public function roster() { return $this->belongsTo(Roster::class); }
    public function cancellation() { return $this->hasOne(MealCancellation::class); }
    public function manifestPeople() { return $this->hasMany(ManifestPerson::class); }

    // ── Scopes ────────────────────────────────────────────────────────────────

    /** Final active meal plans (counted in Predicted POB) */
    public function scopeFinalActive($q)
    {
        return $q->where('status', 'active')
                 ->where('cancelled', false)
                 ->where('outside_meal', false);
    }

    public function scopeForDate($q, $date) { return $q->whereDate('meal_date', $date); }
    public function scopeForRegion($q, $id)  { return $q->where('region_id', $id); }
    public function scopeForMealType($q, $id) { return $q->where('meal_type_id', $id); }
    public function scopeForLocation($q, $id) { return $q->where('meal_location_id', $id); }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->cancelled && !$this->outside_meal;
    }
}
