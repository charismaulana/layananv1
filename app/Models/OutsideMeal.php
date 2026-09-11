<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OutsideMeal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requester_id', 'meal_date', 'meal_type_id', 'reason',
        'status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = ['meal_date' => 'date', 'approved_at' => 'datetime'];

    public function requester() { return $this->belongsTo(User::class, 'requester_id'); }
    public function mealType()  { return $this->belongsTo(MealType::class); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function people()    { return $this->hasMany(OutsideMealPerson::class); }

    public function scopePending($q) { return $q->where('status', 'pending'); }
}
