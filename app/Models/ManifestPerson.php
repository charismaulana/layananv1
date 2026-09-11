<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManifestPerson extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'manifest_batch_id', 'user_id', 'meal_plan_id', 'sequence',
        'attendance_status', 'signed_at', 'qr_scan_token',
    ];
    protected $casts = ['signed_at' => 'datetime'];

    public function batch()    { return $this->belongsTo(ManifestBatch::class, 'manifest_batch_id'); }
    public function user()     { return $this->belongsTo(User::class); }
    public function mealPlan() { return $this->belongsTo(MealPlan::class); }
}
