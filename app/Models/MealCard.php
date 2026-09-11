<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MealCard extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'token', 'is_active', 'last_used_at'];
    protected $casts = ['is_active' => 'boolean', 'last_used_at' => 'datetime'];
    protected $hidden = ['token'];

    public function user() { return $this->belongsTo(User::class); }

    public static function generateToken(): string
    {
        return hash('sha256', Str::uuid()->toString() . now()->timestamp . random_int(1000, 9999));
    }
}
