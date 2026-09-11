<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovementPerson extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['movement_id', 'user_id', 'meal_type_ids'];
    protected $casts = ['meal_type_ids' => 'array'];

    public function movement() { return $this->belongsTo(Movement::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
