<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutsideMealPerson extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['outside_meal_id', 'user_id'];

    public function outsideMeal() { return $this->belongsTo(OutsideMeal::class); }
    public function user()        { return $this->belongsTo(User::class); }
}
