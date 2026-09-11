<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Roster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'roster_date', 'status', 'shift_type', 'region_id', 'notes', 'created_by', 'updated_by',
    ];

    protected $casts = ['roster_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function region() { return $this->belongsTo(Region::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function mealPlans() { return $this->hasMany(MealPlan::class); }

    public function scopeForDate($q, $date) { return $q->whereDate('roster_date', $date); }
    public function scopeForRegion($q, $regionId) { return $q->where('region_id', $regionId); }
    public function scopeActive($q) { return $q->where('status', 'Kerja'); }
}
