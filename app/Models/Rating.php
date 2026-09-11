<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rating extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'menu_id', 'region_id', 'meal_type_id', 'rating_date', 'score', 'comment'];
    protected $casts = ['rating_date' => 'date', 'score' => 'integer'];

    public function user()     { return $this->belongsTo(User::class); }
    public function menu()     { return $this->belongsTo(Menu::class); }
    public function region()   { return $this->belongsTo(Region::class); }
    public function mealType() { return $this->belongsTo(MealType::class); }
}
