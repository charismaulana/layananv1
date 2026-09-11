<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MealLocation extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['region_id', 'name', 'slug', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function region() { return $this->belongsTo(Region::class); }
    public function mealPlans() { return $this->hasMany(MealPlan::class); }
    public function users() { return $this->hasMany(User::class, 'meal_location_id'); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeForRegion($q, $regionId) { return $q->where('region_id', $regionId); }

    public function isMessHall(): bool
    {
        return str_contains($this->name, 'Mess Hall');
    }

    public function isDelivery(): bool
    {
        return !$this->isMessHall();
    }

    public function getDeliveryTypeLabelAttribute(): string
    {
        return $this->isMessHall() ? 'Ambil di Mess Hall' : 'Diantar ke Lokasi';
    }

    public function getIconAttribute(): string
    {
        return $this->isMessHall() ? '🍽️' : '🚚';
    }
}
