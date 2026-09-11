<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorMeal extends Model
{
    protected $fillable = [
        'group_id',
        'visitor_name',
        'institution',
        'meal_date',
        'region_id',
        'meal_location_id',
        'pax_count',
        'has_breakfast',
        'has_lunch',
        'has_dinner',
        'has_supper',
        'notes',
        'created_by',
        'status',
    ];

    protected $casts = [
        'meal_date'     => 'date',
        'pax_count'     => 'integer',
        'has_breakfast' => 'boolean',
        'has_lunch'     => 'boolean',
        'has_dinner'    => 'boolean',
        'has_supper'    => 'boolean',
    ];

    protected $appends = ['group_start_date', 'group_end_date', 'active_meal_types'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function mealLocation(): BelongsTo
    {
        return $this->belongsTo(MealLocation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActiveMealTypesAttribute(): array
    {
        $meals = [];
        if ($this->has_breakfast) $meals[] = "B'fast";
        if ($this->has_lunch)     $meals[] = "Lunch";
        if ($this->has_dinner)    $meals[] = "Dinner";
        if ($this->has_supper)    $meals[] = "Supper";
        return $meals;
    }

    public function getGroupStartDateAttribute(): string
    {
        if ($this->group_id) {
            $min = static::where('group_id', $this->group_id)->min('meal_date');
            if ($min) return Carbon::parse($min)->format('Y-m-d');
        }
        return $this->meal_date ? $this->meal_date->format('Y-m-d') : '';
    }

    public function getGroupEndDateAttribute(): string
    {
        if ($this->group_id) {
            $max = static::where('group_id', $this->group_id)->max('meal_date');
            if ($max) return Carbon::parse($max)->format('Y-m-d');
        }
        return $this->meal_date ? $this->meal_date->format('Y-m-d') : '';
    }
}
