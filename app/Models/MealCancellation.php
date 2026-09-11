<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MealCancellation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'meal_plan_id', 'meal_date', 'meal_type_id', 'reason'];
    protected $casts = ['meal_date' => 'date'];

    public function user()     { return $this->belongsTo(User::class); }
    public function mealPlan() { return $this->belongsTo(MealPlan::class); }
    public function mealType() { return $this->belongsTo(MealType::class); }
}
