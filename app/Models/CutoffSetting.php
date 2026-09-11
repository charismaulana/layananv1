<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CutoffSetting extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'cutoff_days_before', 'cutoff_time', 'timezone', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public static function active(): self
    {
        return static::where('is_active', true)->firstOrFail();
    }
}
