<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    // NO soft delete - audit trail tidak boleh dihapus
    public $timestamps = true;
    const UPDATED_AT = null; // only created_at needed conceptually, but we keep both

    protected $fillable = [
        'user_id', 'activity_type', 'module', 'description',
        'old_values', 'new_values', 'ip_address', 'user_agent',
        'loggable_type', 'loggable_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function loggable() { return $this->morphTo(); }
}
