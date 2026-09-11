<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MealType extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'sort_order', 'shift_only', 'is_active'];
    protected $casts = ['shift_only' => 'boolean', 'is_active' => 'boolean'];

    public function mealPlans() { return $this->hasMany(MealPlan::class); }
    public function menus() { return $this->hasMany(Menu::class); }

    public function scopeActive($query) { return $query->where('is_active', true)->orderBy('sort_order'); }
    public function scopeForUser($query, User $user)
    {
        if (!$user->is_shift) {
            return $query->where('shift_only', false);
        }
        return $query;
    }
}
