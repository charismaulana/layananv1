<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManifestBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'manifest_number', 'manifest_date', 'region_id', 'meal_type_id',
        'meal_location_id', 'version', 'status', 'total_pax',
        'generated_at', 'generated_by', 'is_override', 'override_reason',
    ];

    protected $casts = [
        'manifest_date' => 'date',
        'generated_at'  => 'datetime',
        'is_override'   => 'boolean',
    ];

    public function region()       { return $this->belongsTo(Region::class); }
    public function mealType()     { return $this->belongsTo(MealType::class); }
    public function mealLocation() { return $this->belongsTo(MealLocation::class); }
    public function generatedBy()  { return $this->belongsTo(User::class, 'generated_by'); }
    public function creator()      { return $this->belongsTo(User::class, 'generated_by'); }
    public function people()       { return $this->hasMany(ManifestPerson::class)->orderBy('sequence'); }
}
