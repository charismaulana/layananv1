<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['menu_date', 'region_id', 'meal_type_id', 'description', 'created_by'];
    protected $casts = ['menu_date' => 'date'];

    public function region()    { return $this->belongsTo(Region::class); }
    public function mealType()  { return $this->belongsTo(MealType::class); }
    public function items()     { return $this->hasMany(MenuItem::class)->orderBy('sort_order'); }
    public function ratings()   { return $this->hasMany(Rating::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForDate($q, $date) { return $q->whereDate('menu_date', $date); }

    public function averageRating(): float
    {
        return round($this->ratings()->avg('score') ?? 0, 1);
    }
}
