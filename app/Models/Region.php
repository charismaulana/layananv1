<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function mealLocations() { return $this->hasMany(MealLocation::class); }
    public function rosters() { return $this->hasMany(Roster::class); }
    public function mealPlans() { return $this->hasMany(MealPlan::class); }
    public function users() { return $this->hasMany(User::class, 'homebase_region_id'); }
    public function rooms() { return $this->hasMany(Room::class); }
}
